<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php
//create an object with recaptcha and gdpr
$form_object = new stdClass();
$form_object->recaptcha = $form['enable_captcha'];
$form_object->language = 'english';
?>
<?php app_external_form_header($form_object); ?>
<?php echo $this->load->view('client/navigation'); ?>
<?php echo form_open_multipart(site_url('flexform/submit/' . $form['slug']), ['class' => 'flexform-client-form']); ?>
<div id="flexform-client-block-container" class="flexform-client-block-container"
     data-upurl="<?php echo site_url('flexform/upload'); ?>"
     data-url="<?php echo site_url('flexform/submit/' . $form['slug']); ?>">
    <?php echo ($block) ? flexform_get_display_partial($block,false,false,true) : '' ?>
</div>
<input type="hidden" id="ff_sname" name="sname" value="<?php echo $session_name; ?>">
<input type="hidden" id="ff_svalue" name="svalue" value="<?php echo $session_value; ?>">
<input type="hidden" id="ff_bnh" name="ff_bnh" value="<?php echo ($block) ? $block['id'] : 0 ?>">
<?php echo form_close(); ?>
<div class="flexform-footer-actions">

</div>
<?php app_external_form_footer($form_object); ?>
