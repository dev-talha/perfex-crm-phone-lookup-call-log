<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php
$totalLogs = isset($total_logs) ? (int) $total_logs : count((array) $call_logs);
$leadPhone = isset($lead->phonenumber) ? (string) $lead->phonenumber : (isset($lead['phonenumber']) ? (string) $lead['phonenumber'] : '');
?>
<div class="row unified-lead-call-log-tab"><div class="col-md-12">
    <div class="clearfix unified-lead-call-log-actions">
        <?php if ($leadPhone !== '' && unified_phone_can('create')) { ?>
            <a href="<?php echo admin_url('unified_phone/search?phone=' . urlencode($leadPhone)); ?>" class="btn btn-primary" target="_blank"><i class="fa fa-plus"></i> <?php echo _l('unified_phone_add_call_log'); ?></a>
        <?php } ?>
        <?php if ($totalLogs > 10 && $leadPhone !== '') { ?>
            <a href="<?php echo admin_url('unified_phone/reports?phone=' . urlencode($leadPhone)); ?>" class="btn btn-default pull-right" target="_blank"><?php echo _l('unified_phone_more_call_logs'); ?></a>
        <?php } ?>
    </div>
    <p class="text-muted unified-lead-call-log-note"><?php echo sprintf(_l('unified_phone_lead_tab_latest_note'), 10); ?></p>
    <?php $this->load->view('unified_phone/partials/call_log_table', ['call_logs' => $call_logs]); ?>
</div></div>
