<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head(); ?>
<?php
$related_types = [['id' => '', 'name' => _l('dropdown_non_selected_tex')]];
foreach (unified_phone_related_types() as $id => $name) {
    $related_types[] = ['id' => $id, 'name' => $name];
}
$relatedLabel = !empty($log['rel_type']) && !empty($log['rel_id']) ? unified_phone_related_record_name($log['rel_type'], $log['rel_id']) : '';
?>
<div id="wrapper"><div class="content"><div class="panel_s"><div class="panel-body">
<div class="clearfix mbot15">
    <h3 class="pull-left no-margin"><i class="fa fa-phone"></i> <?php echo _l('edit', _l('unified_phone_call_log')); ?></h3>
    <button type="button" onclick="history.back();" class="btn btn-default pull-right"><?php echo _l('back'); ?></button>
</div>
<hr class="hr-panel-heading" />
<?php echo form_open_multipart(admin_url('unified_phone/edit_call_log/' . (int) $log['id']), ['id' => 'unified-call-log-form']); ?>
<div class="row unified-call-log-edit-form">
    <div class="<?php echo unified_phone_field_col_class('phone_raw', 'col-md-4'); ?>"><?php echo render_input('phone_raw', 'unified_phone_phone', $log['phone_raw'], 'text', unified_phone_required_attr('phone_raw')); ?></div>
    <div class="<?php echo unified_phone_field_col_class('call_type', 'col-md-4'); ?>"><?php echo render_select('call_type', [['id'=>'','name'=>_l('dropdown_non_selected_tex')], ['id'=>'incoming','name'=>_l('incoming')], ['id'=>'outgoing','name'=>_l('outgoing')]], ['id','name'], 'unified_phone_call_type', $log['call_type'] ?: '', unified_phone_required_attr('call_type')); ?></div>
    <div class="<?php echo unified_phone_field_col_class('outcome', 'col-md-4'); ?>"><?php echo render_select('outcome', $outcomes, ['name','name'], 'unified_phone_outcome', $log['outcome'], unified_phone_required_attr('outcome')); ?></div>

    <div class="<?php echo unified_phone_field_col_class('call_date', 'col-md-3'); ?>"><?php echo render_input('call_date', 'unified_phone_call_date', unified_phone_date_input($log['call_datetime']), 'date', unified_phone_required_attr('call_date')); ?></div>
    <div class="<?php echo unified_phone_field_col_class('start_time', 'col-md-3'); ?>"><?php echo render_input('start_time', 'unified_phone_start_time', unified_phone_time_input($log['start_time']), 'time', array_merge(['step' => 1], unified_phone_required_attr('start_time'))); ?></div>
    <div class="<?php echo unified_phone_field_col_class('end_time', 'col-md-3'); ?>"><?php echo render_input('end_time', 'unified_phone_end_time', unified_phone_time_input($log['end_time']), 'time', array_merge(['step' => 1], unified_phone_required_attr('end_time'))); ?></div>
    <div class="<?php echo unified_phone_field_col_class('duration_text', 'col-md-3'); ?>"><?php echo render_input('duration_text', 'unified_phone_duration', $log['duration_text'], 'text', unified_phone_required_attr('duration_text')); ?></div>

    <div class="<?php echo unified_phone_field_col_class('rel_type', 'col-md-3'); ?>"><?php echo render_select('rel_type', $related_types, ['id','name'], 'unified_phone_related_to', $log['rel_type'], unified_phone_required_attr('rel_type')); ?></div>
    <div class="<?php echo unified_phone_field_col_class('rel_id', 'col-md-3'); ?>">
        <label for="unified_rel_id"><?php echo _l('unified_phone_related_id'); ?></label>
        <select name="rel_id" id="unified_rel_id" class="selectpicker ajax-search" data-width="100%" data-live-search="true" data-none-selected-text="<?php echo _l('dropdown_non_selected_tex'); ?>" <?php echo unified_phone_is_required_field('rel_id') ? 'required' : ''; ?>>
            <?php if (!empty($log['rel_id'])) { ?><option value="<?php echo (int) $log['rel_id']; ?>" selected><?php echo html_escape($relatedLabel ?: ('#' . (int) $log['rel_id'])); ?></option><?php } ?>
        </select>
    </div>
    <div class="<?php echo unified_phone_field_col_class('follow_up_datetime', 'col-md-3'); ?>"><?php echo render_input('follow_up_datetime', 'unified_phone_follow_up_datetime', !empty($log['follow_up_datetime']) ? date('Y-m-d\TH:i', strtotime($log['follow_up_datetime'])) : '', 'datetime-local', unified_phone_required_attr('follow_up_datetime')); ?></div>
    <?php if (get_option('unified_phone_recording_enabled') === '1' && unified_phone_can('recordings')) { ?>
    <div class="<?php echo unified_phone_field_col_class('call_recording', 'col-md-3'); ?>">
        <?php $recordingAllowedTypes = get_option('unified_phone_recording_allowed_types') ?: 'mp3,wav,m4a,ogg,webm,mp4'; $recordingMaxSize = (int) get_option('unified_phone_recording_max_size'); $recordingHelp = sprintf(_l('unified_phone_recording_help'), html_escape($recordingAllowedTypes), $recordingMaxSize); ?>
        <label for="call_recording" class="unified-recording-label">
            <?php echo _l('unified_phone_call_recording'); ?>
            <a href="#" class="unified-recording-info" data-toggle="tooltip" title="<?php echo html_escape($recordingHelp); ?>" onclick="return false;">i</a>
        </label>
        <input type="file" name="call_recording" id="call_recording" class="form-control unified-recording-input" data-max-kb="<?php echo (int) $recordingMaxSize; ?>" data-allowed-types="<?php echo html_escape($recordingAllowedTypes); ?>" <?php echo unified_phone_is_required_field('call_recording') && empty($log['recording_file']) ? 'required' : ''; ?> />
        <small class="help-block text-muted unified-recording-note"><?php echo $recordingHelp; ?></small>
        <?php if (!empty($log['recording_file'])) { ?><small class="help-block"><a target="_blank" rel="noopener" href="<?php echo admin_url('unified_phone/recording/' . (int) $log['id']); ?>"><?php echo html_escape($log['recording_original_name'] ?: basename($log['recording_file'])); ?></a></small><?php } ?>
    </div>
    <?php } ?>
    <div class="<?php echo unified_phone_field_col_class('note', 'col-md-12'); ?>"><?php echo render_textarea('note', 'unified_phone_note', $log['note'], unified_phone_required_attr('note')); ?></div>
</div>
<div class="text-right">
    <button type="button" onclick="history.back();" class="btn btn-default"><?php echo _l('cancel'); ?></button>
    <button class="btn btn-primary" type="submit"><i class="fa fa-save"></i> <?php echo _l('save'); ?></button>
</div>
<?php echo form_close(); ?>
</div></div></div></div>
<?php init_tail(); ?>
