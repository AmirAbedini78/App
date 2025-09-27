<!-- map blocks to leads table -->
<?php $columns = flexform_get_form_columns($form); ?>
<?php if($columns) : ?>
<table class="table table-striped">
    <thead>
    <tr>
        <th><?php echo _flexform_lang('table-column'); ?></th>
        <th><?php echo _flexform_lang('blocks'); ?></th>
    </tr>
    </thead>
    <tbody>
    <?php foreach($columns as $key=>$column) : ?>
    <tr>
        <td>
            <?php echo strtoupper($column); ?>
            <input type="hidden" name="column[]" value="<?php echo $key; ?>" />
        <td>
            <?php echo $this->load->view('setup/modals/partial/map-to-column/partial-form-block-list', ['form'=>$form,'column_key'=>$key], true); ?>
        </td>
    </tr>
    <?php endforeach; ?>
    </tbody>
</table>
<?php endif; ?>