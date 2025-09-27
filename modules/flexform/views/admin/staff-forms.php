<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head(); ?>
<div id="wrapper">
    <div class="content">
        <div class="row">
            <div class="col-md-12">
                <div class="tw-flex tw-justify-between tw-items-center tw-mb-2 sm:tw-mb-4">
                    <h4 class="tw-my-0 tw-font-semibold tw-text-lg tw-self-end">
                        <?php echo _flexform_lang('staff_forms'); ?>
                    </h4>
                </div>
                <div class="tw-flex tw-justify-between tw-items-center tw-mb-2 sm:tw-mb-4">
                    <select class="form-control flexform-staff-forms-select" name="staff_id">
                        <option value=""><?php echo _flexform_lang('select_staff'); ?></option>
                        <?php foreach ($members as $member) { ?>
                            <option value="<?php echo $member['staffid']; ?>" <?php echo $staff_id == $member['staffid'] ? 'selected' : ''; ?>><?php echo $member['firstname'] . ' ' . $member['lastname']; ?></option>
                        <?php } ?>
                    </select>
                </div>
                <div class="panel_s">
                    <div class="panel-body panel-table-full">
                       <?php echo $this->load->view('admin/tabs', ['type' => 'staff', 'type_id' => $staff_id,'header' => $title]); ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<?php init_tail(); ?>
</body>

</html>