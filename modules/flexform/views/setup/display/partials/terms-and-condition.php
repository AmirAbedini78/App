<!-- display terms and conditions checkbox as rqeuired -->
<div class="checkbox checkbox-primary">
    <input type="checkbox" name="terms_and_conditions"
           id="terms_and_conditions"
           required>
    <label for="terms_and_conditions">
        <?php echo _flexform_lang('by-submitting-this-form-you-agree-to-our') ?> <a href="<?php echo site_url('terms-and-conditions') ?>" target="_blank">
            <?php echo _flexform_lang('terms-and-conditions') ?>
        </a>
    </label>
</div>