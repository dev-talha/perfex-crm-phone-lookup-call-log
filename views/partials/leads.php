<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<div class="panel_s unified-section-panel"><div class="panel-body"><h4><i class="fa fa-tty"></i> <?php echo _l('leads'); ?></h4>
<?php if (empty($leads)) { echo '<p class="text-muted">' . _l('no_results_found') . '</p>'; } ?>
<?php foreach ((array) $leads as $l) { ?>
<div class="unified-card"><div class="unified-card-head"><strong><?php echo html_escape($l['name'] ?? '-'); ?></strong><?php if (!empty($l['status_name'])) { ?><span class="label label-info"><?php echo html_escape($l['status_name']); ?></span><?php } ?></div>
<div class="row"><div class="col-sm-4"><small class="text-muted"><?php echo _l('company'); ?></small><br><?php echo html_escape($l['company'] ?? '-'); ?></div><div class="col-sm-4"><small class="text-muted"><?php echo _l('unified_phone_phone'); ?></small><br><?php echo html_escape($l['phonenumber'] ?? '-'); ?></div><div class="col-sm-4"><small class="text-muted"><?php echo _l('email'); ?></small><br><?php echo html_escape($l['email'] ?? '-'); ?></div></div>
<div class="row mtop10"><div class="col-sm-4"><small class="text-muted"><?php echo _l('lead_source'); ?></small><br><?php echo html_escape($l['source_name'] ?? '-'); ?></div><div class="col-sm-4"><small class="text-muted"><?php echo _l('staff'); ?></small><br><?php echo html_escape($l['assigned_staff'] ?? '-'); ?></div><div class="col-sm-4"><small class="text-muted"><?php echo _l('lead_add_edit_datecontacted'); ?></small><br><?php echo !empty($l['lastcontact']) ? unified_phone_format_datetime($l['lastcontact']) : '-'; ?></div></div>
<a class="btn btn-xs btn-default mtop10" href="<?php echo admin_url('leads/index/' . (int) $l['id']); ?>"><i class="fa fa-eye"></i> <?php echo _l('view'); ?></a></div>
<?php } ?></div></div>
