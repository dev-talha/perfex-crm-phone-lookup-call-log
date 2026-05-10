<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Unified_phone_report_model extends App_Model
{
    public function metrics($filters = [])
    {
        $this->load->model('unified_phone/Unified_call_log_model', 'call_logs_model');
        $rows = $this->call_logs_model->get(null, $filters, 5000);
        $total_duration = 0;
        $incoming = $outgoing = $connected = $followups = 0;
        $by_staff = [];
        $by_outcome = [];
        $by_phone = [];

        foreach ($rows as $row) {
            $duration = (int) ($row['duration_seconds'] ?? 0);
            $total_duration += $duration;
            if (($row['call_type'] ?? '') === 'incoming') {
                $incoming++;
            }
            if (($row['call_type'] ?? '') === 'outgoing') {
                $outgoing++;
            }
            if (strtolower((string) ($row['outcome'] ?? '')) === 'connected') {
                $connected++;
            }
            if ((int) ($row['follow_up_required'] ?? 0) === 1) {
                $followups++;
            }
            $staff = trim((string) ($row['staff_name'] ?? '')) ?: _l('als_unknown');
            $outcome = trim((string) ($row['outcome'] ?? '')) ?: _l('none');
            $phone = trim((string) ($row['phone_normalized'] ?? '')) ?: trim((string) ($row['phone_raw'] ?? ''));
            $by_staff[$staff] = ($by_staff[$staff] ?? 0) + 1;
            $by_outcome[$outcome] = ($by_outcome[$outcome] ?? 0) + 1;
            if ($phone !== '') {
                $by_phone[$phone] = ($by_phone[$phone] ?? 0) + 1;
            }
        }

        arsort($by_staff);
        arsort($by_outcome);
        arsort($by_phone);

        return [
            'total_calls'             => count($rows),
            'incoming_calls'          => $incoming,
            'outgoing_calls'          => $outgoing,
            'connected_calls'         => $connected,
            'not_connected_calls'     => max(0, count($rows) - $connected),
            'average_duration'        => count($rows) ? round($total_duration / count($rows)) : 0,
            'total_duration'          => $total_duration,
            'follow_up_pending_count' => $followups,
            'by_staff'                => $by_staff,
            'by_outcome'              => $by_outcome,
            'by_phone'                => array_slice($by_phone, 0, 20, true),
            'rows'                    => $rows,
        ];
    }
}
