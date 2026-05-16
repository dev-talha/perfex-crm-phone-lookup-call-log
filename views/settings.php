<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head(); ?>
<div id="wrapper"><div class="content unified-phone-settings">
<div class="row"><div class="col-md-12">
<div class="panel_s"><div class="panel-body">
    <div class="clearfix">
        <h3 class="pull-left"><i class="fa fa-phone-square text-primary"></i> <?php echo _l('unified_phone_settings'); ?></h3>
        <a href="<?php echo admin_url('unified_phone'); ?>" class="btn btn-default pull-right mtop10"><i class="fa fa-search"></i> <?php echo _l('unified_phone_search'); ?></a>
    </div>
    <hr class="hr-panel-heading" />
    <?php echo form_open(admin_url('unified_phone/settings')); ?>
    <?php
    $floating_position_options = [];
    foreach (['left' => 'Left', 'right' => 'Right'] as $side_value => $side_label) {
        foreach (['-15px','20px','25px','30px','35px','40px','50px'] as $offset_value) {
            $floating_position_options[] = ['id' => $side_value . ':' . $offset_value, 'name' => $side_label . ' ' . $offset_value];
        }
    }
    $floating_bottom_options = [];
    foreach (['15px','20px','25px','30px','35px','40px','50px','60px','70px','80px','90px','100px'] as $bottom_value) {
        $floating_bottom_options[] = ['id' => $bottom_value, 'name' => $bottom_value];
    }
    ?>

    <ul class="nav nav-tabs unified-settings-tabs" role="tablist">
        <li role="presentation" class="active"><a href="#up-general" aria-controls="up-general" role="tab" data-toggle="tab"><i class="fa fa-sliders"></i> <?php echo _l('settings_group_general'); ?></a></li>
        <li role="presentation"><a href="#up-results" aria-controls="up-results" role="tab" data-toggle="tab"><i class="fa fa-eye"></i> <?php echo _l('unified_phone_result_visibility'); ?></a></li>
        <li role="presentation"><a href="#up-form" aria-controls="up-form" role="tab" data-toggle="tab"><i class="fa fa-list-check"></i> <?php echo _l('unified_phone_call_log_form_settings'); ?></a></li>
        <li role="presentation"><a href="#up-sip" aria-controls="up-sip" role="tab" data-toggle="tab"><i class="fa fa-phone"></i> <?php echo _l('unified_phone_sip_recording_settings'); ?></a></li>
        <li role="presentation"><a href="#up-chatwoot" aria-controls="up-chatwoot" role="tab" data-toggle="tab"><i class="fa fa-comments"></i> Unichat</a></li>
        <li role="presentation"><a href="#up-permissions" aria-controls="up-permissions" role="tab" data-toggle="tab"><i class="fa fa-lock"></i> <?php echo _l('permissions'); ?></a></li>
    </ul>

    <div class="tab-content unified-settings-tab-content">
        <div role="tabpanel" class="tab-pane active" id="up-general">
            <div class="row">
                <div class="col-md-3"><?php render_yes_no_option('unified_phone_enabled', 'unified_phone_enabled'); ?></div>
                <div class="col-md-3"><?php echo render_select('unified_phone_default_call_outcome', $outcomes, ['name','name'], 'unified_phone_default_call_outcome', get_option('unified_phone_default_call_outcome')); ?></div>
                <div class="col-md-3"><?php render_yes_no_option('unified_phone_like_search_enabled', 'unified_phone_like_search_enabled'); ?></div>
                <div class="col-md-3"><?php render_yes_no_option('unified_phone_normalization_enabled', 'unified_phone_normalization_enabled'); ?></div>
            </div>
            <div class="row">
                <div class="col-md-3"><?php render_yes_no_option('unified_phone_reporting_enabled', 'unified_phone_reporting_enabled'); ?></div>
                <div class="col-md-3"><?php render_yes_no_option('unified_phone_export_enabled', 'unified_phone_export_enabled'); ?></div>
                <div class="col-md-3"><?php render_yes_no_option('unified_phone_edit_after_save', 'unified_phone_edit_after_save'); ?></div>
                <div class="col-md-3"><?php render_yes_no_option('unified_phone_floating_call_button_enabled', 'unified_phone_floating_call_button_enabled'); ?></div>
            </div>
            <div class="row">
                <div class="col-md-3"><?php echo render_select('unified_phone_floating_call_button_position', $floating_position_options, ['id','name'], 'unified_phone_floating_call_button_position', get_option('unified_phone_floating_call_button_position') ?: 'right:20px'); ?></div>
                <div class="col-md-3"><?php echo render_select('unified_phone_floating_call_button_bottom', $floating_bottom_options, ['id','name'], 'unified_phone_floating_call_button_bottom', get_option('unified_phone_floating_call_button_bottom') ?: '20px'); ?></div>
            </div>
        </div>

        <div role="tabpanel" class="tab-pane" id="up-results">
            <p class="text-muted"><?php echo _l('unified_phone_result_visibility_help'); ?></p>
            <div class="table-responsive">
                <table class="table table-bordered table-condensed unified-settings-table">
                    <thead><tr><th><?php echo _l('unified_phone_result_section'); ?></th><th class="text-center"><?php echo _l('unified_phone_show_on_search'); ?></th><th style="width:180px"><?php echo _l('unified_phone_records_limit'); ?></th></tr></thead>
                    <tbody>
                    <?php foreach ((array) $result_sections as $section => $label) { ?>
                        <tr>
                            <td><strong><?php echo $section === 'chatwoot' ? 'Unichat' : (_l($label) !== $label ? _l($label) : html_escape(ucwords(str_replace('_', ' ', $label)))); ?></strong><br><small class="text-muted"><?php echo html_escape('unified_phone_show_' . $section); ?></small></td>
                            <td class="text-center"><?php render_yes_no_option('unified_phone_show_' . $section, ''); ?></td>
                            <td><?php echo render_input('unified_phone_limit_' . $section, '', get_option('unified_phone_limit_' . $section) ?: ($section === 'call_logs' ? 20 : 10), 'number', ['min' => 1, 'max' => 100]); ?></td>
                        </tr>
                    <?php } ?>
                    <tr>
                        <td><strong><?php echo _l('unified_phone_call_history_limit'); ?></strong><br><small class="text-muted">unified_phone_limit_call_logs_history</small></td>
                        <td class="text-center text-muted">-</td>
                        <td><?php echo render_input('unified_phone_limit_call_logs_history', '', get_option('unified_phone_limit_call_logs_history') ?: 100, 'number', ['min' => 20, 'max' => 100]); ?></td>
                    </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <div role="tabpanel" class="tab-pane" id="up-form">
            <p class="text-muted"><?php echo _l('unified_phone_required_fields_help'); ?></p>
            <div class="table-responsive">
                <table class="table table-bordered table-condensed unified-settings-table">
                    <thead><tr><th><?php echo _l('unified_phone_field_name'); ?></th><th class="text-center"><?php echo _l('unified_phone_show_field'); ?></th><th class="text-center"><?php echo _l('unified_phone_required_field'); ?></th></tr></thead>
                    <tbody>
                    <?php foreach (unified_phone_add_log_fields() as $field => $label) { ?>
                        <tr>
                            <td><strong><?php echo _l($label); ?></strong><br><small class="text-muted"><?php echo html_escape($field); ?></small></td>
                            <td class="text-center"><?php render_yes_no_option('unified_phone_show_field_' . $field, ''); ?></td>
                            <td class="text-center"><?php render_yes_no_option('unified_phone_required_' . $field, ''); ?></td>
                        </tr>
                    <?php } ?>
                    </tbody>
                </table>
            </div>
        </div>

        <div role="tabpanel" class="tab-pane" id="up-sip">
            <div class="row">
                <div class="col-md-3"><?php render_yes_no_option('unified_phone_sip_enabled', 'unified_phone_sip_enabled'); ?></div>
                <div class="col-md-3"><?php render_yes_no_option('unified_phone_global_click_to_call_enabled', 'unified_phone_global_click_to_call_enabled'); ?></div>
                <div class="col-md-3"><?php echo render_select('unified_phone_sip_uri_scheme', [['id'=>'sip','name'=>'sip:'], ['id'=>'tel','name'=>'tel:'], ['id'=>'callto','name'=>'callto:']], ['id','name'], 'unified_phone_sip_uri_scheme', unified_phone_sip_scheme()); ?></div>
                <div class="col-md-3"><?php render_yes_no_option('unified_phone_recording_enabled', 'unified_phone_recording_enabled'); ?></div>
            </div>
            <div class="row">
                <div class="col-md-3"><?php echo render_input('unified_phone_recording_max_size', 'unified_phone_recording_max_size', get_option('unified_phone_recording_max_size') ?: 10240, 'number', ['min'=>512, 'max'=>($server_upload_limit_kb > 0 ? $server_upload_limit_kb : 102400)]); ?><small class="text-muted"><?php echo sprintf(_l('unified_phone_server_upload_limit'), (int) $server_upload_limit_kb); ?></small></div>
                <div class="col-md-6"><?php echo render_input('unified_phone_recording_allowed_types', 'unified_phone_recording_allowed_types', get_option('unified_phone_recording_allowed_types') ?: 'mp3,wav,m4a,ogg,webm,mp4'); ?></div>
            </div>
            <div class="alert alert-info"><?php echo _l('unified_phone_sip_settings_help'); ?><br><?php echo _l('unified_phone_recording_permission_note'); ?></div>
        </div>

        <div role="tabpanel" class="tab-pane" id="up-chatwoot">
            <div class="row">
                <div class="col-md-3"><?php render_yes_no_option('unified_phone_chatwoot_enabled', 'unified_phone_unichat_enabled'); ?></div>
                <div class="col-md-3"><?php echo render_input('unified_phone_chatwoot_base_url', 'unified_phone_unichat_base_url', get_option('unified_phone_chatwoot_base_url')); ?></div>
                <div class="col-md-2"><?php echo render_input('unified_phone_chatwoot_account_id', 'unified_phone_unichat_account_id', get_option('unified_phone_chatwoot_account_id')); ?></div>
                <div class="col-md-4"><?php echo render_input('unified_phone_chatwoot_api_token', 'unified_phone_unichat_api_token', get_option('unified_phone_chatwoot_api_token'), 'password'); ?></div>
            </div>
            <div class="row">
                <div class="col-md-3"><?php echo render_input('unified_phone_chatwoot_timeout', 'unified_phone_unichat_timeout_seconds', get_option('unified_phone_chatwoot_timeout'), 'number'); ?></div>
                <div class="col-md-3"><div class="alert alert-info mtop25"><?php echo _l('unified_phone_unichat_inbox_auto_note'); ?></div></div>
                <div class="col-md-3"><?php render_yes_no_option('unified_phone_cache_enabled', 'unified_phone_unichat_cache_enabled'); ?></div>
                <div class="col-md-3"><?php echo render_input('unified_phone_cache_duration', 'unified_phone_unichat_cache_duration_seconds', get_option('unified_phone_cache_duration'), 'number'); ?></div>
            </div>
        </div>

        <div role="tabpanel" class="tab-pane" id="up-permissions">
            <div class="row">
                <div class="col-md-3"><?php render_yes_no_option('unified_phone_agents_edit_own', 'unified_phone_agents_edit_own'); ?></div>
                <div class="col-md-3"><?php render_yes_no_option('unified_phone_agents_delete_own', 'unified_phone_agents_delete_own'); ?></div>
            </div>
            <p class="text-muted"><?php echo _l('unified_phone_settings_storage_note'); ?></p>
        </div>
    </div>
    <hr />
    <button class="btn btn-primary" type="submit"><i class="fa fa-check"></i> <?php echo _l('save'); ?></button>
    <?php echo form_close(); ?>
