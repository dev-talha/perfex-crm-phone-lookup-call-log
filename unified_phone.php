<?php

defined('BASEPATH') or exit('No direct script access allowed');

/*
Module Name: Unified Phone Search & Call Log
Description: Central Bangladesh phone lookup, call logs, CRM context, Chatwoot integration, customer/lead tabs and reporting for Perfex CRM.
Version: 1.20.0
Requires at least: 2.3.*
Author: Custom Development
*/

define('UNIFIED_PHONE_MODULE_NAME', 'unified_phone');
define('UNIFIED_PHONE_VERSION', '1.20.0');

hooks()->add_action('admin_init', 'unified_phone_permissions');
hooks()->add_action('admin_init', 'unified_phone_init_menu_items');
hooks()->add_action('admin_init', 'unified_phone_register_customer_tab');
hooks()->add_action('after_lead_lead_tabs', 'unified_phone_register_lead_tab_menu');
hooks()->add_action('after_lead_tabs_content', 'unified_phone_register_lead_tab_content');
hooks()->add_action('lead_modal_profile_bottom', 'unified_phone_lead_modal_fallback_tab');
hooks()->add_action('app_admin_head', 'unified_phone_add_head_components');
hooks()->add_action('app_admin_footer', 'unified_phone_add_footer_components');
hooks()->add_filter('module_unified_phone_action_links', 'unified_phone_module_action_links');

register_activation_hook(UNIFIED_PHONE_MODULE_NAME, 'unified_phone_module_activation_hook');
register_uninstall_hook(UNIFIED_PHONE_MODULE_NAME, 'unified_phone_module_uninstall_hook');
register_language_files(UNIFIED_PHONE_MODULE_NAME, [UNIFIED_PHONE_MODULE_NAME]);

function unified_phone_module_activation_hook()
{
    require_once(__DIR__ . '/install.php');
}

function unified_phone_module_uninstall_hook()
{
    require_once(__DIR__ . '/uninstall.php');
}

function unified_phone_permissions()
{
    $capabilities = [];
    $capabilities['capabilities'] = [
        'view'         => _l('permission_view') . ' (' . _l('unified_phone_search') . ')',
        'view_detail'  => _l('unified_phone_permission_view_call_log_details'),
        'recordings'   => _l('unified_phone_permission_call_recordings'),
        'create'       => _l('permission_create') . ' (' . _l('unified_phone_call_log') . ')',
        'edit'         => _l('permission_edit') . ' (' . _l('unified_phone_call_log') . ')',
        'delete'       => _l('permission_delete') . ' (' . _l('unified_phone_call_log') . ')',
        'view_reports' => _l('unified_phone_permission_view_reports'),
        'export'       => _l('unified_phone_permission_export'),
        'settings'     => _l('unified_phone_permission_settings'),
        'chatwoot'     => _l('unified_phone_permission_chatwoot'),
    ];
    register_staff_capabilities(UNIFIED_PHONE_MODULE_NAME, $capabilities, _l('unified_phone'));
}

function unified_phone_init_menu_items()
{
    $CI = &get_instance();
    $CI->load->helper(UNIFIED_PHONE_MODULE_NAME . '/unified_phone');

    if (!is_staff_logged_in()) {
        return;
    }

    $isAdmin = is_admin() || unified_phone_can('settings');
    $moduleEnabled = get_option('unified_phone_enabled') === '1';

    if (!$moduleEnabled && !$isAdmin) {
        return;
    }

    $canViewSearch = unified_phone_can('view');
    $canViewReports = unified_phone_can('view_reports');
    $canManageLogs = unified_phone_can('create') || unified_phone_can('edit') || unified_phone_can('delete') || $isAdmin;

    if (!$canViewSearch && !$canViewReports && !$isAdmin) {
        return;
    }

    $CI->app_menu->add_sidebar_menu_item('unified-phone', [
        'name'     => _l('unified_phone_menu'),
        'href'     => $canViewSearch ? admin_url('unified_phone') : admin_url('unified_phone/reports'),
        'position' => 31,
        'icon'     => 'fa fa-phone-square',
    ]);

    if ($canViewSearch) {
        $CI->app_menu->add_sidebar_children_item('unified-phone', [
            'slug'     => 'unified-phone-search',
            'name'     => _l('unified_phone_search'),
            'href'     => admin_url('unified_phone'),
            'position' => 1,
        ]);
        if (get_option('unified_phone_sip_enabled') === '1') {
            $CI->app_menu->add_sidebar_children_item('unified-phone', [
                'slug'     => 'unified-phone-call-by-sip',
                'name'     => _l('unified_phone_call_by_sip'),
                'href'     => admin_url('unified_phone/call_by_sip'),
                'position' => 2,
            ]);
        }
    }

    if ($isAdmin || $canViewReports || $canManageLogs) {
        $CI->app_menu->add_sidebar_children_item('unified-phone', [
            'slug'     => 'unified-phone-reports',
            'name'     => _l('unified_phone_reports'),
            'href'     => admin_url('unified_phone/reports'),
            'position' => 3,
        ]);
    }

    if ($isAdmin) {
        $CI->app_menu->add_sidebar_children_item('unified-phone', [
            'slug'     => 'unified-phone-settings',
            'name'     => _l('settings'),
            'href'     => admin_url('unified_phone/settings'),
            'position' => 4,
        ]);
    }
}

