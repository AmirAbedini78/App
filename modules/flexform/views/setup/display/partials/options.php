<?php
$is_checkbox = false;
$name = 'answer_'.$block['id'];
//$default_value = ($preview) ? [] : flexform_get_block_answer($block);
$default_value = [];
if(!$preview) {
    $fsession = isset($form_session) ? $form_session : null;
    $answer = flexform_get_block_answer($block,$fsession);
    $default_value = ($answer) ? $answer : [];
}
if($block['block_type'] == 'multiple-choice') {
    $is_checkbox = true;
    $name = 'answer_'.$block['id'].'[]';
}
$countries = get_all_countries();
if($default_value && !is_array($default_value)) {
    ///let convert the default value to array
    $default_value = [$default_value];
}

?>
<?php if(isset($dropdown) && $dropdown == true): ?>
    <div class="select-placeholder form-group ff-options-boxes">
        <?php if($block['is_country'] == 1) :?>
            <select data-live-search="true" name="<?php echo $name; ?>" id="bid<?php echo $block['id']; ?>" class="selectpicker form-control">
                <option value=""><?php echo _flexform_lang('please-select-an-option'); ?></option>
                <?php foreach($countries as $country): ?>
                    <option value="<?php echo $country['country_id']; ?>" <?php echo (in_array($country['country_id'],$default_value) || get_option('customer_default_country') == $country['country_id']) ? 'selected' : ''; ?>><?php echo $country['short_name']; ?></option>
                <?php endforeach; ?>
            </select>
        <?php else: ?>
        <?php if($block['ticket_list_type'] != ''): ?>
            <?php echo $this->load->view('partials/ticket-list-dropdown', ['block' => $block, 'default_value' => $default_value,'name'=>$name], true); ?>
        <?php else: ?>
            <?php if($block['options']): ?>
                <select  data-live-search="true" name="<?php echo $name; ?>" id="bid<?php echo $block['id']; ?>" class="ff-options-dropdown form-control selectpicker">
                    <option value=""><?php echo _flexform_lang('please-select-an-option'); ?></option>
                    <?php foreach($block['options'] as $option): ?>
                        <option value="<?php echo $option; ?>" <?php echo (in_array($option,$default_value)) ? 'selected' : '' ?>><?php echo $option; ?></option>
                    <?php endforeach; ?>
                </select>
            <?php endif; ?>
            <?php endif; ?>
        <?php endif; ?>
    </div>
<?php else: ?>
<div class="ff-options-boxes <?php echo ($block['horizontal']) ? 'horizontal' : 'vertical' ?>">
    <?php if($block['options']): ?>
        <?php foreach($block['options'] as $option): ?>
            <div class="ff-option-box">
                <input type="<?php echo ($is_checkbox) ? 'checkbox' : 'radio' ?>" id="<?php echo $option ?>" name="<?php echo $name; ?>" value="<?php echo $option ?>" <?php echo (in_array($option,$default_value)) ? 'checked' : '' ?> />
                <label for="<?php echo $option; ?>"><?php echo $option; ?></label>
                <?php echo (!$is_checkbox) ? '<span class="ff-option-box_checked"><i class="fa-solid fa-circle-dot"></i></span>' : '<span class="ff-option-box_checked"><i class="fa-regular fa-circle-check"></i></span>'; ?>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>
</div>
<?php endif; ?>