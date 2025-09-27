<h4 class="tw-mt-0 tw-mb-3 tw-font-semibold tw-text-lg tw-text-neutral-700 section-heading section-heading-projects">
    <?php echo _l('flexform_my_forms'); ?>
</h4>

<div class="panel_s">
    <div class="panel-body">
        <table class="table dt-table table-projects" data-order-col="2" data-order-type="desc">
            <thead>
                <tr>
                    <th><?php echo _l('flexform_title'); ?></th>
                    <th><?php echo _l('flexform_description'); ?></th>
                    <th><?php echo _l('flexform_actions'); ?></th>
                </tr>
            </thead>
            <tbody>
                <?php foreach($forms as $form){ ?>
                    <tr>
                        <td><?php echo $form['name']; ?></td>
                        <td><?php echo $form['description']; ?></td>
                        <td>
                            <a href="<?php echo site_url('flexform/vf/' . $form['slug']); ?>" class="btn text-primary">
                                <?php echo _l('flexform_view_form'); ?>
                            </a>
                        </td>
                    </tr>
                <?php } ?>
            </tbody>
        </table>
    </div>
</div>