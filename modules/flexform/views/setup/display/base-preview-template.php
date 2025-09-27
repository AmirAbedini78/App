<?php
$fsession = isset($form_session) ? $form_session : null;
$default_value = ($preview) ? '' : flexform_get_block_answer($block,$fsession); ?>
<div class="flexform-text-display" data-current="<?php echo flexformPerfectSerialize($block['id']) ?>">
    <?php echo $this->load->view('partials/title-label', ['block' => $block], true); ?>
    <?php echo $this->load->view('partials/description-label', ['block' => $block], true); ?>
    <div class="form-group tw-mb-4">
    <?php if(isset($textarea) && $textarea ==true): ?>
        <textarea class="form-control flexform-textarea flexform-input-preview" name="answer_<?php echo $block['id'] ?>"><?php echo $default_value; ?></textarea>
    <?php endif; ?>
    <?php if(isset($input) && $input== true): ?>
        <input type="text" value="<?php echo $default_value; ?>" class="form-control flexform-text-input flexform-input-preview" name="answer_<?php echo $block['id'] ?>" placeholder="<?php echo $block['placeholder'] ?>" />
    <?php endif; ?>
    </div>
    <?php echo $this->load->view('partials/submit-button', ['block' => $block,'is_submit'=>$is_submit], true); ?>
</div>