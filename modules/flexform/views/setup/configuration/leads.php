<?php
$form = $props['form'];
$members = $props['members'];
$sources = $props['sources'];
$statuses = $props['statuses'];
?>
<div class="tw-mb-4">
    <div class="ff-section">
        <h5 class="ff-section_title"><?php echo _flexform_lang('leads-configuration'); ?></h5>
    </div>
    <div class="form-group">
        <p><?php echo _flexform_lang('allow_duplicate_leads_desc') ?></p>
        <div class="checkbox checkbox-primary">
            <input type="checkbox" name="allow_duplicate_leads" id="allow_duplicate_leads"
                <?php echo isset($form) && $form['allow_duplicate_leads'] == 1 ? 'checked' : ''; ?>>
            <label for="allow_duplicate_leads">
                <?php echo _flexform_lang('allow_duplicate_leads'); ?>
            </label>
        </div>
    </div>
    <div class="form-group">
        <?php $value = (isset($form) ? $form['lead_name_prefix'] : ''); ?>
        <?php echo render_input('lead_name_prefix', 'lead_name_prefix', $value, 'text', [], [], 'mbot5'); ?>
    </div>
    <?php
    echo render_leads_source_select($sources, (isset($form) ? $form['lead_source'] : get_option('leads_default_source')), 'lead_import_source', 'lead_source');

    echo render_leads_status_select($statuses, (isset($form) ? $form['lead_status'] : get_option('leads_default_status')), 'lead_import_status', 'lead_status', [], true);

     ?>

</div>
