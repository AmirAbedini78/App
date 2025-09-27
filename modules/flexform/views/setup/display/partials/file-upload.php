<?php $form = isset($form) ? $form :  flexform_get_block_form($block); ?>
<?php if(flexform_is_form_single_page($form) || $block['simple_uploader'] == 1) : ?>
    <input type="file"
           <?php if(isset($block['allow_multiple']) && $block['allow_multiple'] == 1): ?>
           name="file_<?php echo $block['id'] ?>[]"
              <?php else: ?>
           name="file_<?php echo $block['id'] ?>"
              <?php endif ?>
           id="file"
           class="form-control"
            <?php if(isset($block['allow_multiple']) && $block['allow_multiple'] == 1) { echo 'multiple'; } ?>
    />
<?php else: ?>
<div id="dropzoneDragArea" class="dz-default dz-message">
   <i class="fa-solid fa-upload"></i> <span><?php echo _flexform_lang('upload-file') ?></span>
</div>
<button type="button" id="flexform-remove-files"><?php echo _flexform_lang('remove-all-files') ?></button>
<div id="flexform-files-input"></div>
<div class="dropzone-previews"></div>
<?php endif ?>