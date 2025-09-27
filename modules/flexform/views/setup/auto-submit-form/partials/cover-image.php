<div class="form-group tw-mb-4">
    <div class="form-group">
        <label for="images" class="control-label"><?php echo _flexform_lang('cover-image') ?></label>
        <div class="fileinput fileinput-new" data-provides="fileinput">
            <div class="ff-image-preview thumbnail <?php echo (!$block['images']) ? 'hidden' : '' ?>">
                <img src="<?php echo flexform_get_image_url($block['images']) ?>" alt="<?php echo _flexform_lang('statement-image') ?>"><br/>
                <a href="#" class="ff-image-preview_remove_btn btn btn-danger"><i class="fa fa-trash"></i></a>
                <br/><br/>
            </div>
            <div class="ff-image-upload-wrapper <?php echo ($block['images']) ? 'hidden' : '' ?>">
                <span class="btn btn-default btn-file">
                    <input onchange="flexform_handle_image_upload(this)" class="form-control" type="file" name="images" id="images" accept="image/*" />
                </span>
            </div>
        </div>
    </div>
</div>