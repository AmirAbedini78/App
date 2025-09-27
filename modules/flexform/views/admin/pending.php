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
                    
                </div>
                <div class="panel_s">
                    <div class="panel-body panel-table-full">
                        <table class="table dt-table" data-order-col="2" data-order-type="desc">
                            <thead>
                            <tr>
                                <th><?php echo _flexform_lang('title'); ?></th>
                                <th><?php echo _flexform_lang('description'); ?></th>
                                <th><?php echo _flexform_lang('actions'); ?></th>
                            </tr>
                            </thead>
                            <tbody>
                                    <?php foreach($forms as $form): ?>
                                        <tr>
                                            <td><?php echo $form['name']; ?></td>
                                            <td><?php echo $form['description']; ?></td>
                                            <td>
                                                <a href="<?php echo site_url('flexform/vf/'.$form['slug']); ?>" class="btn text-primary"><?php echo _flexform_lang('view'); ?></a>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<?php init_tail(); ?>
</body>

</html>

