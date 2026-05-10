<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head(); ?>
<div id="wrapper"><div class="content">
<div class="panel_s"><div class="panel-body">
    <div class="pull-right">
        <?php if (unified_phone_can('export')) { ?>
            <a class="btn btn-default" href="<?php echo admin_url('unified_phone/export/csv?' . http_build_query($filters)); ?>"><i class="fa fa-download"></i> CSV</a>
            <a class="btn btn-default" href="<?php echo admin_url('unified_phone/export/xls?' . http_build_query($filters)); ?>"><i class="fa fa-file-excel-o"></i> Excel</a>
        <?php } ?>
    </div>
    <h3><i class="fa fa-bar-chart text-primary"></i> <?php echo _l('unified_phone_reports'); ?></h3>
    <?php echo form_open(admin_url('unified_phone/reports'), ['method' => 'get', 'class' => 'mbot20']); ?>
    <div class="row">
        <div class="col-md-2"><?php echo render_input('date_from', 'from_date', $filters['date_from'] ?? '', 'date'); ?></div>
        <div class="col-md-2"><?php echo render_input('date_to', 'to_date', $filters['date_to'] ?? '', 'date'); ?></div>
        <div class="col-md-2"><?php echo render_input('phone', 'unified_phone_phone', $filters['phone'] ?? ''); ?></div>
        <div class="col-md-2"><?php echo render_select('call_type', [['id'=>'','name'=>_l('all')],['id'=>'incoming','name'=>_l('incoming')], ['id'=>'outgoing','name'=>_l('outgoing')]], ['id','name'], 'unified_phone_call_type', $filters['call_type'] ?? ''); ?></div>
        <div class="col-md-2"><?php echo render_select('outcome', array_merge([['name'=>'']], $outcomes), ['name','name'], 'unified_phone_outcome', $filters['outcome'] ?? ''); ?></div>
        <div class="col-md-2"><?php echo render_select('staff', $staff, ['id','name'], 'staff', $filters['staff'] ?? ''); ?></div>
    </div>
    <div class="row">
        <?php $relOptions = [['id'=>'','name'=>_l('all')]]; foreach ((array) $related_types as $id => $name) { $relOptions[] = ['id'=>$id, 'name'=>$name]; } ?>
        <div class="col-md-2"><?php echo render_select('rel_type', $relOptions, ['id','name'], 'unified_phone_related_to', $filters['rel_type'] ?? ''); ?></div>
        <div class="col-md-2"><?php echo render_input('follow_up_from', 'unified_phone_follow_up_from', $filters['follow_up_from'] ?? '', 'datetime-local'); ?></div>
        <div class="col-md-2"><?php echo render_input('follow_up_to', 'unified_phone_follow_up_to', $filters['follow_up_to'] ?? '', 'datetime-local'); ?></div>
        <div class="col-md-3 mtop25"><button class="btn btn-primary" type="submit"><i class="fa fa-filter"></i> <?php echo _l('filter'); ?></button> <a class="btn btn-default" href="<?php echo admin_url('unified_phone/reports'); ?>"><i class="fa fa-refresh"></i> <?php echo _l('unified_phone_reset_filter'); ?></a></div>
    </div>
    <?php echo form_close(); ?>
    <div class="row unified-metrics">
        <div class="col-md-2"><div class="metric"><?php echo (int) $metrics['total_calls']; ?><span>Total</span></div></div>
        <div class="col-md-2"><div class="metric"><?php echo (int) $metrics['incoming_calls']; ?><span>Incoming</span></div></div>
        <div class="col-md-2"><div class="metric"><?php echo (int) $metrics['outgoing_calls']; ?><span>Outgoing</span></div></div>
        <div class="col-md-2"><div class="metric"><?php echo (int) $metrics['connected_calls']; ?><span>Connected</span></div></div>
        <div class="col-md-2"><div class="metric"><?php echo unified_phone_format_duration($metrics['average_duration']); ?><span>Avg duration</span></div></div>
        <div class="col-md-2"><div class="metric"><?php echo (int) $metrics['follow_up_pending_count']; ?><span>Follow-up</span></div></div>
    </div>
    <h4><?php echo _l('unified_phone_call_logs'); ?></h4>
    <?php $this->load->view('unified_phone/partials/call_log_table', ['call_logs'=>$metrics['rows']]); ?>
</div></div>
</div></div>
<?php init_tail(); ?>
