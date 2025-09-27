<?php
$index = (isset($index)) ? $index : 0;
$if_operator = (isset($if_operator)) ? $if_operator : '';
$if_value = (isset($if_value)) ? $if_value : '';
?>
<div class="col-sm-4">
    <select name="logic_<?php echo $index ?>_if_operator[]" class="form-control">
        <?php foreach ($commands as $cmd => $cmdText): ?>
            <option value="<?php echo $cmd; ?>" <?php echo $if_operator == $cmd ? 'selected' : '' ?>><?php echo $cmdText ?></option>
        <?php endforeach; ?>
    </select>
</div>
<div class="col-sm-8">
    <?php if ($block['block_type'] == 'dropdown' || $block['block_type'] == 'multiple-choice' || $block['block_type'] == 'single-choice'): ?>
        <?php if ($block['block_type'] == 'dropdown' && $block['is_country'] == 1) : ?>
            <?php $countries = get_all_countries(); ?>
            <select name="logic_<?php echo $index ?>_if_value[]" id="<?php echo $block['id']; ?>" class="form-control">
                <option value=""><?php echo _flexform_lang('please-select-an-option'); ?></option>
                <?php foreach ($countries as $country): ?>
                    <option value="<?php echo $country['country_id']; ?>" <?php echo ($if_value == $country['country_id'] || get_option('customer_default_country') == $country['country_id']) ? 'selected' : ''; ?>><?php echo $country['short_name']; ?></option>
                <?php endforeach; ?>
            </select>
        <?php elseif($block['ticket_list_type'] != ''): ?>
            <?php echo $this->load->view('setup/display/partials/ticket-list-dropdown', ['block' => $block, 'default_value' => $if_value,'name'=>'logic_'.$index.'_if_value[]'], true); ?>
        <?php else: ?>
            <select name="logic_<?php echo $index ?>_if_value[]" class="form-control">
                <option value=""><?php echo _flexform_lang('please-select') ?></option>
                <?php foreach ($block['options'] as $option): ?>
                    <option value="<?php echo $option ?>" <?php echo $if_value == $option ? 'selected' : '' ?>><?php echo $option; ?></option>
                <?php endforeach; ?>
            </select>
        <?php endif; ?>
    <?php elseif ($block['block_type'] == 'date'): ?>
        <?php echo render_date_input('logic_' . $index . '_if_value[]', '', $if_value); ?>
    <?php elseif ($block['block_type'] == 'datetime'): ?>
        <?php echo render_datetime_input('logic_' . $index . '_if_value[]', '', $if_value); ?>
    <?php else: ?>

        <?php echo render_input('logic_' . $index . '_if_value[]', '', $if_value, 'text', [], [], '', 'flexform-block-logic__if-value'); ?>
    <?php endif; ?>
</div>