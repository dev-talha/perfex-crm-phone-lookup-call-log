<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php
$canViewDetails = function_exists('unified_phone_can') ? unified_phone_can('view_detail') : false;
$canEdit = function_exists('unified_phone_can') ? unified_phone_can('edit') : false;
$canDelete = function_exists('unified_phone_can') ? unified_phone_can('delete') : false;
?>
<div class="table-responsive">
<table class="table table-striped table-call-logs">
    <thead>
        <tr>
            <th><?php echo _l('unified_phone_call_datetime'); ?></th>
            <th><?php echo _l('unified_phone_phone'); ?></th>
            <th><?php echo _l('unified_phone_call_type'); ?></th>
            <th><?php echo _l('unified_phone_duration'); ?></th>
            <th><?php echo _l('unified_phone_outcome'); ?></th>
            <th><?php echo _l('unified_phone_follow_up_datetime'); ?></th>
            <th><?php echo _l('unified_phone_related_to'); ?></th>
            <th><?php echo _l('staff'); ?></th>
            <th><?php echo _l('options'); ?></th>
        </tr>
    </thead>
    <tbody>
    <?php foreach ((array) $call_logs as $log) { ?>
        <?php
        $related = function_exists('unified_phone_call_log_related') ? unified_phone_call_log_related($log) : ['label' => '-', 'url' => ''];
        $viewUrl = admin_url('unified_phone/view_call_log/' . (int) $log['id']);
        $phoneValue = $log['phone_normalized'] ?: $log['phone_raw'];
        $phoneUrl = admin_url('unified_phone/search?phone=' . urlencode($phoneValue));
        $staffUrl = !empty($log['created_by']) ? admin_url('staff/profile/' . (int) $log['created_by']) : '';
        ?>
        <tr>
            <td>
                <?php if ($canViewDetails) { ?><a href="<?php echo $viewUrl; ?>"><?php echo html_escape(unified_phone_safe_date($log['call_datetime'] ?? '')); ?></a><?php } else { echo html_escape(unified_phone_safe_date($log['call_datetime'] ?? '')); } ?>
            </td>
            <td>
                <a href="<?php echo $phoneUrl; ?>"><strong><?php echo html_escape($phoneValue); ?></strong></a><br>
                <small class="text-muted"><?php echo html_escape($log['phone_raw']); ?></small>
            </td>
            <td><span class="label label-<?php echo ($log['call_type'] ?? '') === 'outgoing' ? 'info' : 'success'; ?>"><?php echo html_escape(ucfirst($log['call_type'] ?? '')); ?></span></td>
            <td><?php echo html_escape($log['duration_text'] ?? unified_phone_format_duration($log['duration_seconds'] ?? 0)); ?></td>
            <td><?php if (!empty($log['outcome'])) { ?><span class="unified-outcome-badge" style="background:<?php echo html_escape($log['outcome_color'] ?? '#64748b'); ?>"><?php echo html_escape($log['outcome']); ?></span><?php } else { echo '-'; } ?></td>
            <td><?php echo !empty($log['follow_up_datetime']) ? html_escape(unified_phone_safe_date($log['follow_up_datetime'])) : '-'; ?></td>
            <td>
                <?php if (!empty($related['url']) && $related['label'] !== '-') { ?>
                    <a href="<?php echo $related['url']; ?>"><?php echo html_escape($related['label']); ?></a>
                <?php } else { ?>
                    <?php echo html_escape($related['label']); ?>
                <?php } ?>
            </td>
            <td>
                <?php if ($staffUrl) { ?><a href="<?php echo $staffUrl; ?>"><?php echo html_escape($log['staff_name'] ?? $log['created_by'] ?? '-'); ?></a><?php } else { echo html_escape($log['staff_name'] ?? $log['created_by'] ?? '-'); } ?>
            </td>
            <td class="unified-options-cell">
                <div class="unified-call-actions">
                    <?php if ($canViewDetails) { ?><a href="<?php echo $viewUrl; ?>" class="btn btn-default btn-icon btn-xs" title="<?php echo _l('view'); ?>"><i class="fa fa-eye"></i></a><?php } ?>
                    <?php if ($canEdit) { ?><a href="<?php echo admin_url('unified_phone/edit_call_log/' . (int) $log['id']); ?>" class="btn btn-default btn-icon btn-xs" title="<?php echo _l('edit'); ?>"><i class="fa fa-pencil"></i></a><?php } ?>
                    <?php if ($canDelete) { ?><a href="<?php echo admin_url('unified_phone/delete_call_log/' . (int) $log['id']); ?>" class="btn btn-danger btn-icon btn-xs _delete" title="<?php echo _l('delete'); ?>"><i class="fa fa-remove"></i></a><?php } ?>
                </div>
            </td>
        </tr>
    <?php } ?>
    <?php if (empty($call_logs)) { ?><tr><td colspan="9" class="text-muted text-center"><?php echo _l('no_results_found'); ?></td></tr><?php } ?>
    </tbody>
</table>
</div>
