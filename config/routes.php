<?php

defined('BASEPATH') or exit('No direct script access allowed');

$route['unified_phone/call_by_sip'] = 'unified_phone/call_by_sip';
$route['unified_phone/search'] = 'unified_phone/search';
$route['unified_phone/add_call_log'] = 'unified_phone/add_call_log';
$route['unified_phone/call_logs'] = 'unified_phone/call_logs';
$route['unified_phone/view_call_log/(:num)'] = 'unified_phone/view_call_log/$1';
$route['unified_phone/edit_call_log/(:num)'] = 'unified_phone/edit_call_log/$1';
$route['unified_phone/recording/(:num)'] = 'unified_phone/recording/$1';
$route['unified_phone/delete_call_log/(:num)'] = 'unified_phone/delete_call_log/$1';
$route['unified_phone/settings'] = 'unified_phone/settings';
$route['unified_phone/add_outcome'] = 'unified_phone/add_outcome';
$route['unified_phone/update_outcome/(:num)'] = 'unified_phone/update_outcome/$1';
$route['unified_phone/delete_outcome/(:num)'] = 'unified_phone/delete_outcome/$1';
$route['unified_phone/reports'] = 'unified_phone/reports';
$route['unified_phone/export/(:any)'] = 'unified_phone/export/$1';
$route['unified_phone/chatwoot_test'] = 'unified_phone/chatwoot_test';
$route['unified_phone/related_search'] = 'unified_phone/related_search';
$route['unified_phone_settings'] = 'unified_phone/settings';
$route['unified_phone_api/normalize'] = 'unified_phone_api/normalize';
