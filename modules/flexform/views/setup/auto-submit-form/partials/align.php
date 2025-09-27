<div class="form-group tw-mb-4">
    <div class="form-group">
        <label for="button_text" class="control-label"><?php echo _flexform_lang('align') ?></label><br/>
        <!-- align button icon -->
        <div class="ff-left-align ff-align-parent">
            <input type="radio" id="left_align" name="text_align" class="ff-align"
                   value="left" <?php echo ($block['text_align'] == 'left') ? 'checked' : ''; ?>>
            <button class="btn btn-default flexform-align-btn <?php echo ($block['text_align'] == 'left') ? 'checked' : ''; ?>"
                    data-align="left"
                    type="button">
                <i class="fa fa-align-left"></i>
            </button>
        </div>
        <div class="ff-center-align ff-align-parent">
            <input type="radio" id="center_align" name="text_align" class="ff-align"
                   value="center" <?php echo ($block['text_align'] == 'center') ? 'checked' : ''; ?>>
            <button class="btn btn-default flexform-align-btn <?php echo ($block['text_align'] == 'center') ? 'bg-primary' : ''; ?>"
                    data-align="center"
                    type="button">
                <i class="fa fa-align-center"></i>
            </button>
        </div>
    </div>
</div>