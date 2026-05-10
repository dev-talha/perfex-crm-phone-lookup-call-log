<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Unified_phone extends AdminController
{
    public function __construct()
    {
        parent::__construct();
        $this->load->helper('unified_phone/unified_phone');
        if (get_option('unified_phone_enabled') !== '1' && !unified_phone_can('settings')) {
            access_denied(UNIFIED_PHONE_MODULE_NAME);
        }
        if (!unified_phone_can('view') && !unified_phone_can('view_detail') && !unified_phone_can('view_reports') && !unified_phone_can('create') && !unified_phone_can('edit') && !unified_phone_can('delete') && !unified_phone_can('settings')) {
            access_denied(UNIFIED_PHONE_MODULE_NAME);
        }
        $this->load->model('unified_phone/Unified_phone_model', 'lookup_model');
        $this->load->model('unified_phone/Unified_call_log_model', 'call_log_model');
        $this->load->model('unified_phone/Chatwoot_model', 'chatwoot_model');
    }

    public function index()
    {
        if (!unified_phone_can('view')) { access_denied(UNIFIED_PHONE_MODULE_NAME); }
        $phone = trim((string) $this->input->get('phone', true));
        if ($phone !== '') {
            return $this->search();
        }
        $data['title'] = _l('unified_phone_search');
        $data['recent_logs'] = $this->call_log_model->get(null, [], 10);
        $data['outcomes'] = $this->call_log_model->outcomes();
        $data['call_type'] = $this->sanitize_call_type($this->input->get('calltype', true));
        $this->load->view('unified_phone/search', $data);
    }

    public function call_by_sip()
    {
        if (!unified_phone_can('view')) { access_denied(UNIFIED_PHONE_MODULE_NAME); }
        if (get_option('unified_phone_sip_enabled') !== '1') {
            set_alert('warning', _l('unified_phone_sip_disabled'));
            redirect(admin_url('unified_phone'));
        }
        $data['title'] = _l('unified_phone_call_by_sip');
        $data['outcomes'] = $this->call_log_model->outcomes();
        $data['call_type'] = 'outgoing';
        $this->load->view('unified_phone/call_by_sip', $data);
    }

    public function search()
    {
        if (!unified_phone_can('view')) { access_denied(UNIFIED_PHONE_MODULE_NAME); }
        $phone = trim((string) $this->input->get('phone', true));
        if ($phone === '') {
            set_alert('warning', _l('unified_phone_phone_required'));
            redirect(admin_url('unified_phone'));
        }

        $defaultLimit = unified_phone_section_limit('default', 10);
        $data = $this->lookup_model->lookup($phone, $defaultLimit);
        $client_ids = array_column($data['customers'], 'userid');
        $lead_ids = array_column($data['leads'], 'id');
        $data['related'] = $this->lookup_model->related_records($client_ids, $lead_ids);
        $data['section_settings'] = unified_phone_result_sections();
        $data['chatwoot_enabled'] = get_option('unified_phone_chatwoot_enabled') === '1' && unified_phone_section_enabled('chatwoot');
        $data['chatwoot'] = $data['chatwoot_enabled'] ? $this->chatwoot_model->lookup_by_phone($phone) : ['enabled' => false, 'contacts' => [], 'conversations' => [], 'error' => null];
        $data['outcomes'] = $this->call_log_model->outcomes();
        $data['call_type'] = $this->sanitize_call_type($this->input->get('calltype', true));

        $perPage = unified_phone_section_limit('call_logs', 20);
        $maxHistory = unified_phone_section_limit('call_logs_history', 100);
        $callLogFilters = ['phone' => $phone];
        $totalRows = $this->call_log_model->count_filtered($callLogFilters, $maxHistory);
        $totalPages = max(1, (int) ceil($totalRows / $perPage));
        $page = max(1, min((int) $this->input->get('call_page', true), $totalPages));
        $offset = ($page - 1) * $perPage;
        $data['call_logs'] = $this->call_log_model->get_paginated($callLogFilters, $perPage, $offset, $maxHistory);
        $data['call_logs_total'] = $totalRows;
        $data['call_logs_page'] = $page;
        $data['call_logs_per_page'] = $perPage;
        $data['call_logs_max_history'] = $maxHistory;
        $data['call_logs_pagination'] = $this->build_call_log_pagination($phone, $page, $perPage, $totalRows);

        $data['title'] = _l('unified_phone_lookup') . ': ' . $data['phone_display'];
        $this->load->view('unified_phone/result', $data);
    }

    public function add_call_log()
    {
        if (!unified_phone_can('create')) {
            access_denied(UNIFIED_PHONE_MODULE_NAME);
        }
        if (!$this->input->post()) {
            show_404();
        }
        $post = $this->input->post(null, true);
        $validationError = $this->validate_required_call_log_fields($post);
        if ($validationError !== '') {
            if ($this->input->is_ajax_request()) {
                echo json_encode(['success' => false, 'message' => $validationError]);
                return;
            }
            set_alert('warning', $validationError);
            redirect($_SERVER['HTTP_REFERER'] ?? admin_url('unified_phone/reports'));
        }
        $id = $this->call_log_model->add($post);
        $uploadError = '';
        if ($id) {
            $uploadError = $this->handle_recording_upload($id);
        }
        if ($this->input->is_ajax_request()) {
            echo json_encode([
                'success' => (bool) $id && $uploadError === '',
                'id' => $id,
                'message' => $uploadError !== '' ? $uploadError : ($id ? _l('added_successfully', _l('unified_phone_call_log')) : _l('unified_phone_call_log_save_failed')),
            ]);
            return;
        }
        if ($uploadError !== '') {
            set_alert('warning', $uploadError);
        } else {
            set_alert('success', _l('added_successfully', _l('unified_phone_call_log')));
        }
        redirect($_SERVER['HTTP_REFERER'] ?? admin_url('unified_phone/reports'));
    }


    public function view_call_log($id)
    {
        if (!unified_phone_can('view_detail')) {
            access_denied(UNIFIED_PHONE_MODULE_NAME);
        }
        $row = $this->call_log_model->get((int) $id);
        if (!$row) {
            show_404();
        }
        $data['title'] = _l('unified_phone_call_log_details');
        $data['log'] = $row;
        $this->load->view('unified_phone/view_call_log', $data);
    }

    public function recording($id)
    {
        if (!unified_phone_can('recordings')) {
            access_denied(UNIFIED_PHONE_MODULE_NAME);
        }
        $row = $this->call_log_model->get((int) $id);
        if (!$row || empty($row['recording_file'])) {
            show_404();
        }

        $baseDir = realpath(unified_phone_recording_upload_dir());
        $path = realpath(FCPATH . ltrim((string) $row['recording_file'], '/'));
        if (!$baseDir || !$path || strpos($path, $baseDir) !== 0 || !is_file($path)) {
            show_404();
        }

        $allowedExt = array_filter(array_map('strtolower', array_map('trim', explode(',', (string) (get_option('unified_phone_recording_allowed_types') ?: 'mp3,wav,m4a,ogg,webm,mp4')))));
        $ext = strtolower(pathinfo($path, PATHINFO_EXTENSION));
        if (!in_array($ext, $allowedExt, true)) {
            show_404();
        }

        $mime = !empty($row['recording_mime']) ? $row['recording_mime'] : 'application/octet-stream';
        if (function_exists('finfo_open')) {
            $finfo = finfo_open(FILEINFO_MIME_TYPE);
            if ($finfo) {
                $detected = finfo_file($finfo, $path);
                finfo_close($finfo);
                if ($detected) {
                    $mime = $detected;
                }
            }
        }
        $name = !empty($row['recording_original_name']) ? $row['recording_original_name'] : basename($path);
        $name = preg_replace('/[^A-Za-z0-9._ -]/', '_', (string) $name);
        while (ob_get_level() > 0) { @ob_end_clean(); }
        header('X-Content-Type-Options: nosniff');
        header('Cache-Control: private, no-store, no-cache, must-revalidate');
        header('Pragma: no-cache');
        header('Content-Type: ' . $mime);
        header('Content-Length: ' . filesize($path));
        header('Content-Disposition: inline; filename="' . str_replace('"', '', $name) . '"');
        readfile($path);
        exit;
    }

    public function edit_call_log($id)
    {
        $row = $this->call_log_model->get((int) $id);
        if (!$row) {
            show_404();
        }
        if (!$this->can_modify_row($row, 'edit')) {
            access_denied(UNIFIED_PHONE_MODULE_NAME);
        }
        if ($this->input->post()) {
            $post = $this->input->post(null, true);
            $validationError = $this->validate_required_call_log_fields($post);
            if ($validationError !== '') {
                set_alert('warning', $validationError);
                redirect(admin_url('unified_phone/edit_call_log/' . (int) $id));
            }
            $this->call_log_model->update((int) $id, $post);
            $uploadError = $this->handle_recording_upload((int) $id);
            if ($uploadError !== '') {
                set_alert('warning', $uploadError);
            } else {
                set_alert('success', _l('updated_successfully', _l('unified_phone_call_log')));
            }
            redirect(admin_url('unified_phone/reports'));
        }
        $data['title'] = _l('edit', _l('unified_phone_call_log'));
        $data['log'] = $row;
        $data['outcomes'] = $this->call_log_model->outcomes();
        $this->load->view('unified_phone/edit_call_log', $data);
    }

    public function call_logs()
    {
        redirect(admin_url('unified_phone/reports'));
    }

    public function delete_call_log($id)
    {
        $row = $this->call_log_model->get((int) $id);
        if (!$row) {
            show_404();
        }
        if (!$this->can_modify_row($row, 'delete')) {
            access_denied(UNIFIED_PHONE_MODULE_NAME);
        }
        $this->delete_recording_files((int) $id);
        $this->call_log_model->delete((int) $id);
        set_alert('success', _l('deleted', _l('unified_phone_call_log')));
        redirect(admin_url('unified_phone/reports'));
    }


    public function settings()
    {
        if (!unified_phone_can('settings')) {
            access_denied(UNIFIED_PHONE_MODULE_NAME);
        }
        if ($this->input->post()) {
            $postedSettings = $this->input->post('settings', true);
            $postedSettings = is_array($postedSettings) ? $postedSettings : [];
            $rawPost = $this->input->post(null, true);
            $rawPost = is_array($rawPost) ? $rawPost : [];

            $fields = [
                'unified_phone_enabled', 'unified_phone_default_call_type', 'unified_phone_default_call_outcome',
                'unified_phone_normalization_enabled', 'unified_phone_like_search_enabled', 'unified_phone_chatwoot_enabled',
                'unified_phone_chatwoot_base_url', 'unified_phone_chatwoot_account_id', 'unified_phone_chatwoot_api_token',
                'unified_phone_chatwoot_timeout', 'unified_phone_cache_enabled',
                'unified_phone_cache_duration', 'unified_phone_reporting_enabled', 'unified_phone_export_enabled',
                'unified_phone_edit_after_save', 'unified_phone_agents_edit_own', 'unified_phone_agents_delete_own',
                'unified_phone_limit_default', 'unified_phone_limit_call_logs', 'unified_phone_limit_call_logs_history',
                'unified_phone_sip_enabled', 'unified_phone_global_click_to_call_enabled', 'unified_phone_sip_uri_scheme',
                'unified_phone_recording_enabled', 'unified_phone_recording_max_size', 'unified_phone_recording_allowed_types'
            ];
            foreach (array_keys(unified_phone_add_log_required_fields()) as $requiredField) {
                $fields[] = 'unified_phone_required_' . $requiredField;
            }
            foreach (array_keys(unified_phone_add_log_fields()) as $visibleField) {
                $fields[] = 'unified_phone_show_field_' . $visibleField;
            }
            $yesNo = [
                'unified_phone_enabled', 'unified_phone_normalization_enabled', 'unified_phone_like_search_enabled',
                'unified_phone_chatwoot_enabled', 'unified_phone_cache_enabled', 'unified_phone_reporting_enabled',
                'unified_phone_export_enabled', 'unified_phone_edit_after_save', 'unified_phone_agents_edit_own',
                'unified_phone_agents_delete_own', 'unified_phone_sip_enabled', 'unified_phone_global_click_to_call_enabled', 'unified_phone_recording_enabled'
            ];
            foreach (array_keys(unified_phone_add_log_required_fields()) as $requiredField) {
                $yesNo[] = 'unified_phone_required_' . $requiredField;
            }
            foreach (array_keys(unified_phone_add_log_fields()) as $visibleField) {
                $yesNo[] = 'unified_phone_show_field_' . $visibleField;
            }
            foreach (array_keys(unified_phone_result_sections()) as $section) {
                $fields[] = 'unified_phone_show_' . $section;
                $yesNo[] = 'unified_phone_show_' . $section;
                $fields[] = 'unified_phone_limit_' . $section;
            }
            foreach ($fields as $field) {
                $value = array_key_exists($field, $postedSettings) ? $postedSettings[$field] : ($rawPost[$field] ?? null);
                if (in_array($field, $yesNo, true)) {
                    update_option($field, (string) $value === '1' ? '1' : '0');
                    continue;
                }
                if ($field === 'unified_phone_default_call_type' && !in_array($value, ['incoming', 'outgoing'], true)) {
                    $value = 'incoming';
                }
                if (strpos($field, 'unified_phone_limit_') === 0) {
                    $value = max(1, min(100, (int) $value));
                }
                if ($field === 'unified_phone_recording_max_size') {
                    $serverMax = unified_phone_server_upload_limit_kb();
                    $maxAllowed = $serverMax > 0 ? min(102400, $serverMax) : 102400;
                    $value = max(512, min($maxAllowed, (int) $value));
                }
                if ($field === 'unified_phone_sip_uri_scheme') {
                    $value = in_array(strtolower((string) $value), ['sip', 'tel', 'callto'], true) ? strtolower((string) $value) : 'sip';
                }
                if ($field === 'unified_phone_recording_allowed_types') {
                    $value = preg_replace('/[^a-zA-Z0-9,]/', '', (string) $value);
                }
                update_option($field, $value !== null ? (string) $value : '');
            }
            // Inbox ID is intentionally not stored from settings. Inboxes are discovered automatically from the Chatwoot account.
            update_option('unified_phone_chatwoot_inbox_id', '');
            set_alert('success', _l('settings_updated'));
            redirect(admin_url('unified_phone/settings'));
        }
        $data['title'] = _l('unified_phone_settings');
        $data['outcomes'] = $this->call_log_model->outcomes(false);
        $data['result_sections'] = unified_phone_result_sections();
        $data['server_upload_limit_kb'] = unified_phone_server_upload_limit_kb();
        $this->load->view('unified_phone/settings', $data);
    }

    public function add_outcome()
    {
        if (!unified_phone_can('settings')) {
            access_denied(UNIFIED_PHONE_MODULE_NAME);
        }
        if (!$this->input->post()) {
            show_404();
        }
        $insertId = $this->call_log_model->add_outcome($this->input->post(null, true));
        if ($insertId) {
            set_alert('success', _l('added_successfully', _l('unified_phone_outcome')));
        } else {
            set_alert('warning', _l('unified_phone_outcome_save_failed'));
        }
        redirect(admin_url('unified_phone/settings#call-outcomes'));
    }

    public function update_outcome($id)
    {
        if (!unified_phone_can('settings')) {
            access_denied(UNIFIED_PHONE_MODULE_NAME);
        }
        if (!$this->input->post()) {
            show_404();
        }
        $updated = $this->call_log_model->update_outcome((int) $id, $this->input->post(null, true));
        if ($updated) {
            set_alert('success', _l('updated_successfully', _l('unified_phone_outcome')));
        } else {
            set_alert('warning', _l('unified_phone_outcome_save_failed'));
        }
        redirect(admin_url('unified_phone/settings#call-outcomes'));
    }

    public function delete_outcome($id)
    {
        if (!unified_phone_can('settings')) {
            access_denied(UNIFIED_PHONE_MODULE_NAME);
        }
        $deleted = $this->call_log_model->delete_outcome((int) $id);
        if ($deleted) {
            set_alert('success', _l('deleted', _l('unified_phone_outcome')));
        } else {
            set_alert('warning', _l('problem_deleting', _l('unified_phone_outcome')));
        }
        redirect(admin_url('unified_phone/settings#call-outcomes'));
    }

    public function reports()
    {
        if (get_option('unified_phone_reporting_enabled') !== '1' && !unified_phone_can('settings')) {
            access_denied(UNIFIED_PHONE_MODULE_NAME);
        }
        if (!unified_phone_can('view_reports')) {
            access_denied(UNIFIED_PHONE_MODULE_NAME);
        }
        $this->load->model('unified_phone/Unified_phone_report_model', 'report_model');
        $filters = $this->input->get(null, true) ?: [];
        $data['title'] = _l('unified_phone_reports');
        $data['metrics'] = $this->report_model->metrics($filters);
        $data['filters'] = $filters;
        $data['outcomes'] = $this->call_log_model->outcomes();
        $data['staff'] = $this->call_log_model->staff_options();
        $data['related_types'] = unified_phone_related_types();
        $this->load->view('unified_phone/reports', $data);
    }

    public function export($format = 'csv')
    {
        if (get_option('unified_phone_export_enabled') !== '1' || !unified_phone_can('export')) {
            access_denied(UNIFIED_PHONE_MODULE_NAME);
        }
        $this->load->model('unified_phone/Unified_phone_report_model', 'report_model');
        $filters = $this->input->get(null, true) ?: [];
        $metrics = $this->report_model->metrics($filters);
        $rows = $metrics['rows'];
        $filename = 'unified-call-logs-' . date('Y-m-d-His');

        if ($format === 'xls') {
            header('Content-Type: application/vnd.ms-excel; charset=utf-8');
            header('Content-Disposition: attachment; filename="' . $filename . '.xls"');
            echo '<table border="1"><tr><th>Date</th><th>Phone</th><th>Normalized</th><th>Type</th><th>Duration</th><th>Outcome</th><th>Staff</th><th>Related</th><th>Note</th></tr>';
            foreach ($rows as $row) {
                echo '<tr><td>' . html_escape($row['call_datetime']) . '</td><td>' . html_escape($row['phone_raw']) . '</td><td>' . html_escape($row['phone_normalized']) . '</td><td>' . html_escape($row['call_type']) . '</td><td>' . html_escape($row['duration_text']) . '</td><td>' . html_escape($row['outcome']) . '</td><td>' . html_escape($row['staff_name']) . '</td><td>' . html_escape($row['rel_type'] . ':' . $row['rel_id']) . '</td><td>' . html_escape($row['note']) . '</td></tr>';
            }
            echo '</table>';
            exit;
        }

        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="' . $filename . '.csv"');
        $out = fopen('php://output', 'w');
        fputcsv($out, ['Date', 'Phone', 'Normalized', 'Type', 'Duration', 'Outcome', 'Staff', 'Related Type', 'Related ID', 'Note']);
        foreach ($rows as $row) {
            fputcsv($out, [$row['call_datetime'], $row['phone_raw'], $row['phone_normalized'], $row['call_type'], $row['duration_text'], $row['outcome'], $row['staff_name'], $row['rel_type'], $row['rel_id'], $row['note']]);
        }
        fclose($out);
        exit;
    }

    public function related_search()
    {
        if (!$this->input->is_ajax_request() || (!unified_phone_can('view') && !unified_phone_can('create'))) {
            ajax_access_denied();
        }

        $type = strtolower(trim((string) ($this->input->post('type', true) ?: $this->input->get('type', true))));
        $allowed = ['project', 'invoice', 'customer', 'estimate', 'contract', 'ticket', 'lead', 'proposal'];
        if (!in_array($type, $allowed, true)) {
            echo json_encode([]);
            return;
        }

        $this->load->helper('relation');
        if (!$this->input->post('q') && $this->input->get('q')) {
            $_POST['q'] = $this->input->get('q', true);
        }
        $relId = $this->input->post('rel_id', true) ?: $this->input->get('rel_id', true);
        $relId = is_numeric($relId) ? (int) $relId : '';
        $data = get_relation_data($type, $relId, []);
        echo json_encode(init_relation_options($data, $type, $relId));
    }


    public function lead_call_logs_tab($lead_id)
    {
        if (!unified_phone_can('view')) {
            access_denied(UNIFIED_PHONE_MODULE_NAME);
        }

        $lead_id = (int) $lead_id;
        if ($lead_id <= 0) {
            show_404();
        }

        $this->load->model('leads_model');
        $lead = $this->leads_model->get($lead_id);
        if (!$lead) {
            show_404();
        }

        $logs = $this->call_log_model->get_for_lead_phone($lead, 100);
        $this->load->view('unified_phone/lead_tabs/call_logs', [
            'lead'      => $lead,
            'call_logs' => $logs,
        ]);
    }

    public function chatwoot_test()
    {
        if (!unified_phone_can('chatwoot') && !unified_phone_can('settings')) {
            access_denied(UNIFIED_PHONE_MODULE_NAME);
        }
        $this->load->library('unified_phone/Chatwoot_api');
        echo json_encode($this->chatwoot_api->test_connection());
    }


    private function sanitize_call_type($value)
    {
        $value = strtolower(trim((string) $value));
        return in_array($value, ['incoming', 'outgoing'], true) ? $value : '';
    }

    private function validate_required_call_log_fields($post)
    {
        $post = is_array($post) ? $post : [];
        foreach (unified_phone_add_log_required_fields() as $field => $label) {
            if (!unified_phone_is_required_field($field)) {
                continue;
            }
            if ($field === 'call_recording') {
                if (!unified_phone_can('recordings')) {
                    continue;
                }
                if (empty($_FILES['call_recording']['name'])) {
                    return sprintf(_l('unified_phone_required_field_missing'), _l($label));
                }
                continue;
            }
            if (!isset($post[$field]) || trim((string) $post[$field]) === '') {
                return sprintf(_l('unified_phone_required_field_missing'), _l($label));
            }
        }
        return '';
    }



    private function handle_recording_upload($callLogId)
    {
        if (get_option('unified_phone_recording_enabled') !== '1') {
            return '';
        }
        if (!unified_phone_can('recordings')) {
            return '';
        }
        if (empty($_FILES['call_recording']['name'])) {
            return '';
        }

        $callLogId = (int) $callLogId;
        if ($callLogId <= 0) {
            return _l('unified_phone_recording_upload_failed');
        }

        $dir = unified_phone_recording_upload_dir($callLogId);
        if (!is_dir($dir) && !mkdir($dir, 0755, true)) {
            return _l('unified_phone_recording_upload_failed');
        }
        $this->protect_recording_directory($dir);

        $allowedTypesRaw = trim((string) get_option('unified_phone_recording_allowed_types'));
        $allowedTypesRaw = $allowedTypesRaw !== '' ? $allowedTypesRaw : 'mp3,wav,m4a,ogg,webm,mp4';
        $allowedTypesRaw = preg_replace('/[^a-zA-Z0-9,]/', '', $allowedTypesRaw);
        $allowedTypes = str_replace(',', '|', $allowedTypesRaw);
        $maxSize = (int) get_option('unified_phone_recording_max_size');
        $maxSize = $maxSize > 0 ? $maxSize : 10240;

        $uploadErrorCode = $_FILES['call_recording']['error'] ?? UPLOAD_ERR_OK;
        if ($uploadErrorCode === UPLOAD_ERR_INI_SIZE || $uploadErrorCode === UPLOAD_ERR_FORM_SIZE) {
            return _l('unified_phone_recording_php_limit_error');
        }
        if ($uploadErrorCode !== UPLOAD_ERR_OK) {
            return _l('unified_phone_recording_upload_failed');
        }

        $serverMax = unified_phone_server_upload_limit_kb();
        if ($serverMax > 0) {
            $maxSize = min($maxSize, $serverMax);
        }
        if (!empty($_FILES['call_recording']['size']) && ((int) $_FILES['call_recording']['size'] > ($maxSize * 1024))) {
            return sprintf(_l('unified_phone_recording_client_size_error'), $maxSize);
        }

        $this->load->library('upload');
        $config = [
            'upload_path'   => $dir,
            'allowed_types' => $allowedTypes,
            'max_size'      => $maxSize,
            'encrypt_name'  => true,
        ];
        $this->upload->initialize($config);
        if (!$this->upload->do_upload('call_recording')) {
            return strip_tags($this->upload->display_errors('', ''));
        }

        $file = $this->upload->data();
        $ext = strtolower((string) ($file['file_ext'] ?? ''));
        $ext = ltrim($ext, '.');
        $allowedExt = array_filter(array_map('strtolower', array_map('trim', explode(',', $allowedTypesRaw))));
        if ($ext === '' || !in_array($ext, $allowedExt, true)) {
            @unlink($file['full_path']);
            return _l('unified_phone_recording_upload_failed');
        }

        $originalName = preg_replace('/[^A-Za-z0-9._ -]/', '_', (string) $_FILES['call_recording']['name']);
        $this->delete_recording_files($callLogId);
        $relativePath = 'uploads/unified_phone_recordings/' . $callLogId . '/' . $file['file_name'];
        $this->call_log_model->update_recording($callLogId, [
            'recording_file'          => $relativePath,
            'recording_original_name' => $originalName,
            'recording_mime'          => $file['file_type'],
            'recording_size'          => (int) $file['file_size'] * 1024,
        ]);
        return '';
    }

    private function protect_recording_directory($dir)
    {
        $baseDir = unified_phone_recording_upload_dir();
        if (!is_dir($baseDir)) {
            @mkdir($baseDir, 0755, true);
        }
        $rules = "Deny from all\nRequire all denied\n";
        @file_put_contents($baseDir . '.htaccess', $rules);
        @file_put_contents($dir . '.htaccess', $rules);
        @file_put_contents($baseDir . 'index.html', '');
        @file_put_contents($dir . 'index.html', '');
    }

    private function delete_recording_files($callLogId)
    {
        $row = $this->call_log_model->get((int) $callLogId);
        if (!$row || empty($row['recording_file'])) {
            return;
        }
        $baseDir = realpath(unified_phone_recording_upload_dir());
        $path = realpath(FCPATH . ltrim((string) $row['recording_file'], '/'));
        if ($baseDir && $path && strpos($path, $baseDir) === 0 && is_file($path)) {
            @unlink($path);
        }
    }

    private function build_call_log_pagination($phone, $page, $perPage, $totalRows)
    {
        $totalPages = (int) ceil(max(0, (int) $totalRows) / max(1, (int) $perPage));
        if ($totalPages <= 1) {
            return '';
        }
        $page = max(1, min((int) $page, $totalPages));
        $queryBase = ['phone' => $phone];
        $html = '<nav class="text-right"><ul class="pagination pagination-sm mtop15 mbot0">';
        $prevDisabled = $page <= 1 ? ' class="disabled"' : '';
        $prevUrl = $page <= 1 ? '#' : admin_url('unified_phone/search?' . http_build_query(array_merge($queryBase, ['call_page' => $page - 1])));
        $html .= '<li' . $prevDisabled . '><a href="' . $prevUrl . '">&laquo;</a></li>';

        $start = max(1, $page - 2);
        $end = min($totalPages, $page + 2);
        if ($start > 1) {
            $html .= '<li><a href="' . admin_url('unified_phone/search?' . http_build_query(array_merge($queryBase, ['call_page' => 1]))) . '">1</a></li>';
            if ($start > 2) {
                $html .= '<li class="disabled"><a href="#">...</a></li>';
            }
        }
        for ($i = $start; $i <= $end; $i++) {
            $active = $i === $page ? ' class="active"' : '';
            $url = admin_url('unified_phone/search?' . http_build_query(array_merge($queryBase, ['call_page' => $i])));
            $html .= '<li' . $active . '><a href="' . $url . '">' . $i . '</a></li>';
        }
        if ($end < $totalPages) {
            if ($end < $totalPages - 1) {
                $html .= '<li class="disabled"><a href="#">...</a></li>';
            }
            $html .= '<li><a href="' . admin_url('unified_phone/search?' . http_build_query(array_merge($queryBase, ['call_page' => $totalPages]))) . '">' . $totalPages . '</a></li>';
        }

        $nextDisabled = $page >= $totalPages ? ' class="disabled"' : '';
        $nextUrl = $page >= $totalPages ? '#' : admin_url('unified_phone/search?' . http_build_query(array_merge($queryBase, ['call_page' => $page + 1])));
        $html .= '<li' . $nextDisabled . '><a href="' . $nextUrl . '">&raquo;</a></li>';
        $html .= '</ul></nav>';
        return $html;
    }

    private function can_modify_row($row, $capability)
    {
        if ($capability === 'edit' && get_option('unified_phone_edit_after_save') !== '1' && !unified_phone_can('settings')) {
            return false;
        }
        if (unified_phone_can($capability)) {
            return true;
        }
        if ($capability === 'edit' && get_option('unified_phone_agents_edit_own') === '1') {
            return (int) $row['created_by'] === (int) get_staff_user_id();
        }
        if ($capability === 'delete' && get_option('unified_phone_agents_delete_own') === '1') {
            return (int) $row['created_by'] === (int) get_staff_user_id();
        }
        return false;
    }
}
