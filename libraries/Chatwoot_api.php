<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Chatwoot_api
{
    protected $CI;
    protected $base_url;
    protected $account_id;
    protected $token;
    protected $timeout;

    public function __construct()
    {
        $this->CI = &get_instance();
        $this->base_url = rtrim((string) get_option('unified_phone_chatwoot_base_url'), '/');
        $this->account_id = trim((string) get_option('unified_phone_chatwoot_account_id'));
        $this->token = (string) get_option('unified_phone_chatwoot_api_token');
        $this->timeout = max(3, (int) get_option('unified_phone_chatwoot_timeout'));
    }

    public function enabled()
    {
        return get_option('unified_phone_chatwoot_enabled') === '1' && $this->base_url !== '' && $this->account_id !== '' && $this->token !== '';
    }

    public function test_connection()
    {
        if (!$this->enabled()) {
            return ['success' => false, 'error' => 'Chatwoot is not fully configured.'];
        }
        $inboxes = $this->get_inboxes();
        if (isset($inboxes['success']) && $inboxes['success'] === false) {
            return $inboxes;
        }
        return ['success' => true, 'inboxes' => $inboxes];
    }


    public function get_inboxes()
    {
        if (!$this->enabled()) {
            return [];
        }
        $response = $this->request('GET', '/api/v1/accounts/' . rawurlencode($this->account_id) . '/inboxes');
        if (isset($response['success']) && $response['success'] === false) {
            return $response;
        }
        return $response['payload'] ?? ($response['data']['payload'] ?? (isset($response[0]) ? $response : []));
    }

    public function search_contacts($query)
    {
        if (!$this->enabled()) {
            return [];
        }
        $response = $this->request('GET', '/api/v1/accounts/' . rawurlencode($this->account_id) . '/contacts/search?q=' . rawurlencode($query));
        if (isset($response['success']) && $response['success'] === false) {
            return $response;
        }
        $items = $response['payload'] ?? ($response['data']['payload'] ?? (isset($response[0]) ? $response : []));
        foreach ($items as $i => $contact) {
            if (is_array($contact) && isset($contact['id'])) {
                $items[$i]['_url'] = $this->contact_url($contact['id']);
            }
        }
        return $items;
    }

    public function get_contact_conversations($contact_id)
    {
        if (!$this->enabled() || !$contact_id) {
            return [];
        }
        $response = $this->request('GET', '/api/v1/accounts/' . rawurlencode($this->account_id) . '/contacts/' . rawurlencode($contact_id) . '/conversations');
        if (isset($response['success']) && $response['success'] === false) {
            return $response;
        }
        $items = $response['payload'] ?? ($response['data']['payload'] ?? (isset($response[0]) ? $response : []));
        foreach ($items as $i => $conversation) {
            if (is_array($conversation) && isset($conversation['id'])) {
                $items[$i]['_url'] = $this->conversation_url($conversation['id']);
            }
        }
        return $items;
    }

    public function contact_url($contact_id)
    {
        return $this->base_url . '/app/accounts/' . rawurlencode($this->account_id) . '/contacts/' . rawurlencode($contact_id);
    }

    public function conversation_url($conversation_id)
    {
        return $this->base_url . '/app/accounts/' . rawurlencode($this->account_id) . '/conversations/' . rawurlencode($conversation_id);
    }

    protected function request($method, $path)
    {
        $ch = curl_init($this->base_url . $path);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_CUSTOMREQUEST  => $method,
            CURLOPT_TIMEOUT        => $this->timeout,
            CURLOPT_HTTPHEADER     => [
                'Content-Type: application/json',
                'api_access_token: ' . $this->token,
            ],
        ]);
        $body = curl_exec($ch);
        $err = curl_error($ch);
        $code = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($err) {
            return ['success' => false, 'error' => $err, 'status' => $code];
        }
        $decoded = json_decode((string) $body, true);
        if ($code >= 400) {
            return ['success' => false, 'error' => $decoded['message'] ?? $body, 'status' => $code];
        }
        return is_array($decoded) ? $decoded : ['success' => true, 'raw' => $body, 'status' => $code];
    }
}
