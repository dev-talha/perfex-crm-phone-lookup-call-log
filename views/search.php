<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head(); ?>
<div id="wrapper"><div class="content"><div class="row"><div class="col-md-12">
    <div class="panel_s unified-phone-panel"><div class="panel-body text-center">
        <h3><i class="fa fa-phone-square text-primary"></i> <?php echo _l('unified_phone_search'); ?></h3>
        <p class="text-muted"><?php echo _l('unified_phone_search_help'); ?></p>
        <?php echo form_open(admin_url('unified_phone/search'), ['method' => 'get', 'class' => 'unified-phone-search-form']); ?>
            <div class="input-group input-group-lg">
                <input type="text" name="phone" class="form-control" placeholder="01712345678 / 8801712345678" required autofocus>
                <span class="input-group-btn"><button type="submit" class="btn btn-primary"><i class="fa fa-search"></i> <?php echo _l('search'); ?></button></span>
            </div>
        <?php echo form_close(); ?>
        <?php if (unified_phone_can('create')) { ?><button class="btn btn-default mtop15" data-toggle="modal" data-target="#unifiedCallLogModal"><i class="fa fa-plus"></i> <?php echo _l('unified_phone_quick_add_call_log'); ?></button><?php } ?>
    </div></div>
    <?php $this->load->view('unified_phone/call_log_modal', ['phone_raw' => '', 'outcomes' => $outcomes, 'call_type' => $call_type ?? '']); ?>
    <div class="panel_s"><div class="panel-body">
        <h4><?php echo _l('unified_phone_recent_call_logs'); ?></h4>
        <?php $this->load->view('unified_phone/partials/call_log_table', ['call_logs' => $recent_logs]); ?>
    </div></div>
</div></div></div></div>
<?php init_tail(); ?>
