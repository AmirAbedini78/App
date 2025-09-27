<div class="modal fade" id="flexform-logic-to-question" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-md">
        <div class="modal-content">
            <?php echo form_open(admin_url('flexform/update_logic'), ['id'=>'flexform_logic_form']); ?>
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title"><i class="fa fa-puzzle-piece" aria-hidden="true"></i>
                    <?php echo _flexform_lang('logic');  ?>
                </h4>
            </div>
            <div class="modal-body">
                <div class="flexform-logic-title-header">
                    <?php echo _flexform_lang('add-logic-for-'); ?>
                </div>
            </div>
            <div class="modal-footer">
                <button type="submit" class="btn btn-primary"><?php echo _flexform_lang('save'); ?></button>
                <button type="button" class="btn btn-default" data-dismiss="modal"><?php echo _l('close'); ?></button>
            </div>
            <?php echo form_close(); ?>
        </div>
    </div>
</div>