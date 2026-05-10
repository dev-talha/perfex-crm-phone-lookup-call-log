<?php

defined('BASEPATH') or exit('No direct script access allowed');

$CI = &get_instance();

$options = [
    'unified_phone_enabled', 'unified_phone_default_call_type', 'unified_phone_default_call_outcome',
    'unified_phone_normalization_enabled', 'unified_phone_like_search_enabled', 'unified_phone_chatwoot_enabled',
    'unified_phone_chatwoot_base_url', 'unified_phone_chatwoot_account_id', 'unified_phone_chatwoot_api_token',
    'unified_phone_chatwoot_inbox_id', 'unified_phone_chatwoot_timeout', 'unified_phone_cache_enabled',
    'unified_phone_cache_duration', 'unified_phone_reporting_enabled', 'unified_phone_export_enabled',
    'unified_phone_edit_after_save', 'unified_phone_agents_edit_own', 'unified_phone_agents_delete_own'
];
foreach ($options as $option) {
    delete_option($option);
}

// Data tables are intentionally kept to avoid accidental call history loss.
// Uncomment only if you want hard delete on uninstall.
// $CI->db->query('DROP TABLE IF EXISTS `' . db_prefix() . 'unified_call_logs`');
// $CI->db->query('DROP TABLE IF EXISTS `' . db_prefix() . 'unified_call_outcomes`');
// $CI->db->query('DROP TABLE IF EXISTS `' . db_prefix() . 'unified_phone_cache`');
