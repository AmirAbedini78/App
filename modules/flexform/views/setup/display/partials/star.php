<?php
$fsession = isset($form_session) ? $form_session : null;
$limit = $block['rating'] ? $block['rating'] : 3;
$default_value = ($preview) ? 0 : flexform_get_block_answer($block,$fsession);
?>
<div class="flexform-rating">
    <div class="flexform-rating__stars">
        <?php for ($i = 1; $i <= $limit; $i++) : ?>
            <input type="radio" id="flexform-rating-star-<?php echo $block['id'] ?>-<?php echo $i; ?>" name="<?php echo 'answer_'.$block['id'] ?>"  value="<?php echo $i; ?>" class="<?php echo ($default_value >= $i) ? 'active-star' : '' ?>" <?php echo ($default_value == $i) ? 'checked' : '' ?> />
            <label for="flexform-rating-star-<?php echo $block['id'] ?>-<?php echo $i; ?>" data-rating="<?php echo $i; ?>" class="flexform_rating_label flexform_rating_label_<?php echo $i; ?> <?php echo ($default_value >= $i) ? 'active-star' : '' ?>"><i class="fa-regular fa-star"></i></label>
        <?php endfor; ?>
    </div>
</div>