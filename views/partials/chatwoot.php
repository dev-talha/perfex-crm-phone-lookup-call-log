<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php if (!empty($chatwoot['enabled'])) { ?>
<div class="panel_s"><div class="panel-body"><h4>Chatwoot</h4>
<?php if (!empty($chatwoot['error'])) { echo '<div class="alert alert-warning">' . html_escape($chatwoot['error']) . '</div>'; } ?>
<?php if (empty($chatwoot['contacts']) && empty($chatwoot['conversations'])) { echo '<p class="text-muted">' . _l('no_results_found') . '</p>'; } ?>
<?php if (!empty($chatwoot['contacts'])) { ?>
<h5><?php echo _l('contacts'); ?></h5>
<?php foreach ($chatwoot['contacts'] as $c) { ?>
    <div class="unified-card">
        <strong><?php echo html_escape($c['name'] ?? ($c['identifier'] ?? 'Contact')); ?></strong><br>
        Phone: <?php echo html_escape($c['phone_number'] ?? ''); ?><br>
        Email: <?php echo html_escape($c['email'] ?? ''); ?><br>
        <?php if (!empty($c['last_activity_at'])) { ?>Last activity: <?php echo unified_phone_chatwoot_datetime($c['last_activity_at']); ?><br><?php } ?>
        <?php if (!empty($c['_url'])) { ?><a class="btn btn-xs btn-default" target="_blank" href="<?php echo html_escape($c['_url']); ?>">Open in Chatwoot</a><?php } ?>
    </div>
<?php } ?>
<?php } ?>
<?php if (!empty($chatwoot['conversations'])) { ?>
<h5><?php echo _l('conversations'); ?> <small class="text-muted"><?php echo _l('unified_phone_latest_10'); ?></small></h5>
<?php foreach ($chatwoot['conversations'] as $cv) { ?>
    <div class="unified-card">
        <strong>#<?php echo html_escape($cv['id'] ?? ''); ?></strong><br>
        <?php if (!empty($cv['inbox']['name'])) { ?>Inbox: <?php echo html_escape($cv['inbox']['name']); ?><br><?php } ?>
        Status: <?php echo html_escape($cv['status'] ?? ''); ?><br>
        Last activity: <?php echo unified_phone_chatwoot_datetime($cv['last_activity_at'] ?? ($cv['updated_at'] ?? ($cv['created_at'] ?? ''))); ?><br>
        <?php if (!empty($cv['_url'])) { ?><a class="btn btn-xs btn-default" target="_blank" href="<?php echo html_escape($cv['_url']); ?>">Open Conversation</a><?php } ?>
    </div>
<?php } ?>
<?php } ?>
</div></div>
<?php } ?>
