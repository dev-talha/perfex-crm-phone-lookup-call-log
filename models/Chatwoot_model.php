<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Chatwoot_model extends App_Model
{
    public function __construct()
    {
        parent::__construct();
        $this->load->library('unified_phone/Phone_normalizer');
        $this->load->library('unified_phone/Chatwoot_api');
    }

    public function lookup_by_phone($phone)
    {
        if (get_option('unified_phone_chatwoot_enabled') !== '1') {
            return ['enabled' => false, 'contacts' => [], 'conversations' => [], 'error' => null];
        }

        $keys = $this->phone_normalizer->search_keys($phone);
        $normalized = $this->phone_normalizer->normalize_bd_phone($phone);
        $cacheKey = $normalized ?: md5($phone);

        if (get_option('unified_phone_cache_enabled') === '1') {
            $cached = $this->read_cache($cacheKey);
            if ($cached !== null) {
                return $cached;
            }
        }

        $contacts = [];
        $conversations = [];
        $error = null;

        foreach ($keys as $key) {
            $result = $this->chatwoot_api->search_contacts($key);
            if (isset($result['success']) && $result['success'] === false) {
                $error = $result['error'] ?? 'Chatwoot API error';
                continue;
            }
            foreach ((array) $result as $contact) {
                if (!is_array($contact)) {
                    continue;
                }
                $id = $contact['id'] ?? ($contact['source_id'] ?? null);
                if (!$id) {
                    continue;
                }
                $contacts[(string) $id] = $contact;
            }
        }

        foreach ($contacts as $contactId => $contact) {
            $items = $this->chatwoot_api->get_contact_conversations($contactId);
            if (isset($items['success']) && $items['success'] === false) {
                $error = $items['error'] ?? $error;
                continue;
            }
            foreach ((array) $items as $conversation) {
                if (!is_array($conversation)) {
                    continue;
                }
                $cid = $conversation['id'] ?? null;
                if ($cid) {
                    $conversation['_contact_id'] = $contactId;
                    $conversations[(string) $cid] = $conversation;
                }
            }
        }

        $payload = [
            'enabled'       => true,
            'contacts'      => array_values($contacts),
            'conversations' => $this->latest_conversations($conversations, 10),
            'error'         => $error,
        ];

        if (get_option('unified_phone_cache_enabled') === '1') {
            $this->write_cache($cacheKey, $normalized, $payload);
        }

        return $payload;
    }


    private function latest_conversations($conversations, $limit = 10)
    {
        $rows = array_values((array) $conversations);
        usort($rows, function ($a, $b) {
            return $this->conversation_timestamp($b) <=> $this->conversation_timestamp($a);
        });
        return array_slice($rows, 0, max(1, (int) $limit));
    }

    private function conversation_timestamp($conversation)
    {
        foreach (['last_activity_at', 'updated_at', 'created_at'] as $field) {
            if (!empty($conversation[$field])) {
                if (is_numeric($conversation[$field])) {
                    return (int) $conversation[$field];
                }
                $parsed = strtotime((string) $conversation[$field]);
                if ($parsed) {
                    return $parsed;
                }
            }
        }
        return 0;
    }

    private function read_cache($sourceId)
    {
        if (!$this->db->table_exists(db_prefix() . 'unified_phone_cache')) {
            return null;
        }
        $row = $this->db->where('source', 'chatwoot_lookup')
            ->where('source_id', (string) $sourceId)
            ->where('expires_at >', date('Y-m-d H:i:s'))
            ->order_by('id', 'DESC')
            ->get(db_prefix() . 'unified_phone_cache')
            ->row_array();
        if (!$row || empty($row['payload'])) {
            return null;
        }
        $decoded = json_decode($row['payload'], true);
        if (!is_array($decoded)) {
            return null;
        }
        if (isset($decoded['conversations']) && is_array($decoded['conversations'])) {
            $decoded['conversations'] = $this->latest_conversations($decoded['conversations'], 10);
        }
        return $decoded;
    }

    private function write_cache($sourceId, $normalized, $payload)
    {
        if (!$this->db->table_exists(db_prefix() . 'unified_phone_cache')) {
            return;
        }
        $duration = max(60, (int) get_option('unified_phone_cache_duration'));
        $this->db->insert(db_prefix() . 'unified_phone_cache', [
            'phone_raw'         => $sourceId,
            'phone_normalized'  => $normalized,
            'source'            => 'chatwoot_lookup',
            'source_id'         => (string) $sourceId,
            'payload'           => json_encode($payload),
            'created_at'        => date('Y-m-d H:i:s'),
            'expires_at'        => date('Y-m-d H:i:s', time() + $duration),
        ]);
    }
}
