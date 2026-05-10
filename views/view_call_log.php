<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head(); ?>
<div id="wrapper"><div class="content"><div class="row"><div class="col-md-8 col-md-offset-2">
    <div class="panel_s"><div class="panel-body">
        <div class="pull-right">
            <button type="button" class="btn btn-default" onclick="history.back();"><?php echo _l('back'); ?></button>
            <?php if (unified_phone_can('edit')) { ?><a href="<?php echo admin_url('unified_phone/edit_call_log/' . (int) $log['id']); ?>" class="btn btn-default"><i class="fa fa-pencil"></i> <?php echo _l('edit'); ?></a><?php } ?>
        </div>
        <h3><?php echo _l('unified_phone_call_log_details'); ?> #<?php echo (int) $log['id']; ?></h3>
        <hr />
        <div class="row">
            <div class="col-md-6">
                <p><strong><?php echo _l('unified_phone_call_datetime'); ?>:</strong><br><?php echo html_escape(unified_phone_safe_date($log['call_datetime'] ?? '')); ?></p>
                <p><strong><?php echo _l('unified_phone_phone'); ?>:</strong><br><?php echo html_escape($log['phone_raw'] ?? ''); ?></p>
                <p><strong><?php echo _l('unified_phone_normalized_phone'); ?>:</strong><br><?php echo html_escape($log['phone_normalized'] ?? '-'); ?></p>
                <p><strong><?php echo _l('unified_phone_call_type'); ?>:</strong><br><?php echo html_escape(ucfirst($log['call_type'] ?? '-')); ?></p>
            </div>
            <div class="col-md-6">
                <p><strong><?php echo _l('unified_phone_start_time'); ?>:</strong><br><?php echo html_escape(unified_phone_format_time($log['start_time'] ?? '')); ?></p>
                <p><strong><?php echo _l('unified_phone_end_time'); ?>:</strong><br><?php echo html_escape(unified_phone_format_time($log['end_time'] ?? '')); ?></p>
                <p><strong><?php echo _l('unified_phone_duration'); ?>:</strong><br><?php echo html_escape($log['duration_text'] ?? unified_phone_format_duration($log['duration_seconds'] ?? 0)); ?></p>
                <p><strong><?php echo _l('staff'); ?>:</strong><br><?php echo html_escape($log['staff_name'] ?? $log['created_by'] ?? '-'); ?></p>
            </div>
        </div>
        <hr />
        <p><strong><?php echo _l('unified_phone_outcome'); ?>:</strong><br><?php if (!empty($log['outcome'])) { ?><span class="unified-outcome-badge" style="background:<?php echo html_escape($log['outcome_color'] ?? '#64748b'); ?>"><?php echo html_escape($log['outcome']); ?></span><?php } else { echo '-'; } ?></p>
        <?php $related = function_exists('unified_phone_call_log_related') ? unified_phone_call_log_related($log) : ['label' => (($log['rel_type'] ?? '-') . (!empty($log['rel_id']) ? ' #' . $log['rel_id'] : '')), 'url' => '']; ?>
        <p><strong><?php echo _l('unified_phone_related_to'); ?>:</strong><br>
            <?php if (!empty($related['url']) && $related['label'] !== '-') { ?>
                <a href="<?php echo $related['url']; ?>"><?php echo html_escape($related['label']); ?></a>
            <?php } else { ?>
                <?php echo html_escape($related['label']); ?>
            <?php } ?>
        </p>
        <?php if (!empty($log['follow_up_datetime'])) { ?><p><strong><?php echo _l('unified_phone_follow_up_datetime'); ?>:</strong><br><?php echo html_escape(unified_phone_safe_date($log['follow_up_datetime'])); ?></p><?php } ?>
        <p><strong><?php echo _l('unified_phone_note'); ?>:</strong><br><?php echo nl2br(html_escape($log['note'] ?? '')); ?></p>

        <?php if (!empty($log['recording_file']) && unified_phone_can('recordings')) { ?>
        <p><strong><?php echo _l('unified_phone_call_recording'); ?>:</strong><br>
            <?php $recordingUrl = admin_url('unified_phone/recording/' . (int) $log['id']); ?>
            <audio controls class="unified-recording-player"><source src="<?php echo $recordingUrl; ?>" type="<?php echo html_escape($log['recording_mime'] ?? 'audio/mpeg'); ?>"></audio><br>
            <a href="<?php echo $recordingUrl; ?>" target="_blank" class="btn btn-default btn-sm mtop10"><i class="fa fa-download"></i> <?php echo html_escape($log['recording_original_name'] ?: basename($log['recording_file'])); ?></a>
        </p>
        <?php } ?>
        <hr />
        <p class="text-muted">
            <?php echo _l('created_at'); ?>: <?php echo html_escape(unified_phone_safe_date($log['created_at'] ?? '')); ?>
            <?php if (!empty($log['updated_at'])) { ?> | <?php echo _l('updated_at'); ?>: <?php echo html_escape(unified_phone_safe_date($log['updated_at'])); ?><?php } ?>
        </p>
    </div></div>
</div></div></div></div>
<?php init_tail(); ?>
