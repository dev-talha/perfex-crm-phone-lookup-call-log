<?php

defined('BASEPATH') or exit('No direct script access allowed');

if (!function_exists('unified_phone_can')) {
    function unified_phone_can($capability)
    {
        if (is_admin()) {
            return true;
        }
        if (function_exists('staff_can')) {
            return staff_can($capability, UNIFIED_PHONE_MODULE_NAME);
        }
        return has_permission(UNIFIED_PHONE_MODULE_NAME, '', $capability);
    }
}

if (!function_exists('unified_phone_format_duration')) {
    function unified_phone_format_duration($seconds)
    {
        $seconds = max(0, (int) $seconds);
        return sprintf('%02d:%02d:%02d', floor($seconds / 3600), floor(($seconds % 3600) / 60), $seconds % 60);
    }
}

if (!function_exists('unified_phone_datetime_local')) {
    function unified_phone_datetime_local($date = null)
    {
        $time = $date ? strtotime($date) : time();
        if (!$time) {
            $time = time();
        }
        return date('Y-m-d\TH:i', $time);
    }
}


if (!function_exists('unified_phone_date_input')) {
    function unified_phone_date_input($date = null)
    {
        $time = $date ? strtotime($date) : time();
        return date('Y-m-d', $time ?: time());
    }
}

if (!function_exists('unified_phone_time_input')) {
    function unified_phone_time_input($date = null)
    {
        $time = $date ? strtotime($date) : time();
        return date('H:i:s', $time ?: time());
    }
}

if (!function_exists('unified_phone_format_date')) {
    function unified_phone_format_date($date)
    {
        if (empty($date) || $date === '0000-00-00' || $date === '0000-00-00 00:00:00') {
            return '-';
        }
        $time = strtotime((string) $date);
        return $time ? date('d-M-Y', $time) : '-';
    }
}

if (!function_exists('unified_phone_format_datetime')) {
    function unified_phone_format_datetime($date)
    {
        if (empty($date) || $date === '0000-00-00' || $date === '0000-00-00 00:00:00') {
            return '-';
        }
        $time = strtotime((string) $date);
        return $time ? date('d-M-Y h:i A', $time) : '-';
    }
}

if (!function_exists('unified_phone_format_time')) {
    function unified_phone_format_time($date)
    {
        if (empty($date) || $date === '0000-00-00' || $date === '0000-00-00 00:00:00') {
            return '-';
        }
        $time = strtotime((string) $date);
        return $time ? date('h:i:s A', $time) : '-';
    }
}

if (!function_exists('unified_phone_safe_date')) {
    function unified_phone_safe_date($date)
    {
        return unified_phone_format_datetime($date);
    }
}

if (!function_exists('unified_phone_chatwoot_datetime')) {
    function unified_phone_chatwoot_datetime($value)
    {
        if ($value === null || $value === '') {
            return '-';
        }
        if (is_numeric($value)) {
            $timestamp = (int) $value;
        } else {
            $timestamp = strtotime((string) $value);
        }
        return $timestamp ? date('d-M-Y h:i A', $timestamp) : html_escape((string) $value);
    }
}


if (!function_exists('unified_phone_result_sections')) {
    function unified_phone_result_sections()
    {
        return [
            'customers' => 'clients',
            'contacts' => 'contacts',
            'leads' => 'leads',
            'proposals' => 'proposals',
            'estimates' => 'estimates',
            'invoices' => 'invoices',
            'projects' => 'projects',
            'contracts' => 'contracts',
            'tickets' => 'tickets',
            'chatwoot' => 'Unichat',
            'call_logs' => 'unified_phone_previous_call_logs',
        ];
    }
}


if (!function_exists('unified_phone_related_types')) {
    function unified_phone_related_types()
    {
        return [
            'project' => 'Project',
            'invoice' => 'Invoice',
            'customer' => 'Customer',
            'estimate' => 'Estimate',
            'contract' => 'Contract',
            'ticket' => 'Ticket',
            'lead' => 'Lead',
            'proposal' => 'Proposal',
        ];
    }
}

if (!function_exists('unified_phone_section_enabled')) {
    function unified_phone_section_enabled($section)
    {
        $option = get_option('unified_phone_show_' . $section);
        return $option === false ? true : $option === '1';
    }
}

