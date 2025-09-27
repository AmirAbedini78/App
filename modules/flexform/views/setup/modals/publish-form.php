<div class="modal fade" id="flexform_publish_modal" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-md">
        <div class="modal-content">
            <?php echo form_open(admin_url('flexform/publish'), ['id'=>'flexform_publish_modal']); ?>
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title">
                    <?php echo _flexform_lang('publish');  ?>
                </h4>
            </div>
            <div class="modal-body">
            </div>
            <div class="modal-footer">
                <button type="submit" class="btn btn-primary"><?php echo _flexform_lang('save-changes-and-publish'); ?></button>
                <button type="button" class="btn btn-default" data-dismiss="modal"><?php echo _l('close'); ?></button>
            </div>
            <?php echo form_close(); ?>
        </div>
    </div>
</div>