</div></div>

<div class="panel_s"><div class="panel-body" id="call-outcomes">
<h4 class="unified-section-title"><i class="fa fa-list-ul"></i> <?php echo _l('unified_phone_call_outcomes'); ?></h4>
<p class="text-muted"><?php echo _l('unified_phone_call_outcomes_help'); ?></p>
<?php echo form_open(admin_url('unified_phone/add_outcome'), ['class' => 'mbot20']); ?>
<div class="row">
    <div class="col-md-4"><?php echo render_input('name', 'name'); ?></div>
    <div class="col-md-2"><?php echo render_input('color', 'unified_phone_color', '#2563eb'); ?></div>
    <div class="col-md-2"><?php echo render_input('sort_order', 'unified_phone_sort_order', '0', 'number'); ?></div>
    <div class="col-md-2"><div class="checkbox checkbox-primary mtop25"><input type="checkbox" name="is_default" value="1" id="is_default"><label for="is_default"><?php echo _l('is_default'); ?></label></div></div>
    <div class="col-md-2"><button class="btn btn-primary mtop25" type="submit"><i class="fa fa-plus"></i> <?php echo _l('add'); ?></button></div>
</div>
<?php echo form_close(); ?>

<div class="table-responsive">
<table class="table table-striped table-hover">
<thead>
<tr>
    <th><?php echo _l('name'); ?></th>
    <th><?php echo _l('unified_phone_color'); ?></th>
    <th><?php echo _l('unified_phone_sort_order'); ?></th>
    <th><?php echo _l('is_default'); ?></th>
    <th><?php echo _l('active'); ?></th>
    <th class="text-right"><?php echo _l('options'); ?></th>
