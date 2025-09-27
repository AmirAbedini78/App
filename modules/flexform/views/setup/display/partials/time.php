<?php
$fsession = isset($form_session) ? $form_session : null;
$default_value = ($preview) ? '' : flexform_get_block_answer($block,$fsession);
?>
<div class="form-group tw-mb-4">
    <input type="time" class="form-control" id="<?php echo $block['id']; ?>" name="answer_<?php echo $block['id']; ?>" value="<?php echo $default_value; ?>">
</div>