function unified_phone_register_customer_tab()
{
    $CI = &get_instance();
    $CI->load->helper(UNIFIED_PHONE_MODULE_NAME . '/unified_phone');

    if (!is_staff_logged_in() || get_option('unified_phone_enabled') !== '1' || !unified_phone_can('view')) {
        return;
    }

    $CI->app_tabs->add_customer_profile_tab('unified_call_logs', [
        'name'     => _l('unified_phone_call_logs'),
        'icon'     => 'fa fa-phone',
        'view'     => 'unified_phone/customer_groups/call_logs',
        'position' => 91,
        'badge'    => [],
    ]);
}

function unified_phone_register_lead_tab_menu($lead)
{
    $CI = &get_instance();
    $CI->load->helper(UNIFIED_PHONE_MODULE_NAME . '/unified_phone');

    if (!isset($lead->id) || get_option('unified_phone_enabled') !== '1' || !unified_phone_can('view')) {
        return;
    }

    $CI->load->model(UNIFIED_PHONE_MODULE_NAME . '/Unified_call_log_model', 'unified_call_log_model');
    echo '<li role="presentation" class="unified-phone-lead-tab">';
    echo '<a href="#tab_unified_phone_call_logs" aria-controls="tab_unified_phone_call_logs" role="tab" data-toggle="tab">Call Log</a>';
    echo '</li>';
}

function unified_phone_register_lead_tab_content($lead)
{
    if (!isset($lead->id)) {
        return;
    }
    $CI = &get_instance();
    $CI->load->helper(UNIFIED_PHONE_MODULE_NAME . '/unified_phone');
    if (get_option('unified_phone_enabled') !== '1' || !unified_phone_can('view')) {
        return;
    }
    $CI->load->model(UNIFIED_PHONE_MODULE_NAME . '/Unified_call_log_model', 'unified_call_log_model');
    $logs = $CI->unified_call_log_model->get_for_lead_phone($lead, 10);
    $total = $CI->unified_call_log_model->count_for_lead_phone($lead);
    echo '<div role="tabpanel" class="tab-pane" id="tab_unified_phone_call_logs">';
    $CI->load->view('unified_phone/lead_tabs/call_logs', ['lead' => $lead, 'call_logs' => $logs, 'total_logs' => $total]);
    echo '</div>';
}


function unified_phone_lead_modal_fallback_tab($lead_id)
{
    $lead_id = (int) $lead_id;
    if ($lead_id <= 0 || get_option('unified_phone_enabled') !== '1' || !unified_phone_can('view')) {
        return;
    }

    $CI = &get_instance();
    $CI->load->model('leads_model');
    $lead = $CI->leads_model->get($lead_id);
    $count = 0;
    if ($lead) {
        $CI->load->model(UNIFIED_PHONE_MODULE_NAME . '/Unified_call_log_model', 'unified_call_log_model');
        $count = $CI->unified_call_log_model->count_for_lead_phone($lead);
    }
    $badge = $count > 0 ? ' <span class="badge">' . (int) $count . '</span>' : '';
    ?>
    <script>
    (function($){
        $(function(){
            var leadId = <?php echo (int) $lead_id; ?>;
            var badgeHtml = ''; // keep lead modal tab text exactly as Call Log
            var tabId = '#tab_unified_phone_call_logs';
            var $scope = $('input[name="leadid"][value="' + leadId + '"]').closest('.modal-body');
            if (!$scope.length) { $scope = $('#lead-modal'); }
            if (!$scope.length) { $scope = $(document); }

            var $tabs = $scope.find('ul.nav-tabs[role="tablist"]').first();
            var $content = $scope.find('.tab-content').first();
            if (!$tabs.length || !$content.length) { return; }

            var $link = $tabs.find('a[href="' + tabId + '"]');
            if (!$link.length) {
                $tabs.append('<li role="presentation" class="unified-phone-lead-tab"><a href="' + tabId + '" aria-controls="tab_unified_phone_call_logs" role="tab" data-toggle="tab">Call Log</a></li>');
                $link = $tabs.find('a[href="' + tabId + '"]');
            } else {
                $link.html('Call Log');
                $link.closest('li').addClass('unified-phone-lead-tab');
            }

            var $pane = $content.find(tabId);
            if (!$pane.length) {
                $content.append('<div role="tabpanel" class="tab-pane" id="tab_unified_phone_call_logs"><div class="text-muted">Loading...</div></div>');
                $pane = $content.find(tabId);
            }

            var loadLeadCallLogs = function(){
                if ($pane.data('unifiedLoaded')) { return; }
                $pane.data('unifiedLoaded', 1).load(admin_url + 'unified_phone/lead_call_logs_tab/' + leadId);
            };

            $link.off('shown.bs.tab.unifiedPhone').on('shown.bs.tab.unifiedPhone', loadLeadCallLogs);
            if ($link.closest('li').hasClass('active') || $pane.hasClass('active')) { loadLeadCallLogs(); }
        });
    })(jQuery);
    </script>
    <?php
}

