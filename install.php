<?php

defined('BASEPATH') or exit('No direct script access allowed');

$CI = &get_instance();
if (!function_exists('unified_phone_add_log_fields')) {
    require_once(__DIR__ . '/helpers/unified_phone_helper.php');
}

if (!$CI->db->table_exists(db_prefix() . 'unified_call_logs')) {
    $CI->db->query('CREATE TABLE `' . db_prefix() . "unified_call_logs` (
      `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
      `phone_raw` VARCHAR(50) NULL,
      `phone_normalized` VARCHAR(50) NULL,
      `call_type` VARCHAR(20) DEFAULT 'incoming',
      `call_datetime` DATETIME NULL,
      `start_time` DATETIME NULL,
      `end_time` DATETIME NULL,
      `duration_seconds` INT DEFAULT 0,
      `duration_text` VARCHAR(20) NULL,
      `outcome` VARCHAR(100) NULL,
      `note` TEXT NULL,
      `follow_up_required` TINYINT(1) DEFAULT 0,
      `follow_up_datetime` DATETIME NULL,
      `rel_type` VARCHAR(50) NULL,
      `rel_id` INT NULL,
      `client_id` INT NULL,
      `contact_id` INT NULL,
      `lead_id` INT NULL,
      `chatwoot_contact_id` VARCHAR(100) NULL,
      `chatwoot_conversation_id` VARCHAR(100) NULL,
      `recording_file` VARCHAR(255) NULL,
      `recording_original_name` VARCHAR(255) NULL,
      `recording_mime` VARCHAR(100) NULL,
      `recording_size` INT UNSIGNED DEFAULT 0,
      `created_by` INT NULL,
      `updated_by` INT NULL,
      `created_at` DATETIME NULL,
      `updated_at` DATETIME NULL,
      PRIMARY KEY (`id`),
      KEY `idx_phone_normalized` (`phone_normalized`),
      KEY `idx_phone_raw` (`phone_raw`),
      KEY `idx_created_by` (`created_by`),
      KEY `idx_call_datetime` (`call_datetime`),
      KEY `idx_rel` (`rel_type`, `rel_id`),
      KEY `idx_client_id` (`client_id`),
      KEY `idx_contact_id` (`contact_id`),
      KEY `idx_lead_id` (`lead_id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=" . $CI->db->char_set . ';');
}


// Upgrade existing installations with call recording columns.
if ($CI->db->table_exists(db_prefix() . 'unified_call_logs')) {
    $recordingColumns = [
        'recording_file'          => "VARCHAR(255) NULL",
        'recording_original_name' => "VARCHAR(255) NULL",
        'recording_mime'          => "VARCHAR(100) NULL",
        'recording_size'          => "INT UNSIGNED DEFAULT 0",
    ];
    foreach ($recordingColumns as $column => $definition) {
        if (!$CI->db->field_exists($column, db_prefix() . 'unified_call_logs')) {
            $CI->db->query('ALTER TABLE `' . db_prefix() . 'unified_call_logs` ADD `' . $column . '` ' . $definition . ' AFTER `chatwoot_conversation_id`');
        }
    }
}

if (!$CI->db->table_exists(db_prefix() . 'unified_call_outcomes')) {
    $CI->db->query('CREATE TABLE `' . db_prefix() . "unified_call_outcomes` (
      `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
      `name` VARCHAR(100) NOT NULL,
      `color` VARCHAR(20) NULL,
      `is_default` TINYINT(1) DEFAULT 0,
      `is_active` TINYINT(1) DEFAULT 1,
      `sort_order` INT DEFAULT 0,
      PRIMARY KEY (`id`),
      KEY `idx_active_order` (`is_active`, `sort_order`)
    ) ENGINE=InnoDB DEFAULT CHARSET=" . $CI->db->char_set . ';');
}

if (!$CI->db->table_exists(db_prefix() . 'unified_phone_cache')) {
    $CI->db->query('CREATE TABLE `' . db_prefix() . "unified_phone_cache` (
      `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
      `phone_raw` VARCHAR(50) NULL,
      `phone_normalized` VARCHAR(50) NULL,
      `source` VARCHAR(50) NULL,
      `source_id` VARCHAR(100) NULL,
      `payload` LONGTEXT NULL,
      `created_at` DATETIME NULL,
      `expires_at` DATETIME NULL,
      PRIMARY KEY (`id`),
      KEY `idx_phone_source` (`phone_normalized`, `source`),
      KEY `idx_expires` (`expires_at`)
    ) ENGINE=InnoDB DEFAULT CHARSET=" . $CI->db->char_set . ';');
}

$default_options = [
    'unified_phone_enabled' => '1',
    'unified_phone_default_call_type' => 'incoming',
    'unified_phone_default_call_outcome' => 'Connected',
    'unified_phone_normalization_enabled' => '1',
    'unified_phone_like_search_enabled' => '1',
    'unified_phone_chatwoot_enabled' => '0',
    'unified_phone_chatwoot_base_url' => '',
    'unified_phone_chatwoot_account_id' => '',
    'unified_phone_chatwoot_api_token' => '',
    'unified_phone_chatwoot_inbox_id' => '',
    'unified_phone_chatwoot_timeout' => '8',
    'unified_phone_cache_enabled' => '1',
    'unified_phone_cache_duration' => '300',
    'unified_phone_reporting_enabled' => '1',
    'unified_phone_export_enabled' => '1',
    'unified_phone_edit_after_save' => '1',
    'unified_phone_agents_edit_own' => '1',
    'unified_phone_agents_delete_own' => '0',
    'unified_phone_limit_default' => '10',
    'unified_phone_limit_call_logs' => '20',
    'unified_phone_limit_call_logs_history' => '100',
    'unified_phone_required_phone_raw' => '1',
    'unified_phone_required_call_type' => '0',
    'unified_phone_required_outcome' => '0',
    'unified_phone_required_call_date' => '1',
    'unified_phone_required_start_time' => '0',
    'unified_phone_required_end_time' => '0',
    'unified_phone_required_duration_text' => '0',
    'unified_phone_required_rel_type' => '0',
    'unified_phone_required_rel_id' => '0',
    'unified_phone_required_follow_up_datetime' => '0',
    'unified_phone_required_note' => '0',
    'unified_phone_required_call_recording' => '0',
    'unified_phone_sip_enabled' => '1',
    'unified_phone_global_click_to_call_enabled' => '0',
    'unified_phone_sip_uri_scheme' => 'sip',
    'unified_phone_recording_enabled' => '1',
    'unified_phone_recording_max_size' => '10240',
    'unified_phone_recording_allowed_types' => 'mp3,wav,m4a,ogg,webm,mp4',
];


foreach (array_keys(unified_phone_add_log_fields()) as $field) {
    $default_options['unified_phone_show_field_' . $field] = '1';
}

foreach ($default_options as $name => $value) {
    if (get_option($name) === false) {
        add_option($name, $value);
    }
}


// Protect recording upload directory from direct web access; files are served through a permission-checked controller route.
$recording_upload_dir = FCPATH . 'uploads/unified_phone_recordings/';
if (!is_dir($recording_upload_dir)) {
    @mkdir($recording_upload_dir, 0755, true);
}
@file_put_contents($recording_upload_dir . '.htaccess', "Deny from all
");
@file_put_contents($recording_upload_dir . 'index.html', '');

$section_options = [
    'customers', 'contacts', 'leads', 'proposals', 'estimates', 'invoices', 'payments', 'projects', 'contracts',
    'tickets', 'chatwoot', 'call_logs'
];
foreach ($section_options as $section) {
    if (get_option('unified_phone_show_' . $section) === false) {
        add_option('unified_phone_show_' . $section, '1');
    }
    if (get_option('unified_phone_limit_' . $section) === false) {
        add_option('unified_phone_limit_' . $section, $section === 'call_logs' ? '20' : '10');
    }
}
if (get_option('unified_phone_limit_call_logs_history') === false) {
    add_option('unified_phone_limit_call_logs_history', '100');
}

$default_outcomes = [
    ['Connected', '#22c55e', 1, 1],
    ['Not reachable', '#ef4444', 0, 2],
    ['Busy', '#f97316', 0, 3],
    ['Switched off', '#64748b', 0, 4],
    ['Wrong number', '#991b1b', 0, 5],
    ['Interested', '#16a34a', 0, 6],
    ['Not interested', '#6b7280', 0, 7],
    ['Follow-up needed', '#2563eb', 0, 8],
    ['Converted', '#15803d', 0, 9],
    ['Complaint', '#dc2626', 0, 10],
    ['Support request', '#7c3aed', 0, 11],
];

foreach ($default_outcomes as $outcome) {
    $exists = $CI->db->where('name', $outcome[0])->get(db_prefix() . 'unified_call_outcomes')->row();
    if (!$exists) {
        $CI->db->insert(db_prefix() . 'unified_call_outcomes', [
            'name' => $outcome[0],
            'color' => $outcome[1],
            'is_default' => $outcome[2],
            'sort_order' => $outcome[3],
        ]);
    }
}