if (!function_exists('unified_phone_section_limit')) {
    function unified_phone_section_limit($section, $default = 10)
    {
        $value = get_option('unified_phone_limit_' . $section);
        $value = is_numeric($value) ? (int) $value : (int) $default;
        if ($value < 1) {
            $value = (int) $default;
        }
        return min(100, $value);
    }
}



if (!function_exists('unified_phone_add_log_fields')) {
    function unified_phone_add_log_fields()
    {
        return [
            'phone_raw'          => 'unified_phone_phone',
            'call_type'          => 'unified_phone_call_type',
            'outcome'            => 'unified_phone_outcome',
            'call_date'          => 'unified_phone_call_date',
            'start_time'         => 'unified_phone_start_time',
            'end_time'           => 'unified_phone_end_time',
            'duration_text'      => 'unified_phone_duration',
            'rel_type'           => 'unified_phone_related_to',
            'rel_id'             => 'unified_phone_related_id',
            'follow_up_datetime' => 'unified_phone_follow_up_datetime',
            'call_recording'     => 'unified_phone_call_recording',
            'note'               => 'unified_phone_note',
        ];
    }
}

if (!function_exists('unified_phone_field_visible')) {
    function unified_phone_field_visible($field)
    {
        $value = get_option('unified_phone_show_field_' . $field);
        return $value === false ? true : $value === '1';
    }
}

if (!function_exists('unified_phone_field_col_class')) {
    function unified_phone_field_col_class($field, $default = 'col-md-3')
    {
        return unified_phone_field_visible($field) ? $default : $default . ' hide';
    }
}

if (!function_exists('unified_phone_sip_scheme')) {
    function unified_phone_sip_scheme()
    {
        $scheme = strtolower(trim((string) get_option('unified_phone_sip_uri_scheme')));
        $allowed = ['sip', 'tel', 'callto'];
        return in_array($scheme, $allowed, true) ? $scheme : 'sip';
    }
}

if (!function_exists('unified_phone_recording_upload_dir')) {
    function unified_phone_recording_upload_dir($id = null)
    {
        $dir = FCPATH . 'uploads/unified_phone_recordings/';
        if ($id !== null) {
            $dir .= (int) $id . '/';
        }
        return $dir;
    }
}

if (!function_exists('unified_phone_recording_url')) {
    function unified_phone_recording_url($path)
    {
        $path = trim((string) $path);
        if ($path === '') { return ''; }
        if (strpos($path, 'http://') === 0 || strpos($path, 'https://') === 0) { return $path; }
        return base_url($path);
    }
}

if (!function_exists('unified_phone_add_log_required_fields')) {
    function unified_phone_add_log_required_fields()
    {
        return unified_phone_add_log_fields();
    }
}

if (!function_exists('unified_phone_is_required_field')) {
    function unified_phone_is_required_field($field)
    {
        $value = get_option('unified_phone_required_' . $field);
        return $value === '1';
    }
}

if (!function_exists('unified_phone_required_attr')) {
    function unified_phone_required_attr($field)
    {
        return unified_phone_is_required_field($field) ? ['required' => 'required'] : [];
    }
}


if (!function_exists('unified_phone_ini_size_to_kb')) {
    function unified_phone_ini_size_to_kb($value)
    {
        $value = trim((string) $value);
        if ($value === '') { return 0; }
        $unit = strtolower(substr($value, -1));
        $num = (float) $value;
        switch ($unit) {
            case 'g': $num *= 1024;
                // no break
            case 'm': $num *= 1024;
                break;
            case 'k':
                break;
            default:
                $num = $num / 1024;
                break;
        }
        return (int) floor($num);
    }
}

if (!function_exists('unified_phone_server_upload_limit_kb')) {
    function unified_phone_server_upload_limit_kb()
    {
        $upload = unified_phone_ini_size_to_kb(ini_get('upload_max_filesize'));
        $post = unified_phone_ini_size_to_kb(ini_get('post_max_size'));
        $limits = array_filter([$upload, $post], static function ($v) { return $v > 0; });
        return $limits ? min($limits) : 0;
    }
}

if (!function_exists('unified_phone_money')) {
    function unified_phone_money($amount)
    {
        if (function_exists('app_format_money') && function_exists('get_base_currency')) {
            return app_format_money($amount, get_base_currency());
        }
        return number_format((float) $amount, 2);
    }
}


