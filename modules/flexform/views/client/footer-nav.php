<?php
$submit_btn_text_color = $form['submit_btn_text_color'] ? $form['submit_btn_text_color'] : '#fff';
$submit_btn_bg_color = $form['submit_btn_bg_color'] ? $form['submit_btn_bg_color'] : '#0a0a0a';
$current_block_id = $showing_block['id']; //block that is currently displayed
$show_prev = false;
$show_next = false;
//if we have at least one item in the logs, we can show the prev button
if(count($nav_logs) == 1){
    $show_prev = true;
}elseif(count($nav_logs) > 1){
    //the position of the current block in the logs
    $current_block_position = array_search($current_block_id, $nav_logs);
    //if the current block is not the first block, we can show the prev button
    if($current_block_position > 0){
        $show_prev = true;
    }
    //if the current block is not the last block, we can show the next button
    if($current_block_position < count($nav_logs) - 1){
        $show_next = true;
    }

}
//t
?>
<?php if($nav_logs && $showing_block['block_type'] != 'thank-you'): ?>
<?php if($show_prev): ?>
    <button  style="background: <?php echo $submit_btn_bg_color; ?>;color: <?php echo $submit_btn_text_color; ?>;"
             data-url="<?php echo site_url('flexform/nav/' . $form['slug']); ?>"
             data-type="prev"
             data-id="<?php echo $current_block_id; ?>"
            class="btn btn-sm" id="flexform-prev-button"><i class="fa-solid fa-chevron-up"></i> <span><?php echo _flexform_lang('prev') ?></span></button>
<?php endif; ?>
<?php if($show_next): ?>
        &nbsp;
    <button  style="background: <?php echo $submit_btn_bg_color; ?>;color: <?php echo $submit_btn_text_color; ?>;"
                data-url="<?php echo site_url('flexform/nav/' . $form['slug']); ?>"
                data-type="next"
                data-id="<?php echo $current_block_id; ?>"
            class="btn btn-sm"
            id="flexform-next-button"><i class="fa-solid fa-chevron-down"></i> <span><?php echo _flexform_lang('next') ?></span></button>
<?php endif; ?>
<?php endif; ?>