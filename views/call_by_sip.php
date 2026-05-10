<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head(); ?>
<div id="wrapper">
  <div class="content unified-call-by-sip-page">
    <div class="row">
      <div class="col-lg-6 col-lg-offset-3 col-md-8 col-md-offset-2">
        <div class="panel_s unified-sip-card">
          <div class="panel-body">
            <div class="unified-sip-header clearfix">
              <div class="pull-left">
                <h3 class="no-margin"><?php echo _l('unified_phone_call_by_sip'); ?></h3>
                <p class="text-muted no-margin mtop5"><?php echo _l('unified_phone_call_by_sip_subtitle'); ?></p>
              </div>
              <button type="button" class="btn btn-default btn-icon pull-right" data-toggle="collapse" data-target="#unified-sip-help" title="<?php echo _l('unified_phone_sip_instructions'); ?>">
                <i class="fa fa-info"></i>
              </button>
            </div>
            <div id="unified-sip-help" class="collapse mtop15">
              <div class="alert alert-info no-margin">
                <?php echo _l('unified_phone_microsip_note'); ?>
              </div>
            </div>
            <div class="unified-sip-display mtop20">
              <input type="text" id="unified-sip-phone" class="form-control input-lg text-center" autocomplete="off" autofocus>
            </div>
            <div class="unified-dialpad mtop15" aria-label="<?php echo _l('unified_phone_dial_pad'); ?>">
              <?php foreach (['1','2','3','4','5','6','7','8','9','+','0','⌫'] as $key) { ?>
                <button type="button" class="btn btn-default unified-dial-key" data-key="<?php echo html_escape($key); ?>"><?php echo html_escape($key); ?></button>
              <?php } ?>
            </div>
            <button type="button" id="unified-sip-call-btn" class="btn btn-primary btn-lg btn-block mtop15"><i class="fa fa-phone"></i> <?php echo _l('unified_phone_call_now'); ?></button>
            <p class="text-muted text-center mtop10 mbot0"><?php echo _l('unified_phone_call_by_sip_help_short'); ?></p>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>
<?php $this->load->view('unified_phone/call_log_modal', ['phone_raw' => '', 'outcomes' => $outcomes, 'call_type' => 'outgoing']); ?>
<?php init_tail(); ?>
