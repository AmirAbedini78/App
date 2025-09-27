<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Flexform extends AdminController
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('flexform_model');
        $this->load->helper('flexform/flexform');
    }

    public function index()
    {
        if(!has_permission(FLEXFORM_MODULE_NAME, '', 'view')){
            set_alert('warning', _flexform_lang('permission_denied'));
            redirect(admin_url(''));
            return;
        }
        $data['title'] = _flexform_lang('forms');
        $data['connected_to'] = flexform_get_connected_to();
        $data['privacy'] = flexform_get_privacy();
        $data['forms'] = $this->flexform_model->all();
        $this->load->view('index', $data);
    }

    public function pending(){
        $data['title'] = _flexform_lang('my_forms');
        $forms = [];
        $active_forms = $this->flexform_model->get_all_active_forms('staff');
        foreach($active_forms as $form){
            $form_id = $form['id'];
            $completed = flexform_has_user_completed_form($form_id,'staff',get_staff_user_id());
            if(!$completed){
                $forms[] = $form;
            }
        }
        $data['forms'] = $forms;
        $this->load->view('admin/pending', $data);
    }

    public function staff($staff_id = '')
    {
        $data['title'] = _flexform_lang('staff_forms');
        $data['members']  =  $this->staff_model->get('', [
            'active' => 1,
            'is_not_staff' => 0,
        ]);
        //debug
        if(!$staff_id){
            //get the first staff member
            $staff_id = $data['members'][0]['staffid'];
            $data['title'] = $data['members'][0]['firstname'] . ' ' . $data['members'][0]['lastname'] . ' ' . _flexform_lang('forms');
        }else{
            //get the staff member from members array filter by staffid
            $staff_member = array_filter($data['members'], function($member) use ($staff_id){
                return $member['staffid'] == $staff_id;
            });
            $staff_member = array_values($staff_member)[0];
            $data['title'] = $staff_member['firstname'] . ' ' . $staff_member['lastname'] . ' ' . _flexform_lang('forms');
        }
        $data['staff_id'] = $staff_id;
        $this->app_css->add('flexform-css', module_dir_url('flexform', 'assets/css/flexform.css'), 'admin', ['app-css']);
        $this->load->view('admin/staff-forms', $data);
    }

    public function new_form()
    {
        if(!has_permission(FLEXFORM_MODULE_NAME, '', 'create')){
            set_alert('warning', _flexform_lang('permission_denied'));
            redirect(admin_url(''));
            return;
        }
        $this->load->model('flexform_model');
        $this->load->library('form_validation');
        $this->form_validation->set_rules('title', _flexform_lang('form_title'), 'required');
        if ($this->form_validation->run() == false) {
            set_alert('warning', validation_errors());
            redirect(admin_url('flexform'));
        }
        $data = [
            'name' => $this->input->post('title'),
            'slug' => md5($this->input->post('title').'-'.uniqid()),
            'type' => $this->input->post('type'),
            'description' => $this->input->post('description'),
            'date_added' => date('Y-m-d H:i:s'),
            'date_updated' => date('Y-m-d H:i:s'),
            'published' => '0',
            'staffid' => get_staff_user_id(),
            'privacy' => $this->input->post('privacy'),
        ];
        $form_id = $this->flexform_model->add($data);
        if($form_id){
            flexform_add_default_blocks($form_id);
            redirect(admin_url('flexform/setup/'.$data['slug']));
        }
        //we need to add
        set_alert('success', _flexform_lang('form_added_successfully'));
        redirect(admin_url('flexform'));
    }

    public function update_form(){
        $this->load->model('flexform_model');
        $form_id = $this->input->post('id');
        $form = $this->flexform_model->get(['id' => $form_id]);
        if(!$form){
            set_alert('warning', _flexform_lang('form_not_found'));
              redirect(admin_url('flexform'));
        }
        $data = [
            'name' => $this->input->post('name'),
            'description' => $this->input->post('description'),
            'allow_duplicate_leads' => $this->input->post('allow_duplicate_leads') ? "1" : "0",
            'lead_name_prefix' => $this->input->post('lead_name_prefix'),
            'lead_source' => $this->input->post('lead_source'),
            'lead_status' => $this->input->post('lead_status'),
            'responsible' => $this->input->post('responsible'),
            'notify_form_submission' => $this->input->post('notify_form_submission') ? "1" : "0",
            'notify_type' => $this->input->post('notify_type'),
            'notify_ids' => serialize($this->input->post('notify_ids_staff')),
            'submit_btn_name' => $this->input->post('submit_btn_name'),
            'submit_btn_text_color' => $this->input->post('submit_btn_text_color'),
            'submit_btn_bg_color' => $this->input->post('submit_btn_bg_color'),
            'date_updated' => date('Y-m-d H:i:s'),
            'end_date' => to_sql_date($this->input->post('end_date'),true),
            'enable_captcha' => $this->input->post('enable_captcha') ? "1" : "0",
            'require_terms_and_conditions' => $this->input->post('require_terms_and_conditions') ? "1" : "0",
            'enable_single_page' => $this->input->post('enable_single_page') ? "1" : "0",
            'data_submission_notification_emails'=> $this->input->post('data_submission_notification_emails'),
            'privacy' => $this->input->post('privacy'),
            'customerids' => ($this->input->post('privacy') == 'customers' && $this->input->post('customerids')) ? serialize($this->input->post('customerids')) : '',
            'staffids' => ($this->input->post('privacy') == 'staff' && $this->input->post('staffids')) ? serialize($this->input->post('staffids')) : '',
            ];
        //print_r($data);die();
        $this->flexform_model->update($data, $form_id);
        set_alert('success', _flexform_lang('changes-saved-successfully'));
        $redirect_url = $this->input->post('redirect_url') ? $this->input->post('redirect_url') : admin_url('flexform');
        redirect($redirect_url);
    }

    function setup($slug = ''){
        if(!has_permission(FLEXFORM_MODULE_NAME, '', 'view')){
            set_alert('warning', _flexform_lang('permission_denied'));
            redirect(admin_url(''));
            return;
        }
        $data['title'] = _flexform_lang('setup-your-form');
        $form = $this->flexform_model->get_by_slug_or_id($slug);
        if (!$form) {
            set_alert('warning', _flexform_lang('form_not_found'));
            redirect(admin_url('flexform'));
        }
        //get all blocks
        $data['blocks'] = flexform_get_all_blocks($form['id']);

        $data['form'] = $form;
        $this->load->model('roles_model');
        $this->load->model('leads_model');
        $data['roles']    = $this->roles_model->get();
        //if the form is connected to leads
        $body_class = '';
        if($form['type'] == 'leads') {
            $data['sources'] = $this->leads_model->get_source();
            $data['statuses'] = $this->leads_model->get_status();
            $body_class = 'web-to-lead-form';
        }

        $data['members'] = $this->staff_model->get('', [
            'active'       => 1,
            'is_not_staff' => 0,
        ]);
        $this->app_scripts->add('signature-pad','assets/plugins/signature-pad/signature_pad.min.js', 'admin', ['app-js']);
        $this->app_css->add('flexform-css', module_dir_url('flexform', 'assets/css/flexform.css'), 'admin', ['app-css']);
        $this->app_scripts->add('flexform-js', module_dir_url('flexform', 'assets/js/flexform.js'), 'admin', ['app-js']);
        $this->load->view('setup/index', array('props'=>$data,'bodyclass'=>$body_class,'title' => $data['title'], 'form' => $form,'blocks' => $data['blocks']));
    }

    function ajax(){
        $type = $this->input->post('action');
        $this->load->model('flexformblocks_model');
        //check if user have permission to update the form
        if(!has_permission(FLEXFORM_MODULE_NAME, '', 'edit')){
            echo json_encode(['status' => 'error','message' => _flexform_lang('permission_denied')]);
            return;
        }
        switch ($type){
            case 'remove_image':
                $block_id = $this->input->post('id');
                $block = $this->flexformblocks_model->get(['id' => $block_id]);
                if(!$block){
                    echo json_encode(['status' => 'error','message' => _flexform_lang('block_not_found')]);
                    break;
                }
                $this->flexformblocks_model->update(['images' => ''], $block_id);
                //remove image from folder
                $image_path = FLEXFORM_FOLDER . $block['images'];
                if (file_exists($image_path)) {
                    unlink($image_path);
                }
                echo json_encode(['status' => 'success','message' => _flexform_lang('image_removed_successfully')]);
                break;
            case 'add_new_block':
                $block_type = $this->input->post('block_type');
                $title = flexform_blocks($block_type)['default_label'];
                $form_id = $this->input->post('id');
                $data = [
                    'form_id' => $form_id,
                    'block_type' =>$block_type,
                    'date_added' => date('Y-m-d H:i:s'),
                    'date_updated' => date('Y-m-d H:i:s'),
                    'title' => $title,
                    'button_text'=>_flexform_lang('next')
                ];
                //get the last block order
                $last_block_order = $this->flexformblocks_model->get_last_block_order($form_id);
                $index =  $last_block_order + 1;
                $data['block_order'] = $index;
                $block_id = $this->flexformblocks_model->add($data);
                if(!$block_id){
                    echo json_encode(['status' => 'error','message' => _flexform_lang('error_occurred')]);
                    break;
                }
                $block = flexform_arrange_block($this->flexformblocks_model->get(['id'=>$block_id]));
                $block_li_view = $this->load->view('setup/blocks/each-block-li', ['block' => $block,'index'=>$index], true);
                $middle_content = flexform_get_display_partial($block);
                $right_side_content = flexform_get_setup_autosubmitform_partial($block);
                echo json_encode([
                    'left_hand_side'=>$block_li_view,
                    'middle_content'=>$middle_content,
                    'right_hand_side'=>$right_side_content,
                    'status' => 'success',
                    'message' => _flexform_lang('block_added_successfully')]);
                break;
            case 'load_block':
                $block = $this->flexformblocks_model->get(['id' => $this->input->post('id')]);
                if(!$block){
                    echo json_encode(['status' => 'error','message' => _flexform_lang('block_not_found')]);
                    break;
                }
                $block = flexform_arrange_block($block);
                $middle_content = flexform_get_display_partial($block);
                $right_side_content = flexform_get_setup_autosubmitform_partial($block);
                echo json_encode([
                    'left_hand_side'=>'',
                    'middle_content'=>$middle_content,
                    'right_hand_side'=>$right_side_content,
                    'status' => 'success',
                    'message' => _flexform_lang('block_loaded_successfully')]);
                break;
            case 'load_block_logic':
                //load block with block id
                $block = $this->flexformblocks_model->get(['id' => $this->input->post('id')]);
                if(!$block){
                    echo json_encode(['status' => 'error','message' => _flexform_lang('block_not_found')]);
                    break;
                }
                //check if the block can have logic
                if(!flexform_can_block_have_logic($block)){
                    echo json_encode(['status' => 'error','message' => _flexform_lang('block_cannot_have_logic')]);
                    break;
                }
                //extract existing logic from the block
                $logic = (isset($block['validation_logic']) && $block['validation_logic']) ? flexformPerfectUnserialize($block['validation_logic']) : [];
                $view = $this->load->view('setup/logic/index', ['block' => flexform_arrange_block($block), 'logic' => $logic], true);
                echo json_encode([
                    'html'=>$view,
                    'status' => 'success',
                    'message' => _flexform_lang('block_loaded_successfully')]);
                break;
            case 'load_block_logic_operator_and_value_field':
                $block = $this->flexformblocks_model->get(['id' => $this->input->post('id')]);
                if(!$block){
                    echo json_encode(['status' => 'error','message' => _flexform_lang('block_not_found')]);
                    break;
                }
                $commands = flexform_logic_commands($block);
                $index = $this->input->post('index');
                $view = $this->load->view('setup/logic/block-logic-operator-and-value-field', ['index'=>$index,'block' => flexform_arrange_block($block),'commands'=>$commands], true);
                echo json_encode([
                    'html'=>$view,
                    'status' => 'success',
                    'message' => _flexform_lang('block_loaded_successfully')]);
                break;
            case 'add_new_logic_condition_field':
                $block = $this->flexformblocks_model->get(['id' => $this->input->post('id')]);
                if(!$block){
                    echo json_encode(['status' => 'error','message' => _flexform_lang('block_not_found')]);
                    break;
                }
                $commands = flexform_logic_commands($block);
                $index = $this->input->post('index');
                $view = $this->load->view('setup/logic/condition-field-with-operator', ['index'=>$index,'block' => flexform_arrange_block($block),'commands'=>$commands], true);
                echo json_encode([
                    'html'=>$view,
                    'status' => 'success',
                    'message' => _flexform_lang('block_loaded_successfully')]);
                break;
            case 'add_new_logic':
                $block = $this->flexformblocks_model->get(['id' => $this->input->post('id')]);
                if(!$block){
                    echo json_encode(['status' => 'error','message' => _flexform_lang('block_not_found')]);
                    break;
                }
                $index = $this->input->post('index');
                $view = $this->load->view('setup/logic/logic-field', ['index'=>$index,'block' => flexform_arrange_block($block)], true);
                echo json_encode([
                    'html'=>$view,
                    'status' => 'success',
                    'message' => _flexform_lang('block_loaded_successfully')]);
                break;
            case 'update_block_order':
                $blocks = $this->input->post('blocks');
                $i = 1;
                foreach ($blocks as $block_id){
                    $this->flexformblocks_model->update(['block_order' => $i], $block_id);
                    $i++;
                }
                echo json_encode(['status' => 'success','message' => _flexform_lang('block_order_updated_successfully')]);
                break;
            case 'publish_form_content':
                $form_id = $this->input->post('id');
                $form = $this->flexform_model->get(['id' => $form_id]);
                if(!$form){
                    echo json_encode(['status' => 'error','message' => _flexform_lang('form_not_found')]);
                    break;
                }
                $view = $this->load->view('setup/modals/partial/publish-content', ['form' => $form], true);
                echo json_encode([
                    'html'=>$view,
                    'status' => 'success']);
                break;
            case 'delete_block':
                if(!has_permission(FLEXFORM_MODULE_NAME, '', 'delete')){
                    echo json_encode(['status' => 'error','message' => _flexform_lang('permission_denied')]);
                    return;
                }
                $block_id = $this->input->post('id');
                $block = $this->flexformblocks_model->get(['id' => $block_id]);
                if(!$block){
                    echo json_encode(['status' => 'error','message' => _flexform_lang('block_not_found')]);
                    break;
                }
                $this->flexformblocks_model->delete($block_id);
                echo json_encode(['status' => 'success','message' => _flexform_lang('block_deleted_successfully')]);
                break;
            case 'delete_responses':
                if(!has_permission(FLEXFORM_MODULE_NAME, '', 'delete')){
                    echo json_encode(['status' => 'error','message' => _flexform_lang('permission_denied')]);
                    return;
                }
                $this->load->model('flexformcompleted_model');
                $this->load->model('flexformblockanswer_model');
                $session_id = $this->input->post('ssid');
                $form_id = $this->input->post('fid');
                //get form responses and remove files if any exist
                $this->cleanup_files($form_id, $session_id);
                $this->load->model('flexformblockanswer_model');
                $this->flexformblockanswer_model->delete(['session_id' => $session_id]);
                //if the session is in completed, delete it
                $this->load->model('flexformcompleted_model');
                $this->flexformcompleted_model->delete(['session_id' => $session_id]);
                echo json_encode(['status' => 'success','message' => _flexform_lang('response_deleted_successfully')]);
                break;

            case 'load_response':
                $session_id = $this->input->post('ssid');
                $form_id = $this->input->post('fid');
                $active_tab = $this->input->post('active');
                //check if form exists
                $form = $this->flexform_model->get(['id' => $form_id]);
                if(!$form){
                    echo json_encode(['status' => 'error','message' => _flexform_lang('form_not_found')]);
                    break;
                }
                $responses = $this->flexform_get_response($form_id, $session_id,$active_tab);
                $view = $this->load->view('responses/detail', ['response' => $responses[$session_id],'all_blocks'=>flexform_get_all_blocks($form_id)], true);
                echo json_encode([
                    'html'=>$view,
                    'status' => 'success']);
                break;

            default:
                echo json_encode(['status' => 'error','message' => _flexform_lang('invalid_request')]);
                break;
        }
    }

    public function update_block(){
        $this->load->model('flexformblocks_model');
        $block_id = $this->input->post('id');
        $block = $this->flexformblocks_model->get(['id' => $block_id]);
        $image_path = "";
        if(!$block){
            echo json_encode(['status' => 'error','message' => _flexform_lang('block_not_found')]);
        }
        //check if file is selected
        if(isset($_FILES['images']) && $_FILES['images']['name']){
            $this->load->library('upload');
            $this->upload->initialize(flexform_upload_config());
            if (!$this->upload->do_upload('images')) {
                echo json_encode(['status' => 'error','message' => $this->upload->display_errors()]);
                return;
            }
            $upload_data = $this->upload->data();
            $image_path = $upload_data['file_name'];
        }
        $data = [
            'id' => $block_id,
            'title' => $this->input->post('question'),
            'description' => $this->input->post('description'),
            'button_text' => $this->input->post('button_text'),
            'placeholder' => $this->input->post('placeholder'),
            'required' => $this->input->post('is_required'),
            'random' => $this->input->post('random'),
            'maximum_number' => $this->input->post('maximum_number'),
            'minimum_number' => $this->input->post('minimum_number'),
            'rating' => $this->input->post('rating'),
            'default_value' => $this->input->post('default_value'),
            'allow_multiple' => $this->input->post('allow_multiple'),
            'file_types' => $this->input->post('file_types'),
            'simple_uploader' => $this->input->post('simple_uploader'),
            'horizontal' => $this->input->post('horizontal'),
            'text_align' => $this->input->post('text_align'),
            'redirect_url' => $this->input->post('redirect_url'),
            'redirect_message' => $this->input->post('redirect_message'),
            'redirect_delay' => $this->input->post('redirect_delay'),
            'is_country' => $this->input->post('is_country'),
            'ticket_list_type' => $this->input->post('ticket_list_type'),
            'right_label' => $this->input->post('right_label'),
            'left_label' => $this->input->post('left_label'),
            'confetti' => $this->input->post('confetti'),
            'date_updated' => date('Y-m-d H:i:s'),
        ];
        //options is an array, check if it is set
        if($this->input->post('options')){
            $data['options'] = flexformPerfectSerialize($this->input->post('options'));
        }
        if($image_path){
            $data['images'] = $image_path;
        }
        $this->flexformblocks_model->update($data, $block_id);
        //let us unpublish the form
        $this->flexform_model->update(['published' => '0'], $block['form_id']);
        //update the middle content
        $block = flexform_arrange_block($this->flexformblocks_model->get(['id'=>$block_id]));
        $middle_content = flexform_get_display_partial($block);
        echo json_encode(['publish_status'=>flexform_html_status('0'),'status' => 'success', 'middle_content'=>$middle_content,'message' => _flexform_lang('block_updated_successfully')]);
    }

    public function update_logic(){
        $this->load->model('flexformblocks_model');
        $block_id = $this->input->post('block_id');
        $block = $this->flexformblocks_model->get(['id' => $block_id]);
        if(!$block){
            set_alert('warning', _flexform_lang('block_not_found'));
            redirect(admin_url('flexform'));
        }
        //delete all existing logic
        $this->load->model('flexformblockslogic_model');
        $this->flexformblockslogic_model->delete_all($block_id);
        //get all index and loop through them
        $indexes = $this->input->post('index');
        foreach ($indexes as $index){
            $if_block = $this->input->post('logic_'.$index.'_if_block'); //question
            $if_operator = $this->input->post('logic_'.$index.'_if_operator');
            $if_value = $this->input->post('logic_'.$index.'_if_value');
            $goto = $this->input->post('logic_'.$index.'_goto');
            $next_condition = $this->input->post('logic_'.$index.'_next_condition');
            //delete the first element of the next condition because it is always 'and' and hidden on the form
            array_shift($next_condition);
            $other_cases_goto = $this->input->post('other_cases_goto');
            $data = [
                'block_id' => $block_id,
                'if_block_id' => flexformPerfectSerialize($if_block),
                'if_operator' => flexformPerfectSerialize($if_operator),
                'if_value' => flexformPerfectSerialize($if_value),
                'goto' => $goto,
                'next_condition' => flexformPerfectSerialize($next_condition),
                'other_cases_goto' => $other_cases_goto,
            ];
            $this->flexformblockslogic_model->add($data);
        }
        //lets unpublish the form
        $this->flexform_model->update(['published' => '0'], $block['form_id']);
        $form = $this->flexform_model->get(['id' => $block['form_id']]);
        set_alert('success', $block['title']._flexform_lang('logic_updated_successfully'));
        redirect(admin_url('flexform/setup/'.$form['slug']));
    }

    public function publish()
    {
        $this->load->model('flexformblocks_model');
        $form_id = $this->input->post('form_id');
        $form = $this->flexform_model->get(['id' => $form_id]);
        if (!$form) {
            set_alert('warning', _flexform_lang('form_not_found'));
            redirect(admin_url('flexform'));
        }
        //get all the form blocks
        $all_blocks = flexform_get_all_blocks($form_id);
        //a valid form must have at least one block that is not a statement block or thank you block
        $valid_blocks = 0;
        $have_thank_you = false;
        foreach ($all_blocks as $block){
            if($block['block_type'] != 'statement' && $block['block_type'] != 'thank-you'){
                $valid_blocks++;
            }else{
               if($block['block_type'] == 'thank-you'){
                   $have_thank_you = true;
               }
            }
        }
        if($valid_blocks == 0){
            set_alert('warning', _flexform_lang('form_must_have_at_least_one_question'));
            redirect(admin_url('flexform/setup/'.$form['slug']));
        }
        //form must also have a thank you block
        if(!$have_thank_you){
            set_alert('warning', _flexform_lang('form_must_have_thank_you_block'));
            redirect(admin_url('flexform/setup/'.$form['slug']));
        }
        //check if block and columns are submitted
        if(flexform_requires_mapping($form)){
            $columns = $this->input->post('column');
            $blocks = $this->input->post('block');
            if($blocks){
                foreach ($blocks as $key=>$block_id){
                    if($block_id){
                        $column = $columns[$key];
                        //update the block with the column
                        $this->flexformblocks_model->update(['map_to_column' => $column], $block_id);
                    }
                }
            }
        }
        $data = [
            'published' => '1',
            'date_updated' => date('Y-m-d H:i:s'),
        ];
        $this->flexform_model->update($data, $form_id);
        set_alert('success', _flexform_lang('form_published_successfully'));
        redirect(admin_url('flexform/setup/'.$form['slug']));
    }

    public function responses($slug = ''){
        if(!has_permission(FLEXFORM_MODULE_NAME, '', 'view')){
            set_alert('warning', _flexform_lang('permission_denied'));
            redirect(admin_url(''));
            return;
        }
        $form = $this->flexform_model->get_by_slug_or_id($slug);
        if (!$form) {
            set_alert('warning', _flexform_lang('form_not_found'));
            redirect(admin_url('flexform'));
        }
        $data['title'] = _flexform_lang('submissions-for').' '.$form['name'];
        $this->load->model('flexformcompleted_model');
        $this->load->model('flexformblockanswer_model');
        $data['form'] = $form;
        $active_tab = $this->input->get('tab') ?  $this->input->get('tab') : 'complete';
        //all form blocks
        $all_form_blocks = flexform_get_all_blocks($form['id']);
        //group answers by session_id
        $completed_group_by_session =  $this->flexformblockanswer_model->get_completed_group_by_session($form['id']);
        $partial_group_by_session = $this->flexformblockanswer_model->get_partial_group_by_session($form['id']);
        $responses = [];
        //count for tab
        $data['completed_count'] =  count($completed_group_by_session);
        $data['partial_count'] = count($partial_group_by_session);

       //let us get the right repsonses to display based on the tab
        $responses_based_on_session = ($active_tab == 'complete') ? $completed_group_by_session : $partial_group_by_session;
        foreach ($responses_based_on_session as $response_session){
            $session_id = $response_session['session_id'];
            $responses = array_merge($responses,$this->flexform_get_response($form['id'], $session_id,$active_tab));
        }
        $data['form_blocks'] = $all_form_blocks;
        $data['active_tab'] = $active_tab;
        $data['responses'] = $responses;
        //load js
        $this->app_scripts->add('flexform-pdf-js', module_dir_url('flexform', 'assets/js/html2pdf.bundle.min.js'), 'admin', ['app-js']);
        $this->app_scripts->add('flexform-js', module_dir_url('flexform', 'assets/js/flexform.js'), 'admin', ['app-js']);
        $this->load->view('responses/index', $data);
    }

    private function flexform_get_response($form_id, $session_id,$active_tab = 'complete'){
        return flexform_get_response($form_id, $session_id,$active_tab);
    }

    private function cleanup_files($form_id, $session_id = null){
        $responses = $this->flexform_get_response($form_id, $session_id);
        //loop through and check if the block is file
        if($session_id){
            foreach ($responses[$session_id] as $response){
                $block = $response['block'];
                $answer = $response['answer'];
                if($answer && $block['block_type'] == 'file'){
                    foreach ($answer as $file){
                        $file_path = FLEXFORM_FOLDER . $file;
                        if (file_exists($file_path)) {
                            unlink($file_path);
                        }
                    }
                }
            }
        }
    }

    public function duplicate($slug){
        $this->load->model('flexformblocks_model');
        if(!has_permission(FLEXFORM_MODULE_NAME, '', 'create')){
            set_alert('warning', _flexform_lang('permission_denied'));
            redirect(admin_url(''));
            return;
        }
        $form = $this->flexform_model->get_by_slug_or_id($slug);
        if(!$form){
            set_alert('warning', _flexform_lang('form_not_found'));
            redirect(admin_url('flexform'));
        }
        $new_form = $form;
        unset($new_form['id']);
        $new_form['slug'] = md5($form['name'].'-'.'-'.uniqid());
        $new_form['name'] = $form['name'].' - '._flexform_lang('copy');
        $new_form['published'] = '0';
        $new_form['staffid'] = get_staff_user_id();
        $new_form['date_added'] = date('Y-m-d H:i:s');
        $new_form['date_updated'] = date('Y-m-d H:i:s');
        $new_form_id = $this->flexform_model->add($new_form);
        $blocks_comparison = [];
        if($new_form_id){
            //get all blocks
            $blocks = $this->flexformblocks_model->all(['form_id' => $form['id']]);
            foreach ($blocks as $block){
                $new_block = $block;
                unset($new_block['id']);
                $new_block['form_id'] = $new_form_id;
                $new_block['date_added'] = date('Y-m-d H:i:s');
                $new_block['date_updated'] = date('Y-m-d H:i:s');
                //echo '<pre>',print_r($new_block),'</pre>';die();
                $new_block_id = $this->flexformblocks_model->add($new_block);
                if($new_block_id){
                    $blocks_comparison[$block['id']] = $new_block_id;
                    //check if the block have logic, if yes, duplicate it
                }
            }
            //duplicate logic
            //loop through the comparision array and duplicate the logic
            $this->load->model('flexformblockslogic_model');
            foreach ($blocks_comparison as $old_block_id => $new_block_id){
                $logic = $this->flexformblockslogic_model->all(['block_id' => $old_block_id]);
                //check if the block have logic
                foreach ($logic as $logic_item){
                    $new_logic = $logic_item;
                    unset($new_logic['id']);
                    $new_logic['block_id'] = $new_block_id;
                    //get the goto field
                    $new_logic['goto'] = $blocks_comparison[$logic_item['goto']];
                    $new_logic['other_cases_goto'] = $blocks_comparison[$logic_item['other_cases_goto']];
                    $new_logic['date_added'] = date('Y-m-d H:i:s');
                    $new_logic['date_updated'] = date('Y-m-d H:i:s');
                    $this->flexformblockslogic_model->add($new_logic);
                }
            }
            set_alert('success', _flexform_lang('form_duplicated_successfully'));
            redirect(admin_url('flexform/setup/'.$new_form['slug']));
        }
        set_alert('warning', _flexform_lang('error_occurred'));
        redirect(admin_url('flexform'));
    }

    public function delete($id){
        if(!has_permission(FLEXFORM_MODULE_NAME, '', 'delete')){
            set_alert('warning', _flexform_lang('permission_denied'));
            redirect(admin_url(''));
            return;
        }
        $form = $this->flexform_model->get_by_slug_or_id($id);
        if(!$form){
            set_alert('warning', _flexform_lang('form_not_found'));
            redirect(admin_url('flexform'));
        }
        $this->flexform_model->delete($form['id']);
        set_alert('success', _flexform_lang('form_deleted_successfully'));
        redirect(admin_url('flexform'));
    }


}
