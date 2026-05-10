<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head(); ?>
<div id="wrapper"><div class="content unified-phone-result">
    <div class="row"><div class="col-md-12">
        <div class="panel_s unified-hero"><div class="panel-body">
            <div class="pull-right unified-hero-actions">
                <a href="<?php echo admin_url('unified_phone'); ?>" class="btn btn-default"><i class="fa fa-search"></i> <?php echo _l('unified_phone_new_search'); ?></a>
                <?php if (unified_phone_can('create')) { ?><button class="btn btn-primary" data-toggle="modal" data-target="#unifiedCallLogModal"><i class="fa fa-plus"></i> <?php echo _l('unified_phone_add_call_log'); ?></button><?php } ?>
            </div>
            <h3><i class="fa fa-phone-square text-primary"></i> <?php echo _l('unified_phone_lookup'); ?>: <?php echo html_escape($phone_display); ?></h3>
            <p class="text-muted mbot0"><?php echo _l('unified_phone_raw'); ?>: <?php echo html_escape($phone_raw); ?> &nbsp; | &nbsp; <?php echo _l('unified_phone_normalized_phone'); ?>: <?php echo html_escape($phone_normalized ?: '-'); ?></p>
        </div></div>
    </div></div>

    <?php $this->load->view('unified_phone/call_log_modal', ['phone_raw' => $phone_raw, 'phone_normalized' => $phone_normalized, 'outcomes' => $outcomes, 'call_type' => $call_type ?? '']); ?>

    <div class="row unified-summary-row">
        <?php if (unified_phone_section_enabled('customers')) { ?><div class="col-md-3 col-sm-6"><div class="unified-summary-card"><span><?php echo count($customers); ?></span><?php echo _l('clients'); ?></div></div><?php } ?>
        <?php if (unified_phone_section_enabled('leads')) { ?><div class="col-md-3 col-sm-6"><div class="unified-summary-card"><span><?php echo count($leads); ?></span><?php echo _l('leads'); ?></div></div><?php } ?>
        <?php if (unified_phone_section_enabled('call_logs')) { ?><div class="col-md-3 col-sm-6"><a href="#unified-previous-call-logs-section" class="unified-summary-card unified-summary-link"><span><?php echo (int) $call_logs_total; ?></span><?php echo _l('unified_phone_previous_call_logs'); ?></a></div><?php } ?>
        <?php if (!empty($chatwoot_enabled)) { ?>
            <?php $chatwootCount = count((array) ($chatwoot['conversations'] ?? [])); ?>
            <div class="col-md-3 col-sm-6"><a href="#unified-chatwoot-section" class="unified-summary-card unified-summary-link"><span><?php echo (int) $chatwootCount; ?></span>Chatwoot</a></div>
        <?php } ?>
    </div>

    <div class="row">
        <?php if (unified_phone_section_enabled('customers')) { ?><div class="col-md-6"><?php $this->load->view('unified_phone/partials/customers', ['customers' => $customers]); ?></div><?php } ?>
        <?php if (unified_phone_section_enabled('contacts')) { ?><div class="col-md-6"><?php $this->load->view('unified_phone/partials/contacts', ['contacts' => $contacts]); ?></div><?php } ?>
    </div>
    <?php if (unified_phone_section_enabled('leads')) { ?><div class="row"><div class="col-md-12"><?php $this->load->view('unified_phone/partials/leads', ['leads' => $leads]); ?></div></div><?php } ?>
    <div class="row"><div class="col-md-12"><?php $this->load->view('unified_phone/partials/financials', ['related' => $related]); ?></div></div>
    <?php if (!empty($chatwoot_enabled)) { ?>
        <div class="row" id="unified-chatwoot-section"><div class="col-md-12"><?php $this->load->view('unified_phone/partials/chatwoot', ['chatwoot' => $chatwoot]); ?></div></div>
    <?php } ?>
    <?php if (unified_phone_section_enabled('call_logs')) { ?>
    <div class="row" id="unified-previous-call-logs-section"><div class="col-md-12"><div class="panel_s"><div class="panel-body">
        <div class="clearfix">
            <h4 class="pull-left"><i class="fa fa-history"></i> <?php echo _l('unified_phone_previous_call_logs'); ?></h4>
            <span class="label label-default pull-right mtop5"><?php echo (int) $call_logs_total; ?> / <?php echo (int) $call_logs_max_history; ?></span>
        </div>
        <p class="text-muted"><?php echo _l('unified_phone_previous_call_logs_help'); ?></p>
        <?php $this->load->view('unified_phone/partials/call_log_table', ['call_logs' => $call_logs]); ?>
        <?php echo $call_logs_pagination ?? ''; ?>
    </div></div></div></div>
    <?php } ?>
</div></div>
<?php init_tail(); ?>