</tr>
</thead>
<tbody>
<?php foreach ($outcomes as $outcome) { ?>
<tr>
    <td><?php echo html_escape($outcome['name']); ?></td>
    <td><span class="unified-color-dot" style="background:<?php echo html_escape($outcome['color']); ?>"></span> <?php echo html_escape($outcome['color']); ?></td>
    <td><?php echo (int) $outcome['sort_order']; ?></td>
    <td><?php echo (int) $outcome['is_default'] ? _l('yes') : _l('no'); ?></td>
    <td><?php echo (int) $outcome['is_active'] ? _l('yes') : _l('no'); ?></td>
    <td class="text-right">
        <button type="button" class="btn btn-default btn-icon" data-toggle="modal" data-target="#editOutcomeModal<?php echo (int) $outcome['id']; ?>" title="<?php echo _l('edit'); ?>"><i class="fa fa-pen-to-square"></i></button>
        <a href="<?php echo admin_url('unified_phone/delete_outcome/' . (int) $outcome['id']); ?>" class="btn btn-danger btn-icon _delete" title="<?php echo _l('delete'); ?>"><i class="fa fa-remove"></i></a>
    </td>
</tr>
<?php } ?>
<?php if (empty($outcomes)) { ?>
<tr><td colspan="6" class="text-center text-muted"><?php echo _l('no_results_found'); ?></td></tr>
<?php } ?>
</tbody>
</table>
</div>

