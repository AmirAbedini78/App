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