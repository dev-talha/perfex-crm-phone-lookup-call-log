<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php
$sections = [
    'proposals' => ['icon' => 'fa-file-text-o', 'label' => 'proposals'],
    'estimates' => ['icon' => 'fa-calculator', 'label' => 'estimates'],
    'invoices' => ['icon' => 'fa-file-text', 'label' => 'invoices'],
    'payments' => ['icon' => 'fa-credit-card', 'label' => 'payments'],
    'projects' => ['icon' => 'fa-tasks', 'label' => 'projects'],
    'contracts' => ['icon' => 'fa-handshake-o', 'label' => 'contracts'],
    'tickets' => ['icon' => 'fa-life-ring', 'label' => 'tickets'],
];
$hasAny = false;
foreach ($sections as $key => $meta) { if (unified_phone_section_enabled($key)) { $hasAny = true; break; } }
?>
<?php if ($hasAny) { ?>
<div class="panel_s unified-section-panel"><div class="panel-body">
    <h4><i class="fa fa-link"></i> <?php echo _l('unified_phone_related_records'); ?></h4>
    <div class="row unified-related-grid">
    <?php foreach ($sections as $key => $meta) { if (!unified_phone_section_enabled($key)) { continue; } $items = isset($related[$key]) ? (array) $related[$key] : []; ?>
        <div class="col-md-6 col-lg-4">
            <div class="unified-related-box">
                <div class="unified-related-title"><i class="fa <?php echo $meta['icon']; ?>"></i> <?php echo _l($meta['label']); ?> <span class="badge"><?php echo count($items); ?></span></div>
                <?php if (empty($items)) { ?><p class="text-muted small mbot0"><?php echo _l('no_results_found'); ?></p><?php } ?>
                <ul class="unified-related-list">
                <?php foreach ($items as $row) { ?>
                    <li>
                    <?php if ($key === 'proposals') { ?>
                        <a href="<?php echo admin_url('proposals/list_proposals/' . (int) $row['id']); ?>"><?php echo function_exists('format_proposal_number') ? format_proposal_number($row['id']) : '#' . (int) $row['id']; ?></a><br><small><?php echo html_escape($row['subject'] ?? '-'); ?></small>
                    <?php } elseif ($key === 'estimates') { ?>
                        <a href="<?php echo admin_url('estimates/list_estimates/' . (int) $row['id']); ?>"><?php echo function_exists('format_estimate_number') ? format_estimate_number($row['id']) : '#' . (int) $row['id']; ?></a><br><small><?php echo unified_phone_money($row['total'] ?? 0); ?></small>
                    <?php } elseif ($key === 'invoices') { ?>
                        <a href="<?php echo admin_url('invoices/list_invoices/' . (int) $row['id']); ?>"><?php echo function_exists('format_invoice_number') ? format_invoice_number($row['id']) : '#' . (int) $row['id']; ?></a><br><small><?php echo unified_phone_money($row['total'] ?? 0); ?></small>
                    <?php } elseif ($key === 'payments') { ?>
                        <a href="<?php echo admin_url('payments/payment/' . (int) $row['id']); ?>"><?php echo _l('payment'); ?> #<?php echo (int) $row['id']; ?></a><br><small><?php echo unified_phone_money($row['amount'] ?? 0); ?> | <?php echo !empty($row['date']) ? unified_phone_format_date($row['date']) : '-'; ?></small>
                    <?php } elseif ($key === 'projects') { ?>
                        <a href="<?php echo admin_url('projects/view/' . (int) $row['id']); ?>"><?php echo html_escape($row['name'] ?? ('#' . (int) $row['id'])); ?></a><br><small><?php echo html_escape($row['status'] ?? ''); ?></small>
                    <?php } elseif ($key === 'contracts') { ?>
                        <a href="<?php echo admin_url('contracts/contract/' . (int) $row['id']); ?>"><?php echo html_escape($row['subject'] ?? ('#' . (int) $row['id'])); ?></a><br><small><?php echo !empty($row['dateend']) ? unified_phone_format_date($row['dateend']) : '-'; ?></small>
                    <?php } elseif ($key === 'tickets') { ?>
                        <a href="<?php echo admin_url('tickets/ticket/' . (int) $row['ticketid']); ?>"><?php echo html_escape($row['subject'] ?? '-'); ?></a><br><small><?php echo !empty($row['date']) ? unified_phone_format_datetime($row['date']) : '-'; ?></small>
                    <?php } ?>
                    </li>
                <?php } ?>
                </ul>
            </div>
        </div>
    <?php } ?>
    </div>
</div></div>
<?php } ?>
