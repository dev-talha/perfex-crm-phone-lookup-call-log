<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php
$CI = get_instance();
$modal_phone_raw = $phone_raw ?? $CI->input->get('phone', true) ?? '';
$selected_call_type = isset($call_type) ? strtolower((string) $call_type) : strtolower((string) $CI->input->get('calltype', true));
if (!in_array($selected_call_type, ['incoming', 'outgoing'], true)) {
    $selected_call_type = '';
}
$selected_rel_type = strtolower((string) ($rel_type ?? $CI->input->get('rel_type', true) ?? ''));
$selected_rel_id = (int) ($rel_id ?? $CI->input->get('rel_id', true) ?? 0);
$related_types = [['id' => '', 'name' => _l('dropdown_non_selected_tex')]];
foreach (unified_phone_related_types() as $id => $name) {
    $related_types[] = ['id' => $id, 'name' => $name];
}
$selectedRelLabel = $selected_rel_type && $selected_rel_id ? unified_phone_related_record_name($selected_rel_type, $selected_rel_id) : '';
?>
<div class="modal fade" id="unifiedCallLogModal" tabindex="-1" role="dialog">
  <div class="modal-dialog modal-lg unified-call-log-modal" role="document"><div class="modal-content">
    <?php echo form_open_multipart(admin_url('unified_phone/add_call_log'), ['id' => 'unified-call-log-form']); ?>
    <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal">&times;</button>
        <h4 class="modal-title"><i class="fa fa-phone"></i> <?php echo _l('unified_phone_add_call_log'); ?></h4>
    </div>
    <div class="modal-body">
      <div class="row">
        <div class="<?php echo unified_phone_field_col_class('phone_raw', 'col-md-4'); ?>"><?php echo render_input('phone_raw', 'unified_phone_phone', $modal_phone_raw, 'text', unified_phone_required_attr('phone_raw')); ?></div>
        <div class="<?php echo unified_phone_field_col_class('call_type', 'col-md-4'); ?>"><?php echo render_select('call_type', [['id'=>'','name'=>_l('dropdown_non_selected_tex')], ['id'=>'incoming','name'=>_l('incoming')], ['id'=>'outgoing','name'=>_l('outgoing')]], ['id','name'], 'unified_phone_call_type', $selected_call_type, unified_phone_required_attr('call_type')); ?></div>
        <div class="<?php echo unified_phone_field_col_class('outcome', 'col-md-4'); ?>"><?php echo render_select('outcome', $outcomes, ['name','name'], 'unified_phone_outcome', get_option('unified_phone_default_call_outcome'), unified_phone_required_attr('outcome')); ?></div>

        <div class="<?php echo unified_phone_field_col_class('call_date', 'col-md-3'); ?>"><?php echo render_input('call_date', 'unified_phone_call_date', unified_phone_date_input(), 'date', unified_phone_required_attr('call_date')); ?></div>
        <div class="<?php echo unified_phone_field_col_class('start_time', 'col-md-3'); ?>"><?php echo render_input('start_time', 'unified_phone_start_time', unified_phone_time_input(), 'time', array_merge(['step' => 1], unified_phone_required_attr('start_time'))); ?></div>
        <div class="<?php echo unified_phone_field_col_class('end_time', 'col-md-3'); ?>"><?php echo render_input('end_time', 'unified_phone_end_time', unified_phone_time_input(), 'time', array_merge(['step' => 1], unified_phone_required_attr('end_time'))); ?></div>
        <div class="<?php echo unified_phone_field_col_class('duration_text', 'col-md-3'); ?>"><?php echo render_input('duration_text', 'unified_phone_duration', '00:00:00', 'text', unified_phone_required_attr('duration_text')); ?></div>

        <div class="<?php echo unified_phone_field_col_class('rel_type', 'col-md-3'); ?>"><?php echo render_select('rel_type', $related_types, ['id','name'], 'unified_phone_related_to', $selected_rel_type, unified_phone_required_attr('rel_type')); ?></div>
        <div class="<?php echo unified_phone_field_col_class('rel_id', 'col-md-3'); ?>">
            <label for="unified_rel_id"><?php echo _l('unified_phone_related_id'); ?></label>
            <select name="rel_id" id="unified_rel_id" class="selectpicker ajax-search" data-width="100%" data-live-search="true" data-none-selected-text="<?php echo _l('dropdown_non_selected_tex'); ?>" <?php echo unified_phone_is_required_field('rel_id') ? 'required' : ''; ?>>
                <?php if ($selected_rel_id > 0) { ?><option value="<?php echo (int) $selected_rel_id; ?>" selected><?php echo html_escape($selectedRelLabel ?: ('#' . $selected_rel_id)); ?></option><?php } ?>
            </select>
        </div>
        <div class="<?php echo unified_phone_field_col_class('follow_up_datetime', 'col-md-3'); ?>"><?php echo render_input('follow_up_datetime', 'unified_phone_follow_up_datetime', '', 'datetime-local', unified_phone_required_attr('follow_up_datetime')); ?></div>
        <?php if (get_option('unified_phone_recording_enabled') === '1' && unified_phone_can('recordings')) { ?>
        <div class="<?php echo unified_phone_field_col_class('call_recording', 'col-md-3'); ?>">
            <?php $recordingAllowedTypes = get_option('unified_phone_recording_allowed_types') ?: 'mp3,wav,m4a,ogg,webm,mp4'; $recordingMaxSize = (int) get_option('unified_phone_recording_max_size'); $recordingHelp = sprintf(_l('unified_phone_recording_help'), html_escape($recordingAllowedTypes), $recordingMaxSize); ?>
            <label for="call_recording" class="unified-recording-label">
                <?php echo _l('unified_phone_call_recording'); ?>
                <a href="#" class="unified-recording-info" data-toggle="tooltip" title="<?php echo html_escape($recordingHelp); ?>" onclick="return false;">i</a>
            </label>
            <input type="file" name="call_recording" id="call_recording" class="form-control unified-recording-input" data-max-kb="<?php echo (int) $recordingMaxSize; ?>" data-allowed-types="<?php echo html_escape($recordingAllowedTypes); ?>" <?php echo unified_phone_is_required_field('call_recording') ? 'required' : ''; ?> />
            <small class="help-block text-muted unified-recording-note"><?php echo $recordingHelp; ?></small>
        </div>
        <?php } ?>
        <div class="<?php echo unified_phone_field_col_class('note', 'col-md-12'); ?>"><?php echo render_textarea('note', 'unified_phone_note', '', unified_phone_required_attr('note')); ?></div>
      </div>
    </div>
    <div class="modal-footer">
        <button type="button" class="btn btn-default" data-dismiss="modal"><?php echo _l('close'); ?></button>
        <button type="submit" class="btn btn-primary"><i class="fa fa-save"></i> <?php echo _l('save'); ?></button>
    </div>
    <?php echo form_close(); ?>
  </div></div>
</div>
