<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Unified_phone_reports extends AdminController
{
    public function __construct()
    {
        parent::__construct();
        $this->load->helper('unified_phone/unified_phone');
        if (get_option('unified_phone_reporting_enabled') !== '1' && !unified_phone_can('settings')) {
            access_denied(UNIFIED_PHONE_MODULE_NAME);
        }
        if (!unified_phone_can('view_reports')) {
            access_denied(UNIFIED_PHONE_MODULE_NAME);
        }
        $this->load->model('unified_phone/Unified_phone_report_model', 'report_model');
    }

    public function index()
    {
        $filters = $this->input->get(null, true) ?: [];
        $data['title'] = _l('unified_phone_reports');
        $data['metrics'] = $this->report_model->metrics($filters);
        $data['filters'] = $filters;
        $this->load->view('unified_phone/reports', $data);
    }

    public function export($format = 'csv')
    {
        if (get_option('unified_phone_export_enabled') !== '1' || !unified_phone_can('export')) {
            access_denied(UNIFIED_PHONE_MODULE_NAME);
        }
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
}
