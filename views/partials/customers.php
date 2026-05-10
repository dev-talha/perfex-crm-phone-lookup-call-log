<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<div class="panel_s unified-section-panel"><div class="panel-body"><h4><i class="fa fa-building"></i> <?php echo _l('clients'); ?></h4>
<?php if (empty($customers)) { echo '<p class="text-muted">' . _l('no_results_found') . '</p>'; } ?>
<?php foreach ((array) $customers as $c) { ?>
<div class="unified-card"><div class="unified-card-head"><strong><?php echo html_escape($c['company'] ?? '-'); ?></strong><span class="label label-<?php echo !empty($c['active']) ? 'success' : 'default'; ?>"><?php echo !empty($c['active']) ? _l('active') : _l('inactive'); ?></span></div>
<div class="row"><div class="col-sm-6"><small class="text-muted"><?php echo _l('unified_phone_phone'); ?></small><br><?php echo html_escape($c['phonenumber'] ?? '-'); ?></div><div class="col-sm-6"><small class="text-muted"><?php echo _l('contact_primary'); ?></small><br><?php echo html_escape($c['primary_contact'] ?? '-'); ?></div></div>
<div class="row mtop10"><div class="col-sm-6"><small class="text-muted"><?php echo _l('email'); ?></small><br><?php echo html_escape($c['primary_email'] ?? '-'); ?></div><div class="col-sm-6"><small class="text-muted"><?php echo _l('date_created'); ?></small><br><?php echo !empty($c['datecreated']) ? unified_phone_format_datetime($c['datecreated']) : '-'; ?></div></div>
<a class="btn btn-xs btn-default mtop10" href="<?php echo admin_url('clients/client/' . (int) $c['userid']); ?>"><i class="fa fa-eye"></i> <?php echo _l('view'); ?></a></div>
<?php } ?></div></div>
