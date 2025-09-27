<div class="">
    <?php echo form_hidden('form_id', $form['id']); ?>
    <?php if(flexform_requires_mapping($form)): ?>
        <h2 class="tw-font-semibold tw-text-lg tw-mt-0"><?php echo _flexform_lang('map-blocks-to-'.$form['type'].'-table') ?></h2>
        <div class="alert alert-info">
            <?php echo _flexform_lang('map-column-to-blocks-to-automatically-sync-response-to-destination-table'); ?>
        </div>
        <?php echo $this->load->view('setup/modals/partial/map-to-column/fields', ['form'=>$form], true); ?>
    <?php else: ?>
    <?php endif; ?>
    <div class="alert alert-info">
        <?php echo _flexform_lang('publishing-a-form-will-make-it-live-and-accessible-to-the-public'); ?>
    </div>
</div>