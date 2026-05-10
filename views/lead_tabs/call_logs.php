<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<div class="row"><div class="col-md-12">
    <?php if (isset($lead->phonenumber) && unified_phone_can('create')) { ?>
        <a href="<?php echo admin_url('unified_phone/search?phone=' . urlencode($lead->phonenumber)); ?>" class="btn btn-primary mbot15" target="_blank"><i class="fa fa-plus"></i> <?php echo _l('unified_phone_add_call_log'); ?></a>
    <?php } ?>
    <?php $this->load->view('unified_phone/partials/call_log_table', ['call_logs' => $call_logs]); ?>
</div></div>
