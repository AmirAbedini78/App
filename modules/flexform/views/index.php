<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head(); ?>
<div id="wrapper">
    <div class="content">
        <div class="row">
            <div class="col-md-12">
                <div class="tw-flex tw-justify-between tw-items-center tw-mb-2 sm:tw-mb-4">
                    <h4 class="tw-my-0 tw-font-semibold tw-text-lg tw-self-end">
                        <?php echo $title; ?>
                    </h4>
                    <div>
                        <a href="#" data-toggle="modal" data-target="#flexform_new_form"
                           class="btn btn-primary mright5">
                            <i class="fa fa-plus"></i> <?php echo _flexform_lang('new_form'); ?>
                        </a>
                    </div>
                </div>
                <div class="panel_s">
                    <div class="panel-body panel-table-full">
                        <table class="table dt-table" data-order-col="8" data-order-type="desc">
                            <thead>
                            <tr>
                                <th><?php echo _flexform_lang('title'); ?></th>
                                <th><?php echo _flexform_lang('description'); ?></th>
                                <th><?php echo _flexform_lang('created_by'); ?></th>
                                <th><?php echo _flexform_lang('connected_to'); ?></th>
                                <th><?php echo _flexform_lang('responses'); ?></th>
                                <th><?php echo _flexform_lang('published-status'); ?></th>
                                <th><?php echo _flexform_lang('submission-status'); ?></th>
                                <th><?php echo _flexform_lang('privacy'); ?></th>
                                <th><?php echo _flexform_lang('date_created'); ?></th>
                            </tr>
                            </thead>
                            <tbody>
                            <!-- loop through the forms -->
                            <?php foreach ($forms as $form) {?>
                                <tr>
                                    <td>
                                        <a href="<?php echo  admin_url('flexform/setup/' . $form['slug']) ?>"> <?php echo $form['name']; ?></a>
                                        <div class="row-options">
                                            <a href="<?php echo admin_url('flexform/setup/' . $form['slug']); ?>"> <?php echo _flexform_lang('form-builder') ?></a> |
                                            <a href="<?php echo admin_url('flexform/responses/' . $form['slug']); ?>" class="text-success"> <?php echo _flexform_lang('responses') ?></a> |
                                            <a href="<?php echo admin_url('flexform/duplicate/' . $form['slug']); ?>" class="text-info"> <?php echo _flexform_lang('duplicate') ?></a> |
                                            <a href="<?php echo admin_url('flexform/delete/' . $form['slug']); ?>" class="_delete text-danger"><?php echo _l('delete')?> </a>
                                        </div>
                                    </td>
                                    <td><?php echo $form['description']; ?></td>
                                    <td><?php echo get_staff_full_name($form['staffid']); ?></td>
                                    <td><?php echo $form['type']; ?></td>
                                    <td><?php echo flexform_count_responses($form['id']) ?></td>
                                    <td><?php echo $form['published'] == 1 ? _flexform_lang('published') : _flexform_lang('draft'); ?></td>
                                    <td><?php echo strtotime($form['end_date']) >  time() ? _flexform_lang('open') : _flexform_lang('closed'); ?></td>
                                    <td><?php echo flexform_get_privacy_name($form); ?></td>
                                    <td><?php echo _dt($form['date_added']); ?></td>
                                </tr>
                            <?php } ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<div class="modal fade" id="flexform_new_form" tabindex="-1" role="dialog">
    <div class="modal-dialog">
        <?php echo form_open(admin_url('flexform/new_form')); ?>
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span
                        aria-hidden="true">&times;</span></button>
                <h4 class="modal-title"><?php echo _l('new_form'); ?></h4>
            </div>
            <div class="modal-body">
                <?php echo render_input('title', _flexform_lang('form_title'), '', 'text'); ?>
                <!-- select dropdown to select where the form belongs to -->
                <?php echo render_select('type', $connected_to, ['id', 'name'], _flexform_lang('connected_to_optional'), '', [], [], 'selectpicker'); ?>
                <!-- description -->
                <?php echo render_textarea('description', _flexform_lang('description'), '', ['rows' => 3]); ?>
                <!--privacy -->
                <?php echo render_select('privacy', $privacy, ['id', 'name'], _flexform_lang('privacy'), 'public', [], [], 'selectpicker'); ?>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal"><?php echo _l('close'); ?></button>
                <button type="submit" class="btn btn-primary"><?php echo _l('submit'); ?></button>
            </div>
        </div><!-- /.modal-content -->
        <?php echo form_close(); ?>
    </div><!-- /.modal-dialog -->
</div><!-- /.modal -->
<?php init_tail(); ?>
</body>

</html>