if (!function_exists('unified_phone_related_url')) {
    function unified_phone_related_url($type, $id = null)
    {
        $type = strtolower(trim((string) $type));
        $id = (int) $id;
        if ($id <= 0) {
            return '';
        }
        switch ($type) {
            case 'customer':
            case 'client':
                return admin_url('clients/client/' . $id);
            case 'lead':
                return admin_url('leads/index/' . $id);
            case 'contact':
                return admin_url('clients/client/' . $id);
            case 'project':
                return admin_url('projects/view/' . $id);
            case 'invoice':
                return admin_url('invoices/list_invoices/' . $id);
            case 'estimate':
                return admin_url('estimates/list_estimates/' . $id);
            case 'contract':
                return admin_url('contracts/contract/' . $id);
            case 'ticket':
                return admin_url('tickets/ticket/' . $id);
            case 'proposal':
                return admin_url('proposals/list_proposals/' . $id);
        }
        return '';
    }
}

if (!function_exists('unified_phone_related_record_name')) {
    function unified_phone_related_record_name($type, $id)
    {
        $type = strtolower(trim((string) $type));
        $id = (int) $id;
        if ($id <= 0) {
            return '-';
        }
        $CI = &get_instance();
        switch ($type) {
            case 'customer':
            case 'client':
                $row = $CI->db->select('company')->where('userid', $id)->get(db_prefix() . 'clients')->row_array();
                return $row ? $row['company'] : _l('client') . ' #' . $id;
            case 'lead':
                $row = $CI->db->select('name')->where('id', $id)->get(db_prefix() . 'leads')->row_array();
                return $row ? $row['name'] : _l('lead') . ' #' . $id;
            case 'project':
                $row = $CI->db->select('name')->where('id', $id)->get(db_prefix() . 'projects')->row_array();
                return $row ? $row['name'] : 'Project #' . $id;
            case 'invoice':
                return function_exists('format_invoice_number') ? format_invoice_number($id) : 'Invoice #' . $id;
            case 'estimate':
                return function_exists('format_estimate_number') ? format_estimate_number($id) : 'Estimate #' . $id;
            case 'contract':
                $row = $CI->db->select('subject')->where('id', $id)->get(db_prefix() . 'contracts')->row_array();
                return $row ? $row['subject'] : 'Contract #' . $id;
            case 'ticket':
                $row = $CI->db->select('subject')->where('ticketid', $id)->get(db_prefix() . 'tickets')->row_array();
                return $row ? $row['subject'] : 'Ticket #' . $id;
            case 'proposal':
                return function_exists('format_proposal_number') ? format_proposal_number($id) : 'Proposal #' . $id;
        }
        return ucfirst($type) . ' #' . $id;
    }
}

if (!function_exists('unified_phone_call_log_related')) {
    function unified_phone_call_log_related($log)
    {
        $log = (array) $log;
        $type = strtolower(trim((string) ($log['rel_type'] ?? '')));
        $id = !empty($log['rel_id']) ? (int) $log['rel_id'] : 0;

        if ($type === '' && !empty($log['client_id'])) {
            $type = 'customer';
            $id = (int) $log['client_id'];
        } elseif ($type === '' && !empty($log['lead_id'])) {
            $type = 'lead';
            $id = (int) $log['lead_id'];
        } elseif ($type === '' && !empty($log['contact_id'])) {
            $type = 'contact';
            $id = (int) $log['contact_id'];
        }

        if ($type === 'customer' && !empty($log['client_company'])) {
            $label = _l('client') . ': ' . $log['client_company'];
        } elseif ($type === 'lead' && !empty($log['lead_name'])) {
            $label = _l('lead') . ': ' . $log['lead_name'];
        } elseif ($type === 'contact' && !empty($log['contact_name'])) {
            $label = _l('contact') . ': ' . $log['contact_name'];
        } elseif ($type !== '' && $id > 0) {
            $label = ucfirst($type) . ': ' . unified_phone_related_record_name($type, $id);
        } else {
            $label = '-';
        }

        return [
            'type'  => $type,
            'id'    => $id,
            'label' => $label,
            'url'   => unified_phone_related_url($type, $id),
        ];
    }
}