function unified_phone_add_head_components()
{
    if (!is_staff_logged_in()) {
        return;
    }

    $CI = &get_instance();
    $CI->load->helper(UNIFIED_PHONE_MODULE_NAME . '/unified_phone');

    if (unified_phone_should_load_assets()) {
        echo '<link href="' . module_dir_url(UNIFIED_PHONE_MODULE_NAME, 'assets/css/unified_phone.css') . '?v=' . UNIFIED_PHONE_VERSION . '" rel="stylesheet" type="text/css" />';
    }
}

function unified_phone_add_footer_components()
{
    if (!is_staff_logged_in()) {
        return;
    }

    $CI = &get_instance();
    $CI->load->helper(UNIFIED_PHONE_MODULE_NAME . '/unified_phone');

    if (!unified_phone_should_load_assets()) {
        return;
    }

    $segment = $CI->uri->segment(2);
    $shouldRenderGlobalModal = $segment !== 'unified_phone'
        && get_option('unified_phone_enabled') === '1'
        && get_option('unified_phone_global_click_to_call_enabled') === '1'
        && unified_phone_can('create');

    if ($shouldRenderGlobalModal) {
        $CI->load->model(UNIFIED_PHONE_MODULE_NAME . '/Unified_call_log_model', 'unified_phone_global_call_log_model');
        $CI->load->view('unified_phone/call_log_modal', [
            'phone_raw' => '',
            'outcomes'  => $CI->unified_phone_global_call_log_model->outcomes(),
            'call_type' => 'outgoing',
        ]);
    }

    if (get_option('unified_phone_floating_call_button_enabled') === '1' && get_option('unified_phone_sip_enabled') === '1' && unified_phone_can('view')) {
        $floatingPosition = unified_phone_floating_button_position();
        $floatingBottom = unified_phone_floating_button_bottom();
        $oppositeSide = $floatingPosition['side'] === 'left' ? 'right' : 'left';
        $floatingStyle = $floatingPosition['side'] . ':' . $floatingPosition['offset'] . ';' . $oppositeSide . ':auto;bottom:' . $floatingBottom . ';';
        echo '<a href="' . admin_url('unified_phone/call_by_sip') . '" class="unified-floating-call-btn" style="' . html_escape($floatingStyle) . '" title="' . html_escape(_l('unified_phone_call_by_sip')) . '"><i class="fa fa-phone"></i></a>';
    }

    echo '<script>window.unifiedPhoneSipScheme = ' . json_encode(unified_phone_sip_scheme()) . '; window.unifiedPhoneGlobalClickToCall = ' . (get_option('unified_phone_global_click_to_call_enabled') === '1' ? 'true' : 'false') . ';</script>';
    echo '<script src="' . module_dir_url(UNIFIED_PHONE_MODULE_NAME, 'assets/js/unified_phone.js') . '?v=' . UNIFIED_PHONE_VERSION . '"></script>';
}

function unified_phone_should_load_assets()
{
    if (!is_staff_logged_in()) {
        return false;
    }

    $CI = &get_instance();
    $segment = $CI->uri->segment(2);

    if ($segment === 'unified_phone') {
        return true;
    }

    if (get_option('unified_phone_enabled') !== '1' && !unified_phone_can('settings')) {
        return false;
    }

    if (unified_phone_can('view') || unified_phone_can('create') || unified_phone_can('view_reports') || unified_phone_can('view_detail') || unified_phone_can('settings')) {
        return true;
    }

    return false;
}


function unified_phone_floating_button_position()
{
    $value = (string) get_option('unified_phone_floating_call_button_position');
    $allowedOffsets = ['-15px','20px','25px','30px','35px','40px','50px'];
    $side = 'right';
    $offset = '20px';

    if (preg_match('/^(left|right):(-15px|20px|25px|30px|35px|40px|50px)$/', $value, $matches)) {
        $side = $matches[1];
        $offset = $matches[2];
    }

    return ['side' => $side, 'offset' => $offset];
}

function unified_phone_floating_button_bottom()
{
    $value = (string) get_option('unified_phone_floating_call_button_bottom');
    $allowed = ['15px','20px','25px','30px','35px','40px','50px'];
    return in_array($value, $allowed, true) ? $value : '20px';
}

function unified_phone_module_action_links($actions)
{
    $actions[] = '<a href="' . admin_url('unified_phone/settings') . '">' . _l('settings') . '</a>';
    $actions[] = '<a href="' . admin_url('unified_phone') . '">' . _l('unified_phone_search') . '</a>';
    return $actions;
}
