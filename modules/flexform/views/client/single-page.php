<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php
//create an object with recaptcha and gdpr
$form_object = new stdClass();
$form_object->recaptcha = $form['enable_captcha'];
$form_object->language = 'english';
?>
<?php app_external_form_header($form_object); ?>
<?php echo $this->load->view('client/navigation'); ?>
<?php echo form_open_multipart(site_url('flexform/sp_submit/' . $form['slug']), ['class' => 'flexform-sp-client-form']); ?>
<div id="flexform-client-block-container" class="flexform-single-page-layout"
     data-spurl="<?php echo site_url('flexform/spa/'.$form['slug']); ?>"
     data-url="<?php echo site_url('flexform/submit/' . $form['slug']); ?>">
    <?php foreach ($all_blocks as $b) {
        $b = flexform_arrange_block($b);
        //remove thank you block
        if (flexform_is_thank_you_block($b)) { continue; } ?>
        <div class="flexform-single-page-each-block <?php echo (flexform_is_statement_block($b)) ? '' : 'fsp-padding' ?>" data-block-id="<?php echo $b['id']; ?>">
            <?php echo flexform_get_display_partial($b,false,false,false) ?>
        </div>
        <?php if(flexform_is_statement_block($b)):?>
            <hr class="fsp-hr" />
        <?php endif; ?>
    <?php } ?>
    <div class="flexform-single-page-each-block fsp-padding">
    <?php echo $this->load->view('partials/single-page-submit-button', ['block' => $b,'is_submit'=>true,], true); ?>
    </div>
</div>
<input type="hidden" id="ff_sname" name="sname" value="<?php echo $session_name; ?>">
<input type="hidden" id="ff_svalue" name="svalue" value="<?php echo $session_value; ?>">
<!--<input type="hidden" id="ff_bnh" name="ff_bnh" value="<?php /*echo ($block) ? $block['id'] : 0 */?>">-->
<?php echo form_close(); ?>
<div class="flexform-footer-actions">
    <?php
    $csrfName = $this->security->get_csrf_token_name();
    $csrfHash = $this->security->get_csrf_hash();
    ?>
    <input type="hidden" id="flexform-spa-token" name="<?= $csrfName ?>" value="<?= $csrfHash ?>">
</div>
<?php app_external_form_footer($form_object); ?>
