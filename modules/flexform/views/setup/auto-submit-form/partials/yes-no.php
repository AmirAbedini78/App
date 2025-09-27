<div class="form-group tw-mb-4">
    <div class="form-group">
        <label for="<?php echo $name ?>" class="control-label clearfix"><?php echo _flexform_lang($label) ?> </label>
        <div class="radio radio-primary radio-inline">
            <input type="radio" id="y_opt_1_<?php echo $label ?>" name="<?php echo $name; ?>" class="ff-is-required"
                   value="1" <?php echo ($block[$column] == 1) ? 'checked' : ''; ?>>
            <label for="y_opt_1_<?php echo $label ?>">
                <?php echo _flexform_lang('yes') ?>
            </label>
        </div>
        <div class="radio radio-primary radio-inline">
            <input type="radio" id="y_opt_2_<?php echo $label ?>" name="<?php echo $name; ?>" class="ff-is-required"
                   value="0" <?php echo ($block[$column] == 0) ? 'checked' : ''; ?>>
            <label for="y_opt_2_<?php echo $label ?>">
                <?php echo _flexform_lang('no') ?>
            </label>
        </div>
    </div>
</div>