<?php foreach ($outcomes as $outcome) { ?>
<div class="modal fade" id="editOutcomeModal<?php echo (int) $outcome['id']; ?>" tabindex="-1" role="dialog" aria-labelledby="editOutcomeLabel<?php echo (int) $outcome['id']; ?>">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <?php echo form_open(admin_url('unified_phone/update_outcome/' . (int) $outcome['id'])); ?>
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="<?php echo _l('close'); ?>"><span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title" id="editOutcomeLabel<?php echo (int) $outcome['id']; ?>"><?php echo _l('unified_phone_edit_call_outcome'); ?></h4>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-md-12"><?php echo render_input('name', 'name', $outcome['name']); ?></div>
                    <div class="col-md-6"><?php echo render_input('color', 'unified_phone_color', $outcome['color']); ?></div>
                    <div class="col-md-6"><?php echo render_input('sort_order', 'unified_phone_sort_order', $outcome['sort_order'], 'number'); ?></div>
                    <div class="col-md-6"><div class="checkbox checkbox-primary"><input type="checkbox" name="is_default" value="1" id="is_default_<?php echo (int) $outcome['id']; ?>" <?php echo (int) $outcome['is_default'] ? 'checked' : ''; ?>><label for="is_default_<?php echo (int) $outcome['id']; ?>"><?php echo _l('is_default'); ?></label></div></div>
                    <div class="col-md-6"><div class="checkbox checkbox-primary"><input type="checkbox" name="is_active" value="1" id="is_active_<?php echo (int) $outcome['id']; ?>" <?php echo (int) $outcome['is_active'] ? 'checked' : ''; ?>><label for="is_active_<?php echo (int) $outcome['id']; ?>"><?php echo _l('active'); ?></label></div></div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal"><?php echo _l('close'); ?></button>
                <button type="submit" class="btn btn-primary"><?php echo _l('save'); ?></button>
            </div>
            <?php echo form_close(); ?>
        </div>
    </div>
</div>
<?php } ?>
</div></div>
</div></div>
</div></div>
<?php init_tail(); ?>
