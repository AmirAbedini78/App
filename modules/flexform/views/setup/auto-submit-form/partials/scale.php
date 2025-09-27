<div class="form-group tw-mb-4">
    <?php echo render_input('left_label', _flexform_lang('left_label'), $block['left_label'], 'text', ['autocomplete' => 'off', 'maxlength' => '100'], [], '', 'flexform-question-left-right-label-text'); ?>
</div>
<div class="form-group tw-mb-4">
    <?php echo render_input('right_label', _flexform_lang('right_label'), $block['right_label'], 'text', ['autocomplete' => 'off', 'maxlength' => '100'], [], '', 'flexform-question-left-right-label-text'); ?>
</div>
<div class="form-group tw-mb-4">
    <div class="form-group">
        <label for="ff-rating" class="control-label"><?php echo _flexform_lang('max-rating') ?></label><br/>
        <select name="rating" id="ff-rating" class="form-control ff-rating">
            <?php for ($i = 3; $i <= 10; $i++): ?>
                <option value="<?php echo $i; ?>" <?php echo ($block['rating'] == $i) ? 'selected' : ''; ?>><?php echo $i; ?></option>
            <?php endfor; ?>
        </select>
    </div>
</div>