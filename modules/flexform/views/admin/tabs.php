<?php
$type_id = isset($type_id) ? $type_id :  $this->uri->segment(4);;
$type = isset($type) ? $type : "customers";
$forms = flexform_get_all_active_forms($type, false, $type_id, false);
$active_tab = "complete";
?>
<div class="panel_s">
    <div class="panel-body table-responsive">
        <h4 class="tw-m-2 tw-mb-4"><?php echo (isset($header) && $header) ? $header : _flexform_lang('forms'); ?></h4>
        <div class="panel-table-full">
            <table id="contacts-table" class="table dt-table">
                <thead>
                    <tr>
                        <th><?php echo _flexform_lang('title'); ?></th>

                        <th><?php echo _flexform_lang('submitted_at'); ?></th>
                        <th><?php echo _flexform_lang('actions'); ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($forms as $form) {
                        $completed_form = flexform_has_user_completed_form($form['id'], $type, $type_id);
                    ?>
                        <tr>
                            <td><a href="<?php echo admin_url('flexform/setup/' . $form['slug']); ?>"><?php echo $form['name']; ?></a></td>
                            <?php if ($completed_form) { ?>
                                <td><?php echo $completed_form['date_added']; ?></td>
                                <td>
                                    <a href="#"
                                        data-ssid="<?php echo $completed_form['session_id']; ?>"
                                    data-url="<?php echo admin_url('flexform/ajax'); ?>"
                                    data-active="<?php echo $active_tab; ?>"
                                    data-fid="<?php echo $form['id']; ?>"
                                    class="btn text-info btn-circle flexform-view-response">
                                    <?php echo _flexform_lang('view_response'); ?>
                                </a>
                                </td>
                                <?php }else{ ?>
                                    <td><?php echo _flexform_lang('awaiting_submission'); ?></td>
                                    <td><?php echo _flexform_lang('awaiting_submission'); ?></td>
                                <?php } ?>
                            

                        </tr>
                    <?php } ?>
                </tbody>
            </table>
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
<?php hooks()->add_action('app_admin_footer', 'flexform_init_response_modal');
function flexform_init_response_modal()
{ ?>
    <script>
        $(function() {
            $(document).ready(function() {
                window.flexformDownloadModal = function(obj) {
                    //disable the download buttonÒ
                    $(obj).prop('disabled', true);
                    const modalContent = document.getElementById('flexformSubmissionAnswers');
                    try {
                        const opt = {
                            margin: [20, 10, 20, 10], // [top, left, bottom, right]
                            filename: 'submission.pdf',
                            html2canvas: {
                                scale: 2
                            }, // Improve quality
                            jsPDF: {
                                unit: 'mm',
                                format: 'a4',
                                orientation: 'portrait'
                            }
                        };

                        html2pdf()
                            .from(modalContent)
                            .set(opt)
                            .save();
                        //enable the download button
                        $(obj).prop('disabled', false);
                    } catch (e) {
                        console.log(e);
                    }
                }

                $(document).on('click', '.flexform-view-response', function() {
                    const obj = $(this);
                    const url = $(obj).data('url');
                    const session_id = $(obj).data('ssid');
                    const fid = $(obj).data('fid');
                    const active = $(obj).data('active');
                    const data = {
                        action: 'load_response',
                        ssid: session_id,
                        fid: fid,
                        active: active
                    };
                    $.post(url, data, function(response) {
                        const r = JSON.parse(response);
                        if (r.status === 'error') {
                            alert_float('danger', r.message);
                            return false;
                        }
                        const modal = $('#flexform_view_response_modal');
                        modal.find('.modal-body').html(r.html);
                        modal.modal('show');
                    });
                    return false;
                });

                //when we change the staff dropdown, we need to reload the page
                $('select[name="staff_id"]').on('change', function() {
                    const staff_id = $(this).val();
                    window.location.href = '<?php echo admin_url('flexform/staff/'); ?>' + staff_id;
                });
            });

        });
    </script>
    <script src="<?php echo module_dir_url('flexform', 'assets/js/html2pdf.bundle.min.js'); ?>"></script>
<?php } ?>