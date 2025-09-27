<?php
$fsession = isset($form_session) ? $form_session : null;
$limit = $block['rating'] ? $block['rating'] : 3;
$default_value = ($preview) ? 0 : flexform_get_block_answer($block,$fsession);
?>
<div class="flexform-opinion-scale">
    <div class="flexform-opionion-scale_inner">
        <?php for ($i = 1; $i <= $limit; $i++) : ?>
            <input type="radio" id="flexform-opinion-scale-<?php echo $block['id'] ?>-<?php echo $i; ?>" name="<?php echo 'answer_'.$block['id'] ?>"  value="<?php echo $i; ?>" class="<?php echo ($default_value >= $i) ? 'active-star' : '' ?>" <?php echo ($default_value == $i) ? 'checked' : '' ?> />
            <label for="flexform-opinion-scale-<?php echo $block['id'] ?>-<?php echo $i; ?>" data-rating="<?php echo $i; ?>" class="btn btn-secondary flexform_opinion_scale_label flexform_opinion_scale_label_<?php echo $i; ?> <?php echo ($default_value >= $i) ? 'active-scale' : '' ?>"><?php echo $i ?></label>
        <?php endfor; ?>
    </div>
    <div class="label_wrapper tw-mt-4">
        <span class="pull-left"><?php echo $block['left_label'] ?></span>
        <span class="pull-right"><?php echo $block['right_label'] ?></span>
    </div>
</div>