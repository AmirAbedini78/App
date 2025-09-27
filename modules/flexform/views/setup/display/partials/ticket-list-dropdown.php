<?php
$this->load->model('tickets_model');
$this->load->model('departments_model');
$departments = $this->departments_model->get();
$priorities  = $this->tickets_model->get_priority();
$priorities['callback_translate'] = 'ticket_priority_translate';
$services  = $this->tickets_model->get_service();


$ticket_list_type = $block['ticket_list_type'];
if($ticket_list_type == 'department'):
    $selectedDepartment = isset($default_value) && $default_value ? $default_value[0] : '';
    echo '<div class="' . ($this->input->get('department_id') ? 'hide' : '') . '">';
    echo render_select($name, $departments, ['departmentid', 'name'], '', $selectedDepartment, ['required' => 'true']);
    echo '</div>';
    ?>

<?php elseif($ticket_list_type == 'priority'):
    $selectedPriority = isset($default_value) && $default_value ? $default_value : hooks()->apply_filters('new_ticket_priority_selected', 2);
    echo render_select($name, $priorities, ['priorityid', 'name'], '', $selectedPriority, ['required' => 'true']);
    ?>

<?php elseif($ticket_list_type == 'service'):
    $selectedService = isset($default_value) && $default_value ? $default_value : '';
    echo '<div class="' . ($this->input->get('hide_service') == 1 ? 'hide' : '') . '">';
    echo render_select($name, $services, ['serviceid', 'name'], '', $selectedService);
    echo '</div>';
    ?>
<?php endif; ?>
