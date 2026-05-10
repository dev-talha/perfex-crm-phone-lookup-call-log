<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php
$CI = &get_instance();
$CI->load->helper('unified_phone/unified_phone');
$CI->load->model('unified_phone/Unified_call_log_model', 'unified_customer_call_log_model');
$clientId = isset($client) && isset($client->userid) ? (int) $client->userid : (isset($customer_id) ? (int) $customer_id : 0);
$call_logs = $clientId ? $CI->unified_customer_call_log_model->get(null, ['client_id' => $clientId], 100) : [];
?>
<div class="row"><div class="col-md-12">
    <?php if ($clientId && unified_phone_can('create')) { ?>
        <a href="<?php echo admin_url('unified_phone?phone=' . urlencode($client->phonenumber ?? '')); ?>" class="btn btn-primary mbot15"><i class="fa fa-plus"></i> <?php echo _l('unified_phone_add_call_log'); ?></a>
    <?php } ?>
    <?php $this->load->view('unified_phone/partials/call_log_table', ['call_logs' => $call_logs]); ?>
</div></div>
