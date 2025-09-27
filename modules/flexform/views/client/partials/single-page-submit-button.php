<?php
$submit_btn_text_color = $form['submit_btn_text_color'] ? $form['submit_btn_text_color'] : '#fff';
$submit_btn_bg_color = $form['submit_btn_bg_color'] ? $form['submit_btn_bg_color'] : '#0a0a0a';
$label = $form['submit_btn_name'] ? $form['submit_btn_name'] : _flexform_lang('submit');
$icon = '<i class="fa-regular fa-circle-check"></i>';
?>
<?php if ($form['require_terms_and_conditions'] == 1): ?>
    <?php echo $this->load->view('partials/terms-and-condition', ['block' => $block], true); ?>
<?php endif; ?>
<?php if (show_recaptcha() && $form['enable_captcha'] == 1): ?>
    <?php echo $this->load->view('partials/recaptcha', ['block' => $block], true); ?>
<?php endif; ?>
<div class="flexform-sumbit-button-wrapper">
    <input type="hidden" name="current" value="<?php echo flexformPerfectSerialize($block['id']) ?>">
    <input type="hidden" name="is_submit" value="1">
    <button data-current="<?php echo flexformPerfectSerialize($block['id']) ?>"
            style="background: <?php echo $submit_btn_bg_color; ?>;color: <?php echo $submit_btn_text_color; ?>;"
            class="btn btn-lg ff-submit-button flexform-next-button-preview tw-mt-4"><span><?php echo $label; ?></span>
        &nbsp;
        <?php echo $icon; ?>
    </button>
</div>