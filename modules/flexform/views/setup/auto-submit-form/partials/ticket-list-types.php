<?php
$ticket_list_types = [
   [
       'id'=>'department',
       'name'=>_flexform_lang('ticket-department')
   ],
    [
        'id'=>'priority',
        'name'=>_flexform_lang('ticket-priority')
    ],
    [
        'id'=>'service',
        'name'=>_flexform_lang('ticket-service')
    ],
];
?>
<div class="form-group ticket-list-types">
    <label for="<?php echo $name; ?>"><?php echo _flexform_lang($label); ?></label>
    <select class="form-control ticket_list_type_select" name="<?php echo $name; ?>" id="<?php echo $name; ?>">
        <option value=""><?php echo _flexform_lang('choose-type'); ?></option>
        <?php foreach($ticket_list_types as $type): ?>
            <option value="<?php echo $type['id']; ?>" <?php echo ($block[$column] == $type['id']) ? 'selected' : ''; ?>><?php echo $type['name']; ?></option>
        <?php endforeach; ?>
    </select>
</div>

