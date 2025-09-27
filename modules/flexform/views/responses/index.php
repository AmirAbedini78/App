<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head(); ?>
<?php $i = 1; ?>
<div id="wrapper">
    <div class="content">
        <div class="row">
            <div class="col-md-12">
                <div class="tw-flex tw-justify-between tw-items-center tw-mb-2 sm:tw-mb-4">
                    <h4 class="tw-my-0 tw-font-semibold tw-text-lg tw-self-end">
                        <?php echo $title; ?>
                    </h4>

                </div>
                <div class="tw-flex tw-justify-between tw-items-center tw-mb-2 sm:tw-mb-4">
                    <div class="pull-right">
                        <a href="<?php echo admin_url('flexform/responses/' . $form['slug']).'?tab=complete' ?>"
                           class="btn <?php echo ($active_tab == 'complete') ? 'btn-primary' : 'btn-secondary' ?>">
                            <?php echo _flexform_lang('completed'); ?>
                            &nbsp;&nbsp;<span class="badge badge-info"> <?php echo $completed_count; ?></span>
                        </a>
                        <a href="<?php echo admin_url('flexform/responses/' . $form['slug']).'?tab=partial' ?>"
                           class="btn  <?php echo ($active_tab == 'partial') ? 'btn-primary' : 'btn-secondary' ?>">
                            <?php echo _flexform_lang('partial'); ?>
                            &nbsp;&nbsp;<span class="badge badge-info"><?php echo $partial_count; ?></span>
                        </a>
                        <a href="<?php echo admin_url('flexform/setup/' . $form['slug']) ?>"
                           class="btn text-secondary">
                            <i class="fa fa-arrow-left"></i>
                            <?php echo _flexform_lang('goto-form-builder'); ?>
                        </a>
                    </div>

                </div>
                <div class="panel_s">
                    <div class="panel-body panel-table-full">
                        <table class="table dt-table table-striped" data-order-col="1" data-order-type="desc">
                            <thead>
                            <tr>
                                <th><?php echo '#' ?></th>
                                <th><?php echo _flexform_lang('submitted-at'); ?></th>
                                <?php foreach ($form_blocks as $key=>$column) {
                                    //skip thank you and statement
                                    if($column['block_type'] == 'thank-you' || $column['block_type'] == 'statement') {
                                        continue;
                                    }
                                    ?>
                                    <th><?php echo $column['title']; ?></th>
                                <?php } ?>
                                <th><?php echo _flexform_lang('actions'); ?></th>
                            </tr>
                            </thead>
                            <tbody>
                            <!-- loop through the forms -->
                            <?php foreach ($responses as $session_id=>$response) : ?>
                                <tr>
                                    <td><?php echo $i; ?></td>
                                    <td><?php echo _dt($response[0]['date_added']); ?></td>
                                        <?php foreach ($response as $r) :?>
                                            <td> <?php echo flexform_render_answer($r) ?> </td>
                                        <?php endforeach ?>
                                    <td>
                                        <a href="#"
                                           data-ssid="<?php echo $session_id; ?>"
                                           data-url="<?php echo admin_url('flexform/ajax'); ?>"
                                           data-active="<?php echo $active_tab; ?>"
                                           data-fid="<?php echo $form['id']; ?>"
                                           class="btn text-info btn-circle flexform-view-response">
                                            <?php echo _l('view'); ?>
                                        </a>
                                        <a href="#"
                                           data-ssid="<?php echo $session_id; ?>"
                                           data-fid="<?php echo $form['id']; ?>"
                                           data-url="<?php echo admin_url('flexform/ajax'); ?>"
                                           class="btn text-danger btn-circle flexform-delete-responses">
                                            <?php echo _l('delete'); ?>
                                        </a>
                                    </td>
                                </tr>
                            <?php $i++; endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<div class="modal fade" id="flexform_view_response_modal" tabindex="-1" role="dialog">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span
                        aria-hidden="true">&times;</span></button>
                <h4 class="modal-title"><?php echo _flexform_lang('submission-detail'); ?></h4>
            </div>
            <div class="modal-body">

            </div>
        </div><!-- /.modal-content -->
    </div><!-- /.modal-dialog -->
</div><!-- /.modal -->
<?php init_tail(); ?>
</body>

</html>