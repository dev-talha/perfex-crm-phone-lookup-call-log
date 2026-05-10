<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head(); ?>
<div id="wrapper"><div class="content">
<div class="panel_s"><div class="panel-body">
    <div class="pull-right"><?php if (unified_phone_can('create')) { ?><button class="btn btn-primary" data-toggle="modal" data-target="#unifiedCallLogModal"><i class="fa fa-plus"></i> <?php echo _l('unified_phone_add_call_log'); ?></button><?php } ?></div>
    <h3><?php echo _l('unified_phone_reports'); ?></h3>
    <?php echo form_open(admin_url('unified_phone/reports'), ['method' => 'get', 'class' => 'mbot20']); ?>
    <div class="row">
        <div class="col-md-2"><?php echo render_input('date_from', 'from_date', $filters['date_from'] ?? '', 'date'); ?></div>
        <div class="col-md-2"><?php echo render_input('date_to', 'to_date', $filters['date_to'] ?? '', 'date'); ?></div>
        <div class="col-md-3"><?php echo render_input('phone', 'unified_phone_phone', $filters['phone'] ?? ''); ?></div>
        <div class="col-md-2"><?php echo render_select('call_type', [['id'=>'','name'=>'All'],['id'=>'incoming','name'=>'Incoming'], ['id'=>'outgoing','name'=>'Outgoing']], ['id','name'], 'unified_phone_call_type', $filters['call_type'] ?? ''); ?></div>
        <div class="col-md-2"><?php echo render_select('outcome', array_merge([['name'=>'']], $outcomes), ['name','name'], 'unified_phone_outcome', $filters['outcome'] ?? ''); ?></div>
        <div class="col-md-1"><button class="btn btn-primary mtop25" type="submit"><?php echo _l('filter'); ?></button></div>
    </div>
    <?php echo form_close(); ?>
    <?php $this->load->view('unified_phone/partials/call_log_table', ['call_logs'=>$call_logs]); ?>
</div></div>
<?php $this->load->view('unified_phone/call_log_modal', ['phone_raw' => '', 'outcomes' => $outcomes, 'call_type' => $call_type ?? '']); ?>
</div></div>
<?php init_tail(); ?>
