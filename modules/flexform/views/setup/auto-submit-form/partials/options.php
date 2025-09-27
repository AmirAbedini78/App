<div class="form-group tw-mb-4 mt-4 flexform-new-options-wrapper-fields <?php echo ($block['is_country'] ==1) ? 'hidden' : '' ?>">
    <div class="form-group">
        <label for="options" class="control-label">
            <?php echo _flexform_lang('options') ?>
        </label>
        <span class="pull-right">
            <a href="#" class="" id="ff-add-option">
                <i class="fa fa-plus fa-fw"></i>
                <?php echo _flexform_lang('add_option') ?>
            </a>
        </span>
        <div class="ff-options-wrapper tw-mt-2">
            <?php if ($block['options']): ?>
                <?php foreach ($block['options'] as $option): ?>
                    <div class="option">
                        <input type="text" class="form-control option__input" name="options[]" value="<?php echo $option ?>" />
                        <a href="#" class="option__remove text-danger tw-p-1">
                            <i class="fa fa-trash"></i>
                        </a>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
            <div class="option">
                <input type="text" class="form-control option__input" name="options[]" value="" />
                <a href="#" class="option__remove text-danger tw-p-1">
                    <i class="fa fa-trash"></i>
                </a>
            </div>
            <?php endif; ?>
        </div>
    </div>
</div>