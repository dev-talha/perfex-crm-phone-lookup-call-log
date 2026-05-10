<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Unified_call_log_model extends App_Model
{
    private $table;

    public function __construct()
    {
        parent::__construct();
        $this->table = db_prefix() . 'unified_call_logs';
        $this->load->library('unified_phone/Phone_normalizer');
        $this->load->helper('unified_phone/unified_phone');
    }

    public function get($id = null, $filters = [], $limit = 200, $offset = 0)
    {
        $this->db->select('ucl.*, co.color as outcome_color, CONCAT(st.firstname, " ", st.lastname) as staff_name, c.company as client_company, l.name as lead_name, CONCAT(ct.firstname, " ", ct.lastname) as contact_name', false);
        $this->db->from($this->table . ' as ucl');
        $this->db->join(db_prefix() . 'staff as st', 'st.staffid = ucl.created_by', 'left');
        $this->db->join(db_prefix() . 'clients as c', 'c.userid = ucl.client_id', 'left');
        $this->db->join(db_prefix() . 'leads as l', 'l.id = ucl.lead_id', 'left');
        $this->db->join(db_prefix() . 'contacts as ct', 'ct.id = ucl.contact_id', 'left');
        $this->db->join(db_prefix() . 'unified_call_outcomes as co', 'co.name = ucl.outcome', 'left');

        if ($id !== null) {
            return $this->db->where('ucl.id', (int) $id)->get()->row_array();
        }

        $this->apply_filters($filters);
        $this->db->order_by('ucl.call_datetime', 'DESC');
        if ((int) $limit > 0) {
            $this->db->limit((int) $limit, max(0, (int) $offset));
        }
        return $this->db->get()->result_array();
    }



    public function get_for_lead_phone($lead, $limit = 100)
    {
        $phone = '';
        if (is_object($lead) && isset($lead->phonenumber)) {
            $phone = (string) $lead->phonenumber;
        } elseif (is_array($lead) && isset($lead['phonenumber'])) {
            $phone = (string) $lead['phonenumber'];
        }

        if (trim($phone) === '') {
            return [];
        }

        return $this->get(null, ['phone' => $phone], (int) $limit);
    }

    public function count_for_lead_phone($lead)
    {
        $phone = '';
        if (is_object($lead) && isset($lead->phonenumber)) {
            $phone = (string) $lead->phonenumber;
        } elseif (is_array($lead) && isset($lead['phonenumber'])) {
            $phone = (string) $lead['phonenumber'];
        }

        if (trim($phone) === '') {
            return 0;
        }

        return $this->count_filtered(['phone' => $phone], 1000);
    }

    public function get_paginated($filters = [], $limit = 20, $offset = 0, $max_total = 100)
    {
        $limit = max(1, (int) $limit);
        $max_total = max($limit, (int) $max_total);
        $offset = max(0, min((int) $offset, $max_total - 1));
        $remaining = max(0, $max_total - $offset);
        $effectiveLimit = min($limit, $remaining);

        return $this->get(null, $filters, $effectiveLimit, $offset);
    }

    public function count_filtered($filters = [], $max_total = 100)
    {
        $this->db->from($this->table . ' as ucl');
        $this->apply_filters($filters);
        $count = (int) $this->db->count_all_results();
        return min($count, max(0, (int) $max_total));
    }

    public function add($data)
    {
        $payload = $this->prepare_payload($data, true);
        $this->db->insert($this->table, $payload);
        return (int) $this->db->insert_id();
    }

    public function update($id, $data)
    {
        $payload = $this->prepare_payload($data, false);
        $this->db->where('id', (int) $id)->update($this->table, $payload);
        return $this->db->affected_rows() >= 0;
    }

    public function update_recording($id, $data)
    {
        $payload = [
            'recording_file'          => $data['recording_file'] ?? '',
            'recording_original_name' => $data['recording_original_name'] ?? '',
            'recording_mime'          => $data['recording_mime'] ?? '',
            'recording_size'          => (int) ($data['recording_size'] ?? 0),
            'updated_by'              => get_staff_user_id(),
            'updated_at'              => date('Y-m-d H:i:s'),
        ];
        $this->db->where('id', (int) $id)->update($this->table, $payload);
        return $this->db->affected_rows() >= 0;
    }

    public function delete($id)
    {
        $this->db->where('id', (int) $id)->delete($this->table);
        return $this->db->affected_rows() > 0;
    }

    public function outcomes($active_only = true)
    {
        if ($active_only) {
            $this->db->where('is_active', 1);
        }
        return $this->db->order_by('sort_order', 'ASC')->order_by('name', 'ASC')->get(db_prefix() . 'unified_call_outcomes')->result_array();
    }

    public function staff_options()
    {
        $this->db->select('staffid, firstname, lastname');
        $this->db->from(db_prefix() . 'staff');
        $this->db->where('active', 1);
        $rows = $this->db->order_by('firstname', 'ASC')->get()->result_array();
        $options = [['id' => '', 'name' => _l('all')]];
        foreach ($rows as $row) {
            $options[] = ['id' => (int) $row['staffid'], 'name' => trim($row['firstname'] . ' ' . $row['lastname'])];
        }
        return $options;
    }

    public function add_outcome($data)
    {
        $payload = $this->prepare_outcome_payload($data);
        if ($payload['name'] === '') {
            return false;
        }
        if ($payload['is_default']) {
            $this->db->update(db_prefix() . 'unified_call_outcomes', ['is_default' => 0]);
            update_option('unified_phone_default_call_outcome', $payload['name']);
        }
        $this->db->insert(db_prefix() . 'unified_call_outcomes', $payload);
        return (int) $this->db->insert_id();
    }

    public function update_outcome($id, $data)
    {
        $id = (int) $id;
        if ($id <= 0) {
            return false;
        }
        $existing = $this->db->where('id', $id)->get(db_prefix() . 'unified_call_outcomes')->row_array();
        if (!$existing) {
            return false;
        }
        $payload = $this->prepare_outcome_payload($data);
        if ($payload['name'] === '') {
            return false;
        }
        if ($payload['is_default']) {
            $this->db->where('id !=', $id)->update(db_prefix() . 'unified_call_outcomes', ['is_default' => 0]);
            update_option('unified_phone_default_call_outcome', $payload['name']);
        } elseif ((int) $existing['is_default'] === 1) {
            update_option('unified_phone_default_call_outcome', '');
        }
        $this->db->where('id', $id)->update(db_prefix() . 'unified_call_outcomes', $payload);
        return $this->db->affected_rows() >= 0;
    }

    public function delete_outcome($id)
    {
        $id = (int) $id;
        if ($id <= 0) {
            return false;
        }
        $existing = $this->db->where('id', $id)->get(db_prefix() . 'unified_call_outcomes')->row_array();
        if (!$existing) {
            return false;
        }
        if ((int) $existing['is_default'] === 1) {
            update_option('unified_phone_default_call_outcome', '');
        }
        $this->db->where('id', $id)->delete(db_prefix() . 'unified_call_outcomes');
        return $this->db->affected_rows() > 0;
    }

    private function prepare_outcome_payload($data)
    {
        $color = trim((string) ($data['color'] ?? '#2563eb'));
        if ($color === '' || !preg_match('/^#?[A-Fa-f0-9]{3}([A-Fa-f0-9]{3})?$/', $color)) {
            $color = '#2563eb';
        }
        if ($color[0] !== '#') {
            $color = '#' . $color;
        }

        return [
            'name'       => trim((string) ($data['name'] ?? '')),
            'color'      => $color,
            'is_default' => !empty($data['is_default']) ? 1 : 0,
            'is_active'  => isset($data['is_active']) ? (int) $data['is_active'] : 1,
            'sort_order' => (int) ($data['sort_order'] ?? 0),
        ];
    }

    private function prepare_payload($data, $is_create)
    {
        $now = date('Y-m-d H:i:s');
        $phoneRaw = $data['phone_raw'] ?? ($data['phone'] ?? '');
        $allowedRelTypes = array_keys(unified_phone_related_types());
        $relType = strtolower(trim((string) ($data['rel_type'] ?? '')));
        if (!in_array($relType, $allowedRelTypes, true)) {
            $relType = null;
        }
        $relId = !empty($data['rel_id']) ? (int) $data['rel_id'] : null;

        $payload = [
            'phone_raw'                => trim((string) $phoneRaw),
            'phone_normalized'         => $this->phone_normalizer->normalize_bd_phone($phoneRaw),
            'call_type'                => in_array(($data['call_type'] ?? ''), ['incoming', 'outgoing'], true) ? $data['call_type'] : '',
            'call_datetime'            => $this->compose_datetime($data['call_date'] ?? ($data['call_datetime'] ?? null), $data['start_time'] ?? null, $now),
            'start_time'               => $this->compose_datetime($data['call_date'] ?? ($data['call_datetime'] ?? null), $data['start_time'] ?? null),
            'end_time'                 => $this->compose_datetime($data['call_date'] ?? ($data['call_datetime'] ?? null), $data['end_time'] ?? null),
            'outcome'                  => trim((string) ($data['outcome'] ?? '')),
            'note'                     => $data['note'] ?? '',
            'follow_up_datetime'       => !empty($data['follow_up_datetime']) ? $this->to_mysql_datetime($data['follow_up_datetime']) : null,
            'follow_up_required'       => !empty($data['follow_up_datetime']) ? 1 : 0,
            'rel_type'                 => $relType,
            'rel_id'                   => $relId,
            'client_id'                => !empty($data['client_id']) ? (int) $data['client_id'] : ($relType === 'customer' ? $relId : null),
            'contact_id'               => null,
            'lead_id'                  => !empty($data['lead_id']) ? (int) $data['lead_id'] : ($relType === 'lead' ? $relId : null),
            'chatwoot_contact_id'      => '',
            'chatwoot_conversation_id' => '',
            'updated_by'               => get_staff_user_id(),
            'updated_at'               => $now,
        ];

        $payload['duration_seconds'] = $this->duration_seconds($data, $payload);
        $payload['duration_text'] = unified_phone_format_duration($payload['duration_seconds']);

        if ($is_create) {
            $payload['created_by'] = get_staff_user_id();
            $payload['created_at'] = $now;
        }

        return $payload;
    }

    private function apply_filters($filters)
    {
        if (!empty($filters['date_from'])) {
            $this->db->where('ucl.call_datetime >=', $filters['date_from'] . ' 00:00:00');
        }
        if (!empty($filters['date_to'])) {
            $this->db->where('ucl.call_datetime <=', $filters['date_to'] . ' 23:59:59');
        }
        if (!empty($filters['phone'])) {
            $keys = $this->phone_normalizer->search_keys($filters['phone']);
            $this->db->group_start();
            foreach ($keys as $key) {
                if (function_exists('get_option') && get_option('unified_phone_like_search_enabled') === '0') {
                    $this->db->or_where('ucl.phone_raw', $key);
                    $this->db->or_where('ucl.phone_normalized', $key);
                } else {
                    $this->db->or_like('ucl.phone_raw', $key);
                    $this->db->or_like('ucl.phone_normalized', $key);
                }
            }
            $this->db->group_end();
        }
        foreach (['created_by' => 'staff', 'call_type' => 'call_type', 'outcome' => 'outcome', 'lead_id' => 'lead_id', 'client_id' => 'client_id', 'contact_id' => 'contact_id', 'rel_type' => 'rel_type'] as $column => $field) {
            if (isset($filters[$field]) && $filters[$field] !== '') {
                $this->db->where('ucl.' . $column, $filters[$field]);
            }
        }
        if (!empty($filters['follow_up_from'])) {
            $this->db->where('ucl.follow_up_datetime >=', $this->to_mysql_datetime($filters['follow_up_from']));
        }
        if (!empty($filters['follow_up_to'])) {
            $this->db->where('ucl.follow_up_datetime <=', $this->to_mysql_datetime($filters['follow_up_to']));
        }
        if (!empty($filters['duration_min'])) {
            $this->db->where('ucl.duration_seconds >=', (int) $filters['duration_min']);
        }
        if (!empty($filters['duration_max'])) {
            $this->db->where('ucl.duration_seconds <=', (int) $filters['duration_max']);
        }
    }

    private function duration_seconds($data, $payload)
    {
        if (isset($data['duration_seconds']) && $data['duration_seconds'] !== '') {
            return max(0, (int) $data['duration_seconds']);
        }
        if (!empty($data['duration_text']) && preg_match('/^(\d{1,2}):(\d{1,2}):(\d{1,2})$/', $data['duration_text'], $m)) {
            return ((int) $m[1] * 3600) + ((int) $m[2] * 60) + (int) $m[3];
        }
        if (!empty($payload['start_time']) && !empty($payload['end_time'])) {
            return max(0, strtotime($payload['end_time']) - strtotime($payload['start_time']));
        }
        return 0;
    }


    private function compose_datetime($dateValue, $timeValue = null, $fallback = null)
    {
        if (!empty($dateValue) && strpos((string) $dateValue, 'T') !== false) {
            return $this->to_mysql_datetime($dateValue);
        }
        if (!empty($dateValue) && preg_match('/^\d{4}-\d{2}-\d{2} \d{2}:\d{2}/', (string) $dateValue)) {
            return $this->to_mysql_datetime($dateValue);
        }
        $date = !empty($dateValue) ? date('Y-m-d', strtotime((string) $dateValue)) : date('Y-m-d', strtotime((string) ($fallback ?: 'now')));
        if (empty($timeValue)) {
            $timeValue = $fallback ? date('H:i:s', strtotime((string) $fallback)) : null;
        }
        if (empty($timeValue)) {
            return null;
        }
        $timeValue = trim((string) $timeValue);
        if (preg_match('/^\d{2}:\d{2}$/', $timeValue)) {
            $timeValue .= ':00';
        }
        if (!preg_match('/^\d{2}:\d{2}:\d{2}$/', $timeValue)) {
            $parsed = strtotime($timeValue);
            if (!$parsed) {
                return null;
            }
            $timeValue = date('H:i:s', $parsed);
        }
        return $this->to_mysql_datetime($date . ' ' . $timeValue);
    }

    private function to_mysql_datetime($value)
    {
        if (empty($value)) {
            return null;
        }
        $value = str_replace('T', ' ', (string) $value);
        $time = strtotime($value);
        return $time ? date('Y-m-d H:i:s', $time) : null;
    }
}
