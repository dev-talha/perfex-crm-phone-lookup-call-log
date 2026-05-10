<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<div class="panel_s unified-section-panel"><div class="panel-body"><h4><i class="fa fa-address-book"></i> <?php echo _l('contacts'); ?></h4>
<?php if (empty($contacts)) { echo '<p class="text-muted">' . _l('no_results_found') . '</p>'; } ?>
<?php foreach ((array) $contacts as $c) { ?>
<div class="unified-card"><div class="unified-card-head"><strong><?php echo html_escape(trim(($c['firstname'] ?? '') . ' ' . ($c['lastname'] ?? '')) ?: '-'); ?></strong><span class="label label-<?php echo !empty($c['active']) ? 'success' : 'default'; ?>"><?php echo !empty($c['active']) ? _l('active') : _l('inactive'); ?></span></div>
<div class="row"><div class="col-sm-6"><small class="text-muted"><?php echo _l('client'); ?></small><br><?php echo html_escape($c['client_company'] ?? '-'); ?></div><div class="col-sm-6"><small class="text-muted"><?php echo _l('unified_phone_phone'); ?></small><br><?php echo html_escape($c['phonenumber'] ?? '-'); ?></div></div>
<div class="row mtop10"><div class="col-sm-6"><small class="text-muted"><?php echo _l('email'); ?></small><br><?php echo html_escape($c['email'] ?? '-'); ?></div><div class="col-sm-6"><small class="text-muted"><?php echo _l('position'); ?></small><br><?php echo html_escape($c['title'] ?? '-'); ?></div></div>
<a class="btn btn-xs btn-default mtop10" href="<?php echo admin_url('clients/client/' . (int) $c['userid'] . '?contactid=' . (int) $c['id']); ?>"><i class="fa fa-eye"></i> <?php echo _l('view'); ?></a></div>
<?php } ?></div></div>
