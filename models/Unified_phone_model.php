<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Unified_phone_model extends App_Model
{
    public function __construct()
    {
        parent::__construct();
        $this->load->library('unified_phone/Phone_normalizer');
        $this->load->helper('unified_phone/unified_phone');
    }

    public function lookup($phone, $limit = 10)
    {
        $keys = $this->phone_normalizer->search_keys($phone);
        $normalized = $this->phone_normalizer->normalize_bd_phone($phone);
        $limit = max(1, min(100, (int) $limit));

        return [
            'phone_raw'        => $phone,
            'phone_normalized' => $normalized,
            'phone_display'    => $this->phone_normalizer->display_bd_phone($normalized),
            'keys'             => $keys,
            'customers'        => unified_phone_section_enabled('customers') ? $this->search_customers($keys, unified_phone_section_limit('customers', $limit)) : [],
            'contacts'         => unified_phone_section_enabled('contacts') ? $this->search_contacts($keys, unified_phone_section_limit('contacts', $limit)) : [],
            'leads'            => unified_phone_section_enabled('leads') ? $this->search_leads($keys, unified_phone_section_limit('leads', $limit)) : [],
            'call_logs'        => [],
        ];
    }

    public function search_customers($keys, $limit = 10)
    {
        if (empty($keys)) {
            return [];
        }
        $this->db->select('clients.userid, clients.company, clients.phonenumber, clients.datecreated, clients.active, clients.addedfrom, CONCAT(st.firstname, " ", st.lastname) as assigned_staff, pc.id as primary_contact_id, CONCAT(pc.firstname, " ", pc.lastname) as primary_contact, pc.email as primary_email', false);
        $this->db->from(db_prefix() . 'clients as clients');
        $this->db->join(db_prefix() . 'staff as st', 'st.staffid = clients.addedfrom', 'left');
        $this->db->join(db_prefix() . 'contacts as pc', 'pc.userid = clients.userid AND pc.is_primary = 1', 'left');
        $this->apply_like_group(['clients.phonenumber'], $keys);
        $this->db->order_by('clients.userid', 'DESC')->limit(max(1, (int) $limit));
        return $this->db->get()->result_array();
    }

    public function search_contacts($keys, $limit = 10)
    {
        if (empty($keys)) {
            return [];
        }
        $this->db->select('contacts.id, contacts.userid, contacts.firstname, contacts.lastname, contacts.email, contacts.phonenumber, contacts.title, contacts.active, contacts.datecreated, clients.company as client_company', false);
        $this->db->from(db_prefix() . 'contacts as contacts');
        $this->db->join(db_prefix() . 'clients as clients', 'clients.userid = contacts.userid', 'left');
        $this->apply_like_group(['contacts.phonenumber'], $keys);
        $this->db->order_by('contacts.id', 'DESC')->limit(max(1, (int) $limit));
        return $this->db->get()->result_array();
    }

    public function search_leads($keys, $limit = 10)
    {
        if (empty($keys)) {
            return [];
        }
        $this->db->select('leads.id, leads.name, leads.company, leads.phonenumber, leads.email, leads.dateadded, leads.lastcontact, leads.assigned, statuses.name as status_name, sources.name as source_name, CONCAT(st.firstname, " ", st.lastname) as assigned_staff', false);
        $this->db->from(db_prefix() . 'leads as leads');
        $this->db->join(db_prefix() . 'leads_status as statuses', 'statuses.id = leads.status', 'left');
        $this->db->join(db_prefix() . 'leads_sources as sources', 'sources.id = leads.source', 'left');
        $this->db->join(db_prefix() . 'staff as st', 'st.staffid = leads.assigned', 'left');
        $this->apply_like_group(['leads.phonenumber'], $keys);
        $this->db->order_by('leads.id', 'DESC')->limit(max(1, (int) $limit));
        return $this->db->get()->result_array();
    }

    public function related_records($client_ids = [], $lead_ids = [])
    {
        $data = [
            'proposals' => [], 'estimates' => [], 'invoices' => [], 'payments' => [], 'projects' => [],
            'contracts' => [], 'tickets' => [],
        ];
        $client_ids = array_values(array_filter(array_map('intval', (array) $client_ids)));
        $lead_ids = array_values(array_filter(array_map('intval', (array) $lead_ids)));

        if ($client_ids) {
            if (unified_phone_section_enabled('invoices') && $this->db->table_exists(db_prefix() . 'invoices')) {
                $data['invoices'] = $this->db->where_in('clientid', $client_ids)->order_by('datecreated', 'DESC')->limit(unified_phone_section_limit('invoices'))->get(db_prefix() . 'invoices')->result_array();
            }
            if (unified_phone_section_enabled('estimates') && $this->db->table_exists(db_prefix() . 'estimates')) {
                $data['estimates'] = $this->db->where_in('clientid', $client_ids)->order_by('datecreated', 'DESC')->limit(unified_phone_section_limit('estimates'))->get(db_prefix() . 'estimates')->result_array();
            }
            if (unified_phone_section_enabled('tickets') && $this->db->table_exists(db_prefix() . 'tickets')) {
                $data['tickets'] = $this->db->where_in('userid', $client_ids)->order_by('date', 'DESC')->limit(unified_phone_section_limit('tickets'))->get(db_prefix() . 'tickets')->result_array();
            }
            if (unified_phone_section_enabled('projects') && $this->db->table_exists(db_prefix() . 'projects')) {
                $data['projects'] = $this->db->where_in('clientid', $client_ids)->order_by('id', 'DESC')->limit(unified_phone_section_limit('projects'))->get(db_prefix() . 'projects')->result_array();
            }
            if (unified_phone_section_enabled('contracts') && $this->db->table_exists(db_prefix() . 'contracts')) {
                $data['contracts'] = $this->db->where_in('client', $client_ids)->order_by('id', 'DESC')->limit(unified_phone_section_limit('contracts'))->get(db_prefix() . 'contracts')->result_array();
            }
            if (unified_phone_section_enabled('payments') && $this->db->table_exists(db_prefix() . 'invoicepaymentrecords') && $this->db->table_exists(db_prefix() . 'invoices')) {
                $this->db->select('p.*, i.clientid, i.number as invoice_number, i.prefix as invoice_prefix, i.date as invoice_date', false);
                $this->db->from(db_prefix() . 'invoicepaymentrecords as p');
                $this->db->join(db_prefix() . 'invoices as i', 'i.id = p.invoiceid', 'left');
                $this->db->where_in('i.clientid', $client_ids);
                $data['payments'] = $this->db->order_by('p.date', 'DESC')->limit(unified_phone_section_limit('payments'))->get()->result_array();
            }
        }

        if (unified_phone_section_enabled('proposals') && $this->db->table_exists(db_prefix() . 'proposals') && ($client_ids || $lead_ids)) {
            $this->db->from(db_prefix() . 'proposals');
            $this->db->group_start();
            if ($client_ids) {
                $this->db->or_group_start()->where('rel_type', 'customer')->where_in('rel_id', $client_ids)->group_end();
            }
            if ($lead_ids) {
                $this->db->or_group_start()->where('rel_type', 'lead')->where_in('rel_id', $lead_ids)->group_end();
            }
            $this->db->group_end();
            $data['proposals'] = $this->db->order_by('datecreated', 'DESC')->limit(unified_phone_section_limit('proposals'))->get()->result_array();
        }



        return $data;
    }

    public function related_financials($client_ids = [], $lead_ids = [])
    {
        return $this->related_records($client_ids, $lead_ids);
    }

    public function search_call_logs($keys, $limit = 50)
    {
        if (empty($keys) || !$this->db->table_exists(db_prefix() . 'unified_call_logs')) {
            return [];
        }
        $this->db->from(db_prefix() . 'unified_call_logs');
        $this->apply_like_group(['phone_raw', 'phone_normalized'], $keys);
        $this->db->order_by('call_datetime', 'DESC')->limit((int) $limit);
        return $this->db->get()->result_array();
    }

    private function project_ids_for_clients($client_ids)
    {
        if (empty($client_ids) || !$this->db->table_exists(db_prefix() . 'projects')) {
            return [0];
        }
        $rows = $this->db->select('id')->where_in('clientid', $client_ids)->get(db_prefix() . 'projects')->result_array();
        $ids = array_map('intval', array_column($rows, 'id'));
        return $ids ?: [0];
    }

    private function apply_like_group($columns, $keys)
    {
        $keys = array_values(array_filter(array_unique((array) $keys), static function ($v) { return trim((string) $v) !== ''; }));
        if (empty($keys)) {
            $this->db->where('1=', '0', false);
            return;
        }

        $this->db->group_start();
        foreach ($columns as $column) {
            foreach ($keys as $key) {
                if (function_exists('get_option') && get_option('unified_phone_like_search_enabled') === '0') {
                    $this->db->or_where($column, $key);
                } else {
                    $this->db->or_like($column, $key);
                }
            }
        }
        $this->db->group_end();
    }
}
