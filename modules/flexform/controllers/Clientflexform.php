<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Clientflexform extends ClientsController
{
    private $navigation_history = [];

    public function vf($slug = '')
    {
        $this->load->model('flexform_model');
        $form = $this->flexform_model->get_by_slug_or_id($slug);
        //check if form exists
        if (!$form) {
            //return 404
            show_404();
        }

        $data['form'] = $form;
        $view_shown = false;
        //check if form is published
        if ($form['published'] == 0) {
            $this->view('client/msg/403');
            $view_shown = true;
        }

        //can view for function
        if(!flexform_can_view_form($form)){
            $this->view('client/msg/403');
            $view_shown = true;
        }

        //if the privacy  is customers or staff, chek if the loogged user has alresdy submitted the form
        if($form['privacy'] == 'customers' || $form['privacy'] == 'staff'){
            $user_id = ($form['privacy'] == 'customers') ? get_client_user_id() : get_staff_user_id();
            $completed = flexform_has_user_completed_form($form['id'], $form['privacy'], $user_id);
            if($completed){
                $this->view('client/msg/completed');
                $view_shown = true;
            }
        }
        //check if form is still allowing responses
        $end_date = $form['end_date'];
        if($end_date == "" || $end_date == null || $end_date == '0000-00-00 00:00:00'){
            //it  is an unlimited form, we will not check the end date
        }else{
            if (!$view_shown && $end_date < date('Y-m-d H:i:s')) {
                $this->view('client/msg/no-more-response');
                $view_shown = true;
            }
        }
        $all_blocks = flexform_get_all_blocks($form['id']);
        $data['all_blocks'] = $all_blocks;
        //get the first block
        if (!flexform_is_form_single_page($form)) {
            $first_block = ($all_blocks) ? $all_blocks[0] : [];
            $data['block'] = ($first_block) ? flexform_arrange_block($first_block) : [];
        }
        $data['bodyclass'] = 'flexform_client'; //add body class

        //session tracking
        //we will now create a new session so that we can track the form submission along with each block value
        $session_name = 'flexform_' . $form['slug'];
        $session_value = $this->session->userdata($session_name);
        //if it is a single page form, new each reload will be a new session
        if (flexform_is_form_single_page($form)) {
            $session_value = md5(time() . $form['slug']);
        }
        //check if session already exists
        if (!$session_value) {
            $session_value = md5(time() . $form['slug']);
            //$this->session->set_userdata($session_name, $session_value);
        }
        //clear the block navigation history, we are starting a new form
        //$this->clear_block_navigation_history($session_value);
        //add the first block to the navigation history
        //$this->update_block_navigation_history($first_block['id'], $session_value);
        //save the session value and name and pass it to the view
        $data['session_name'] = $session_name;
        $data['session_value'] = $session_value;
        //navigation history will be managed by the client now
        $this->data($data);
        //add body class
        //if the privacy is customers, we don't need to disable navigation
        $this->disableNavigation()
            ->disableSubMenu();
        $this->disableFooter();
        $this->title($form['name']);
        no_index_customers_area();

        if (!$view_shown) {
            //check if the form is a single page form
            if (flexform_is_form_single_page($form)) {
                //we will show the form now
                $this->view('client/single-page');
            } else {
                //we will show the form now
                $this->view('client/index');
            }
        }
        //add css
        $this->app_css->theme('flexform-css', module_dir_url('flexform', 'assets/css/flexform.css'));
        //add js
        $this->app_scripts->theme('signature-pad', 'assets/plugins/signature-pad/signature_pad.min.js');
        $this->app_scripts->theme('dropzone', 'assets/plugins/dropzone/min/dropzone.min.js');
        $this->app_scripts->theme('flexform-js', module_dir_url('flexform', 'assets/js/flexform-client.js'));
        $this->layout();
    }

    public function pending(){
        $this->load->model('flexform/flexform_model');
        $data['forms'] = flexform_get_all_active_forms('customers');
        $this->title(_l('flexform_my_forms'));
        $this->data($data);
        $this->view('client/pending');
        $this->layout();
    }

    /*private function clear_block_navigation_history($form_session)
    {
        //$this->session->unset_userdata('bnh_' . $form_session);
    }*/

    /**
     * Skip Questions Route
     */
    public function spa($slug)
    {
        if (!$this->input->is_ajax_request()) {
            redirect(site_url('flexform/vf/' . $slug));
        }
        //load the model
        $this->load->model('flexform_model');
        $this->load->model('flexformblockanswer_model');
        $form = $this->flexform_model->get_by_slug_or_id($slug);
        if (!$form) {
            echo json_encode(['status' => 'error', 'message' => _flexform_lang('form-not-found')]);
            exit();
        }
        //$skpped questions will be saved as empty
        $skipped_questions = [];

        //get the Block id
        $block_id = $this->input->post('id'); //current block id
        $submitted_answer = $this->input->post('value'); //answer provided for this block

        //based on the value, get the next block
        $all_blocks = flexform_get_all_blocks($form['id']);
        $current_block = flexform_get_block($block_id);
        $next_block = $this->get_next_block($all_blocks, $current_block, '', $submitted_answer);
        if (!$next_block) {
            echo json_encode(['status' => 'error', 'message' => _flexform_lang('no-more-questions')]);
            exit();
        }
        //we will check if the next block is a thank you block
        //if it is a thank you block, we expect the current question to be the last question
        //if it is not, we will add all the remaining questions to the skipped questions
        if (flexform_is_thank_you_block($next_block)) {
            //check if the current question is the last question
            $current_block_index = $this->get_block_index($all_blocks, $current_block['id']);
            $next_block_index = $current_block_index + 1;
            $remaining_blocks = array_slice($all_blocks, $next_block_index);
            foreach ($remaining_blocks as $block) {
                $skipped_questions[] = $block['id'];
            }
            if ($skipped_questions) {
                //it means the current block is not the last block, we will return a response to show the skipped questions
                echo json_encode(['status' => 'success', 'skipped_blocks' => $skipped_questions]);
                exit();
            }
        } else {
            //if it is not a thank-you block,
            //check if the next block is supposed to be the next block or another block is inbetween that should be added to the skipped questions
            $next_block_index = $this->get_block_index($all_blocks, $next_block['id']);
            //get the current block index
            $current_block_index = $this->get_block_index($all_blocks, $current_block['id']);
            //is there any block inbetween the current block and the next block
            $inbetween_blocks = array_slice($all_blocks, $current_block_index + 1, $next_block_index - $current_block_index - 1);
            if ($inbetween_blocks) {
                foreach ($inbetween_blocks as $block) {
                    $skipped_questions[] = $block['id'];
                }
                if ($skipped_questions) {
                    //it means the current block is not the last block, we will return a response to show the skipped questions
                    echo json_encode(['status' => 'success', 'skipped_blocks' => $skipped_questions]);
                    exit();
                }
            }
        }

        //if we don't have a return at this point, it means the next block is the next block
        //just return success
        echo json_encode(['status' => 'success']);
        exit();
    }

    private function get_next_block($blocks, $current_block, $form_session, $block_answer_provided = ''): array
    {
        //we need to get the block logics if this block has one
        if ($block_logics = flexform_get_block_logics($current_block['id'])) {
            $other_cases_goto = 0;
            foreach ($block_logics as $block_logic) {
                $if_block_id = flexformPerfectUnserialize($block_logic['if_block_id']);
                $if_operator = flexformPerfectUnserialize($block_logic['if_operator']);
                $if_value = flexformPerfectUnserialize($block_logic['if_value']);
                $next_condition = flexformPerfectUnserialize($block_logic['next_condition']);
                $goto = $block_logic['goto']; //block id to go to if condition is met
                $other_cases_goto = $block_logic['other_cases_goto']; //block id to go to if none of the conditions are met
                $index = 0;
                $condition_met_arr = []; //this is the result of the condition met
                foreach ($if_block_id as $key => $block_id) {
                    $condition_met_arr[$key] = 0;
                    $block = flexform_get_block($block_id);
                    //if block is not found, we will skip this block
                    if (!$block) {
                        continue;
                    }
                    $block_type = $block['block_type'];
                    $block_operator = $if_operator[$key];
                    $block_value = $if_value[$key];
                    //get the answer provided for this block
                    if ($block_answer_provided) {
                        $submitted_answer = $block_answer_provided;
                    } else {
                        $block_answer = $this->flexformblockanswer_model->get(['block_id' => $block_id, 'session_id' => $form_session]);
                        $submitted_answer = ($block_answer) ? flexformPerfectUnserialize($block_answer['answers']) : '';
                    }
                    //let us check if the condtion set in the logic is met
                    switch ($block_operator) {
                        case 'is':
                            //what is submitted answer is an array, we will check if the block value is in the array
                            if (is_array($submitted_answer)) {
                                if (in_array($block_value, $submitted_answer)) {
                                    $condition_met_arr[$key] = 1;
                                }
                            } else {
                                if ($submitted_answer == $block_value) {
                                    $condition_met_arr[$key] = 1;
                                }
                            }
                            break;

                        case 'is-not':
                            //what is submitted answer is an array, we will check if the block value is in the array
                            if (is_array($submitted_answer)) {
                                if (!in_array($block_value, $submitted_answer)) {
                                    $condition_met_arr[$key] = 1;
                                }
                            } else {
                                if ($submitted_answer != $block_value) {
                                    $condition_met_arr[$key] = 1;
                                }
                            }
                            break;
                        case 'contains':
                            if (strpos($submitted_answer, $block_value) !== false) {
                                $condition_met_arr[$key] = 1;
                            }
                            break;
                        case 'does-not-contain':
                            if (strpos($submitted_answer, $block_value) === false) {
                                $condition_met_arr[$key] = 1;
                            }
                            break;
                        case 'starts-with':
                            if (strpos($submitted_answer, $block_value) === 0) {
                                $condition_met_arr[$key] = 1;
                            }
                            break;
                        case 'ends-with':
                            if (substr($submitted_answer, -strlen($block_value)) === $block_value) {
                                $condition_met_arr[$key] = 1;
                            }
                            break;

                        case 'is-greater-than':
                            if ($submitted_answer > $block_value) {
                                $condition_met_arr[$key] = 1;
                            }
                            break;

                        case 'is-less-than':
                            if ($submitted_answer < $block_value) {
                                $condition_met_arr[$key] = 1;
                            }
                            break;
                        case 'is-before':
                            //this is likely a data or time, I am supposed to show a date time picker rather than just a text input
                            $boolean_datetime = ($block_type == 'datetime');
                            $block_value = to_sql_date($block_value, $boolean_datetime);
                            if (strtotime($submitted_answer) < strtotime($block_value)) {
                                $condition_met_arr[$key] = 1;
                            }
                            break;
                        case 'is-after':
                            $boolean_datetime = ($block_type == 'datetime');
                            $block_value = to_sql_date($block_value, $boolean_datetime);
                            if (strtotime($submitted_answer) > strtotime($block_value)) {
                                $condition_met_arr[$key] = 1;
                            }
                    }
                    $index++;
                }
                $condition_met_arr = array_values($condition_met_arr);
                //print_r($condition_met_arr);die();
                //check if the condition is met
                //using the next condition, joined the condition met array to get the result
                //if the result is true, then the condition is met
                $final_result = $this->evaluateBooleansWithOperators($condition_met_arr, $next_condition);
                if ($final_result) {
                    $next_block = flexform_arrange_block(flexform_get_block($goto));
                    if (!$next_block) continue;
                    return $next_block;
                }
            }
            //if none of the conditions are met, we will go to the other_cases_goto block
            if ($other_cases_goto) {
                $next_block = flexform_arrange_block(flexform_get_block($other_cases_goto));
                if ($next_block) return $next_block;
            }
        }
        foreach ($blocks as $key => $block) {
            if ($block['id'] == $current_block['id']) {
                if (isset($blocks[$key + 1])) {
                    return flexform_arrange_block($blocks[$key + 1]);
                }
            }
        }
        return [];
    }

    private function evaluateBooleansWithOperators($booleans, $operators)
    {
        if (count($booleans) === 0) {
            return false; // If there are no booleans, return false
        }
        // Initialize the result with the first boolean value
        $result = $booleans[0];

        // Iterate through the operators and corresponding boolean values
        for ($i = 0; $i < count($operators); $i++) {
            $operator = strtolower($operators[$i]);
            $nextValue = $booleans[$i + 1];

            if ($operator == "and") {
                $result = $result && $nextValue;
            } elseif ($operator == "or") {
                $result = $result || $nextValue;
            } else {
                throw new Exception("Invalid operator: $operator");
            }
        }

        return $result;
    }

    private function get_block_index($blocks, $block_id)
    {
        $block_index = 0;
        foreach ($blocks as $key => $each_block) {
            if ($each_block['id'] == $block_id) {
                $block_index = $key;
            }
        }
        return $block_index;
    }

    /**
     * Single page form submission
     * @param $slug
     * @return void
     */
    public function sp_submit($slug)
    {
        //single page form submission
        if (!$this->input->is_ajax_request()) {
            redirect(site_url('flexform/vf/' . $slug));
        }
        //load the model
        $this->load->model('flexform_model');
        $this->load->model('flexformblockanswer_model');
        $form = $this->flexform_model->get_by_slug_or_id($slug);
        if (!$form) {
            echo json_encode(['status' => 'error', 'message' => _flexform_lang('form-not-found')]);
            exit();
        }
        //
        if ($form['require_terms_and_conditions'] == 1) {
            $terms_and_conditions = $this->input->post('terms_and_conditions');
            if (!$terms_and_conditions) {
                echo json_encode(['status' => 'info', 'message' => _flexform_lang('please-accept-terms-and-conditions-required')]);
                exit();
            }
        }

        //check recaptcha
        if (show_recaptcha() && $form['enable_captcha'] == 1) {
            if (!do_recaptcha_validation($this->input->post('g-recaptcha-response'))) {
                echo json_encode(['status' => 'info', 'message' => _flexform_lang('recaptcha-verification-failed')]);
                exit();
            }
        }
        //get the submitted values and save them
        //loop through, the submitted answers
        $blocks_list_gone_through = [];
        $blocks = flexform_get_all_blocks($form['id']);
        $last_provided_answer = ''; //this will be used to check if the last answer provided changes the next block or not
        $last_block = [];
        $form_session = $this->input->post('svalue');
        foreach ($blocks as $b) {

            //the current block might change depending on the last provided answer and the last block id
            if ($last_provided_answer && $last_block) {
                //block based on logic
                $current_block = $this->get_next_block($blocks, $last_block, $this->input->post('svalue'), $last_provided_answer);
                $block_id = $current_block['id'];
            }else{
                //blocks from the pool of blocks
                $block_id = $b['id'];
                $current_block = flexform_get_block($block_id);
            }
            if (!$current_block) {
                echo json_encode(['status' => 'error', 'message' => _flexform_lang('block-not-found')]);
                exit();
            }
            //it is possible for the blockBasedOnLogic to be the same with BlocksFromThePool
            //because we are only updating the last provided answer and last block id if it is a text based input where logic is applicable
            //so if the last block is a text based, no logic is applicable to that TextInput and the next block happens to be a file or singature
            //that means we would have reset the last provided answer and last block id to empty
            //the next block will be the BlockFromThePool of blocks because that provided answer is empty,
            // that Block will be the same as the Last BlockBasedOnLogic
            //so we will skip the block
            if (in_array($block_id, $blocks_list_gone_through)) {
                continue;
            }
            $blocks_list_gone_through[] = $block_id;
            //if it is a statement block or thank you block, we will skip it
            if (flexform_is_statement_block($current_block)) {
                $last_provided_answer = '';
                continue;
            }
            if(flexform_is_thank_you_block($current_block)){
                //if it is a thank you block, we will show the thank you message
                //if the submit button was clicked, we will show the thank you block
                //we will record a new response now
                $this->load->model('flexformcompleted_model');
                $this->flexformcompleted_model->add([
                    'form_id' => $form['id'],
                    'session_id' => $form_session,
                    'date_added' => date('Y-m-d H:i:s'),
                    'customerid' => get_client_user_id() ? get_client_user_id() : 0,
                    'staffid' => get_staff_user_id() ? get_staff_user_id() : 0,
                ]);
                //update the all block answrs to completed
                $this->flexformblockanswer_model->update(['completed' => '1'], ['form_id' => $form['id'], 'session_id' => $form_session]);

                try {
                    $this->post_submit_actions($form['id'], $form_session);
                } catch (Exception $e) {
                }
                //show the thank you block
                $html = flexform_get_display_partial($current_block, false, false); //block view
                echo json_encode([
                    'status' => 'success',
                    'html' => $html,
                ]);
                exit();
            }
            $value_required = $current_block['required'];
            //save the current block values
            $block_type = $current_block['block_type'];
            $block_answers = '';
            //if the block_type is a file, we will save the file and save the file path
            if ($block_type == 'file') {
                $files_result = $this->spa_upload_file($current_block);
                //print_r($files_result);die();
                $file_upload_status = $files_result['success'];
                $files = $files_result['files'];
                //check if it is another error
                if (!$file_upload_status) {
                    $msg = (isset($files_result['message'])) ? $files_result['message'] : _flexform_lang('file-upload-error');
                    echo json_encode(['status' => 'info', 'message' => $msg]);
                    exit();
                }
                //check if file is required
                if (!$files && $value_required) {
                    echo json_encode(['status' => 'info', 'message' => _flexform_lang('file-required')]);
                    exit();
                }
                $block_answers = flexformPerfectSerialize($files);
                //reset the last provided answer and last block id to empty because they don't support logic
                $last_provided_answer = '';
                $last_block = [];
            } elseif ($block_type == 'signature') {
                //check if signature is provided
                $base64_string = $this->input->post('signature' . $block_id);
                if ($base64_string) {
                    $result = $this->process_digital_signature_image($base64_string, $block_id);
                    if ($result['status']) {
                        $block_answers = flexformPerfectSerialize($result['filename']);
                        //reset the last provided answer and last block id to empty because they don't support logic
                        $last_provided_answer = '';
                        $last_block = [];
                    }
                } else {
                    if ($value_required) {
                        echo json_encode(['status' => 'info', 'message' => _flexform_lang('signature-required')]);
                        exit();
                    }
                    $block_answers = '';
                }
            } else {
                //it is a text input e.g text, textarea, email, number, date, time
                //check if it is set
                // $block_answers = flexformPerfectSerialize($this->input->post('answer_'.$block_id));
                if (isset($_POST['answer_' . $block_id])) {
                    //check if it is required
                    $provided_answer = $this->input->post('answer_' . $block_id);
                    if (!$provided_answer && $value_required) {
                        echo json_encode(['status' => 'info', 'message' => _flexform_lang('spa-value-required')]);
                        exit();
                    }
                    //if it is a number, want to check if number is between the max and min set for the block
                    if ($current_block['block_type'] == 'number') {
                        $block_answers = $this->input->post('answer' . $block_id);
                        $max = $current_block['maximum_number'];
                        $min = $current_block['minimum_number'];
                        if ($max && $block_answers > $max) {
                            echo json_encode(['status' => 'info', 'message' => _flexform_lang('number-max-error') . ' ' . $max]);
                            exit();
                        }
                        if ($min && $block_answers < $min) {
                            echo json_encode(['status' => 'info', 'message' => _flexform_lang('number-min-error') . ' ' . $min]);
                            exit();
                        }
                    }
                    //if block_type is date or datetime, we want to convert to sql date format so strtotime can work
                    if ($current_block['block_type'] == 'date' || $current_block['block_type'] == 'datetime') {
                        $to_sql_date_boolean = ($current_block['block_type'] == 'datetime');
                        $provided_answer = to_sql_date($provided_answer, $to_sql_date_boolean);
                    }
                    //we only update the last provided answer and last block id if it is a text based input where logic is applicable
                    $last_provided_answer = $provided_answer;
                    $last_block = $current_block;
                    $block_answers = flexformPerfectSerialize($provided_answer);
                } else {
                    //if you are not set and it is required, we will show an error
                    if ($value_required) {
                        echo json_encode(['status' => 'info', 'message' => _flexform_lang('spa-value-required')]);
                        exit();
                    }
                }
            }
            $session_value = $form_session;
            //delete previous block answer
            $this->flexformblockanswer_model->delete(['block_id' => $block_id, 'form_id' => $form['id'], 'session_id' => $session_value]);
            $this->flexformblockanswer_model->add([
                'block_id' => $block_id,
                'form_id' => $form['id'],
                'session_id' => $session_value,
                'answers' => $block_answers,
                'block_title' => $current_block['title'],  //just in case the block is deleted
                'block_description' => $current_block['description'], //just in case the block is deleted
                'date_added' => date('Y-m-d H:i:s'),
                'date_updated' => date('Y-m-d H:i:s'),
                'customerid' => get_client_user_id() ? get_client_user_id() : 0,
                'staffid' => get_staff_user_id() ? get_staff_user_id() : 0,
            ]);
        }
        //if we get to this point, it means all the blocks have been submitted
        //we didn't get to the thank you block
        echo json_encode(['status' => 'success']);
        exit();

    }

    private function process_file_upload($block)
    {
        $file_result = [
            'files' => [],
            'success' => true
        ];
        $files = $this->input->post('files');
        $fileNames = $this->input->post('file_names');
        if (!$files) {
            return $file_result;
        }
        $allowed_file_types = ($block['file_types']) ? explode(',', $block['file_types']) : explode(',', get_option('allowed_files'));
        foreach ($files as $index => $base64String) {
            // Extract the MIME type and file extension
            $fileName = $fileNames[$index];

            // Remove the base64 header
            //$base64String = str_replace('data:' . $mimeType . ';base64,', '', $base64String);
            $fileData = base64_decode($base64String);

            // Extract the file extension
            $fileExtension = pathinfo($fileName, PATHINFO_EXTENSION);
            // Check if the file extension is allowed
            if (!in_array('.' . $fileExtension, $allowed_file_types) && !in_array($fileExtension, $allowed_file_types)) {
                $file_result['success'] = false;
                $file_result['message'] = _flexform_lang('file-extension-not-allowed');
                return $file_result;
            }

            //saveable file name
            $outputFileSaveableName = md5(time() . $fileName . $index) . '.' . $fileExtension;
            // Generate a safe file name
            $outputFileName = FLEXFORM_FOLDER . $outputFileSaveableName;

            // Save the file
            try {
                $retval = false;
                $fp = fopen($outputFileName, 'w+');
                if (fwrite($fp, $fileData)) {
                    $retval = true;
                }
                fclose($fp);
                if (!$retval) {
                    continue;
                }
                $file_result['files'][] = $outputFileSaveableName;
            } catch (Exception $e) {
                $file_result['success'] = false;
                break;
            }

        }
        return $file_result;
    }

    private function spa_upload_file($block): array
    {
        //we are uploading from the $_FILES
        $is_multiple = $block['allow_multiple'];
        $allowed_file_types = explode(',', $block['file_types']);
        $file_result = [
            'files' => [],
            'success' => true
        ];
        $files = $_FILES['file_' . $block['id']];

        //print_r($files);die();
        if (!$files) {
            return $file_result;
        }
        //check if file multiple or single is empty
        if ($is_multiple == 0) {
            if (empty($files['name'])) {
                return $file_result;
            }
        } else {
            if (empty($files['name'][0])) {
                return $file_result;
            }
        }
        $fileArray = $this->normalizeFilesArray($files);
        //if the file is multiple
        $i = 0;
        foreach($fileArray as $file){
           // Extract the file extension
            $fileExtension = pathinfo($file['name'], PATHINFO_EXTENSION);
            // Check if the file extension is allowed
            if (!in_array($fileExtension, $allowed_file_types)) {
                $file_result['success'] = false;
                $file_result['message'] = _flexform_lang('file-extension-not-allowed');
                //$file_result['message'] = $block['file_types']._flexform_lang('file-extension-not-allowed').$fileExtension;
                return $file_result;
            }
            //saveable file name
            $outputFileSaveableName = md5(time() . $file['name'] . $i) . '.' . $fileExtension;
            // Generate a safe file name
            $outputFileName = FLEXFORM_FOLDER . $outputFileSaveableName;
            // Save the file
            try {
                $retval = false;
                if (move_uploaded_file($file['tmp_name'], $outputFileName)) {
                    $retval = true;
                }
                if (!$retval) {
                    continue;
                }
                $file_result['files'][] = $outputFileSaveableName;
            } catch (Exception $e) {
                $file_result['success'] = false;
                break;
            }
            $i++;
        }
        return $file_result;
    }

    private function normalizeFilesArray($files): array
    {
        $normalized = [];

        if (is_array($files['name'])) {
            // Multiple files
            for ($i = 0; $i < count($files['name']); $i++) {
                $normalized[] = [
                    'name' => $files['name'][$i],
                    'type' => $files['type'][$i],
                    'tmp_name' => $files['tmp_name'][$i],
                    'error' => $files['error'][$i],
                    'size' => $files['size'][$i],
                ];
            }
        } else {
            // Single file
            $normalized[] = $files;
        }

        return $normalized;
    }

    private function process_digital_signature_image($partBase64, $block_id)
    {
        if (empty($partBase64)) {
            return false;
        }
        _maybe_create_upload_path(FLEXFORM_FOLDER);
        $filename = unique_filename(FLEXFORM_FOLDER, md5($block_id . time()) . 'signature.png');
        $retval = false;
        $path = rtrim(FLEXFORM_FOLDER, '/') . '/' . $filename;
        $fp = fopen($path, 'w+');
        if (fwrite($fp, base64_decode($partBase64))) {
            $retval = true;
        }
        fclose($fp);

        return [
            'status' => $retval,
            'filename' => $filename,
        ];
    }

    public function submit($slug)
    {
        //if request is not ajax, redirect to start of the form
        if (!$this->input->is_ajax_request()) {
            redirect(site_url('flexform/vf/' . $slug));
        }
        //load the model
        $this->load->model('flexform_model');
        $this->load->model('flexformblockanswer_model');
        $form = $this->flexform_model->get_by_slug_or_id($slug);
        if (!$form) {
            echo json_encode(['status' => 'error', 'message' => _flexform_lang('form-not-found')]);
            exit();
        }
        $block_id = flexformPerfectUnserialize($this->input->post('current'));
        $current_block = flexform_get_block($block_id);
        if (!$current_block) {
            echo json_encode(['status' => 'error', 'message' => _flexform_lang('block-not-found')]);
            exit();
        }
        //if this block is the first block
        //$navigation_history = $this->get_block_navigation_history($this->session->userdata('flexform_' . $form['slug']));
        $this->navigation_history = explode(',', $this->input->post('ff_bnh'));

        //if the navigation contains just one block, then it means this is the first block
        if (count($this->navigation_history) == 1) {
            //now let us check if terms and conditions is required and if it is checked
            if ($form['require_terms_and_conditions'] == 1) {
                $terms_and_conditions = $this->input->post('terms_and_conditions');
                if (!$terms_and_conditions) {
                    echo json_encode(['status' => 'info', 'message' => _flexform_lang('please-accept-terms-and-conditions-required')]);
                    exit();
                }
            }

            //check recaptcha
            if (show_recaptcha() && $form['enable_captcha'] == 1) {
                if (!do_recaptcha_validation($this->input->post('g-recaptcha-response'))) {
                    echo json_encode(['status' => 'info', 'message' => _flexform_lang('recaptcha-verification-failed')]);
                    exit();
                }
            }
        }
        $value_required = $current_block['required'];
        //$form_session = $this->session->userdata('flexform_' . $form['slug']);
        $form_session = $this->input->post('svalue');
        //save the current block values
        $block_type = $current_block['block_type'];
        $none_input_types = ['statement', 'thank-you'];
        $block_answers = '';
        if (!in_array($block_type, $none_input_types)) {
            //if the block_type is a file, we will save the file and save the file path
            if ($block_type == 'file') {
                //we added the file check since the system now supports file upload from the $_FILES array
                $files_result = (isset($_FILES['file_' . $block_id])) ? $this->spa_upload_file($current_block) : $this->process_file_upload($current_block);
                $file_upload_status = $files_result['success'];
                $files = $files_result['files'];
                //check if file is required
                if (!$files && $value_required) {
                    echo json_encode(['status' => 'info', 'message' => _flexform_lang('file-required')]);
                    exit();
                }
                //check if it is another error
                if (!$file_upload_status) {
                    $msg = (isset($files_result['message'])) ? $files_result['message'] : _flexform_lang('file-upload-error');
                    echo json_encode(['status' => 'info', 'message' => $msg]);
                    exit();
                }
                $block_answers = flexformPerfectSerialize($files);
            } elseif ($block_type == 'signature') {
                //check if signature is provided
                $base64_string = $this->input->post('signature' . $block_id);
                if ($base64_string) {
                    $result = $this->process_digital_signature_image($base64_string, $block_id);
                    if ($result['status']) {
                        $block_answers = flexformPerfectSerialize($result['filename']);
                    }
                } else {
                    if ($value_required) {
                        echo json_encode(['status' => 'info', 'message' => _flexform_lang('signature-required')]);
                        exit();
                    }
                    $block_answers = '';
                }
            } else {
                //it is a text input e.g text, textarea, email, number, date, time
                //check if it is set
                // $block_answers = flexformPerfectSerialize($this->input->post('answer_'.$block_id));
                if (isset($_POST['answer_' . $block_id])) {
                    //check if it is required
                    $provided_answer = $this->input->post('answer_' . $block_id);
                    if (!$provided_answer && $value_required) {
                        echo json_encode(['status' => 'info', 'message' => _flexform_lang('value-required')]);
                        exit();
                    }
                    //if it is a number, want to check if number is between the max and min set for the block
                    if ($current_block['block_type'] == 'number') {
                        $block_answers = $this->input->post('answer' . $block_id);
                        $max = $current_block['maximum_number'];
                        $min = $current_block['minimum_number'];
                        if ($max && $block_answers > $max) {
                            echo json_encode(['status' => 'info', 'message' => _flexform_lang('number-max-error') . ' ' . $max]);
                            exit();
                        }
                        if ($min && $block_answers < $min) {
                            echo json_encode(['status' => 'info', 'message' => _flexform_lang('number-min-error') . ' ' . $min]);
                            exit();
                        }
                    }
                    //if block_type is date or datetime, we want to convert to sql date format so strtotime can work
                    if ($current_block['block_type'] == 'date' || $current_block['block_type'] == 'datetime') {
                        $to_sql_date_boolean = ($current_block['block_type'] == 'datetime');
                        $provided_answer = to_sql_date($provided_answer, $to_sql_date_boolean);
                    }
                    $block_answers = flexformPerfectSerialize($provided_answer);
                } else {
                    //if you are not set and it is required, we will show an error
                    if ($value_required) {
                        echo json_encode(['status' => 'info', 'message' => _flexform_lang('value-required')]);
                        exit();
                    }
                }
            }
            $session_value = $form_session;
            //delete previous block answer
            $this->flexformblockanswer_model->delete(['block_id' => $block_id, 'form_id' => $form['id'], 'session_id' => $session_value]);
            $this->flexformblockanswer_model->add([
                'block_id' => $block_id,
                'form_id' => $form['id'],
                'session_id' => $session_value,
                'answers' => $block_answers,
                'block_title' => $current_block['title'],  //just in case the block is deleted
                'block_description' => $current_block['description'], //just in case the block is deleted
                'date_added' => date('Y-m-d H:i:s'),
                'date_updated' => date('Y-m-d H:i:s'),
                'customerid' => get_client_user_id() ? get_client_user_id() : 0,
                'staffid' => get_staff_user_id() ? get_staff_user_id() : 0,
            ]);
        }
        //let us get the next block
        $all_blocks = flexform_get_all_blocks($form['id']);
        $next_block = $this->get_next_block($all_blocks, $current_block, $form_session);
        if (!$next_block) {
            echo json_encode(['status' => 'error', 'message' => _flexform_lang('no-more-questions')]);
            exit();
        }
        //we will check if the next block is a thank you block or the last block so that we can show the submit button
        $is_submit = false;
        if ($next_block['block_type'] != 'thank-you') {
            //check if the next block after this next block is a thank you block or it is the last block
            $next_block_index = 0;
            foreach ($all_blocks as $key => $each_block) {
                if ($each_block['id'] == $next_block['id']) {
                    $next_block_index = $key;
                }
            }
            $next_block_after_next = [];
            if (isset($all_blocks[$next_block_index + 1])) {
                $next_block_after_next = $all_blocks[$next_block_index + 1];
            } else {
                //this is the last block, maybe the form doesn't have a thank you block
                $is_submit = true;
            }
            //check if the next block after the next block is a thank you block
            if ($next_block_after_next && $next_block_after_next['block_type'] == 'thank-you') {
                $is_submit = true;
            }
        } else {
            //if the next block is a thank you
            //ideally, we should show the submit button
            //we might be showing the thank you block conditionally based on the the provided answer
            //let us check if the  showing a Submit button, if not, we will show the submit button
            if (!$this->input->post('is_submit')) {
                //then it means the submit button was not clicked
                //lets show submit button
                $button_html = $this->load->view('setup/display/partials/submit-button', ['block' => $current_block, 'is_submit' => true], true);
                echo json_encode(['status' => 'success', 'is_submit' => true, 'html' => $button_html]);
                exit();
            } else {
                //if the submit button was clicked, we will show the thank you block
                //we will record a new response now
                $this->load->model('flexformcompleted_model');
                $this->flexformcompleted_model->add([
                    'form_id' => $form['id'],
                    'session_id' => $form_session,
                    'date_added' => date('Y-m-d H:i:s'),
                    'customerid' => get_client_user_id() ? get_client_user_id() : 0,
                    'staffid' => get_staff_user_id() ? get_staff_user_id() : 0,
                ]);
                //update the all block answrs to completed
                $this->flexformblockanswer_model->update(['completed' => '1'], ['form_id' => $form['id'], 'session_id' => $form_session]);
                //let us sync the form if it is connected to a table
                try {
                    $this->post_submit_actions($form['id'], $form_session);
                } catch (Exception $e) {
                }
                //let us delete the session now
                //$this->session->unset_userdata('flexform_' . $form['slug']);
                //$this->clear_block_navigation_history($form_session);
            }
        }
        //keep track of block navigation
        $updated_nav_history = $this->update_block_navigation_history($next_block['id'], $form_session);
        //get the navigation logs
        $nav_logs = $this->get_block_navigation_history($form_session);
        $nav_button_html = $this->load->view('client/footer-nav', ['form' => $form, 'showing_block' => $next_block, 'nav_logs' => $nav_logs], true);
        $html = flexform_get_display_partial($next_block, $is_submit, false); //block view
        $block_limit = $this->upload_limit($next_block);
        $percentage_completed = ($next_block['block_type'] == 'thank-you') ? 100 : $this->get_percentage_completed($all_blocks, $next_block);
        echo json_encode([
            'status' => 'success',
            'upload_limit' => $block_limit,
            'html' => $html,
            'current_percentage_completed' => $percentage_completed,
            'nav_footer_link' => $nav_button_html,
            'nav_logs' => implode(',', $updated_nav_history)
        ]);
    }

    private function post_submit_actions($form_id, $session_id): void
    {

        $this->load->model('flexform_model');
        $form = $this->flexform_model->get_by_slug_or_id($form_id);
        if (!$form) {
            return;
        }
        $form_name = $form['name'];
        $responsible = $form['responsible'];
        $notify_lead_imported = $form['notify_form_submission'];
        $notify_type = $form['notify_type'];
        $notify_ids = $form['notify_ids'];
        //Sync to table if the form is connected to a table
        $table_id = $this->sync_to_belong_table($form, $session_id);
        //send form notification
        if ($notify_lead_imported != 0) {
            $staff = [];
            if ($notify_type != 'assigned') {
                $ids = @unserialize($notify_ids);

                if (is_array($ids) && count($ids) > 0) {
                    $this->db->where('active', 1)
                        ->where_in($notify_type == 'specific_staff' ? 'staffid' : 'role', $ids);
                    $staff = $this->db->get(db_prefix() . 'staff')->result_array();
                }
            } elseif ($responsible) {
                $staff = [
                    [
                        'staffid' => $responsible,
                    ],
                ];
            }

            $notifiedUsers = [];
            foreach ($staff as $member) {

                $link = site_url('flexform/responses/' . $form['slug']);
                if (add_notification([
                    'description' => _flexform_lang('you_received_a_new_form_submission_in') . ' ' . $form_name,
                    'touserid' => $member['staffid'],
                    'fromcompany' => 1,
                    'fromuserid' => 0,
                    'additional_data' => serialize([
                        $form_name,
                    ]),
                    'link' => $link,
                ])) {
                    array_push($notifiedUsers, $member['staffid']);
                }
            }
            pusher_trigger_notification($notifiedUsers);
        }

        //email notification of form submission data
        $emails_to_notify = $form['data_submission_notification_emails'];
        if($emails_to_notify){
            $emails_to_notification_arr = explode(',', $emails_to_notify);
            $CI = &get_instance();
            $CI->load->library(FLEXFORM_MODULE_NAME.'/Flexform_module');
            try{
                $CI->flexform_module->send_form_data_email($emails_to_notification_arr, $form_id, $session_id);
            }  catch (Exception $e) {
                }
        }
    }

    private function sync_to_belong_table($form, $session_id)
    {
        $form_id = $form['id'];
        $form_name = $form['name'];
        $responsible = $form['responsible'];
        $table_id = 0;
        if (flexform_requires_mapping($form)) {
            //the form is connected to a table
            //get all the form blocks
            $form_blocks = flexform_get_all_blocks($form_id);
            //loop through these blocks and get the answers
            $form_data = [];
            $form_attachments = [];
            foreach ($form_blocks as $block) {
                $block_answers = $this->flexformblockanswer_model->get(['block_id' => $block['id'], 'session_id' => $session_id]);
                $answer = ($block_answers) ? flexformPerfectUnserialize($block_answers['answers']) : '';
                //check if the block is mapped to a column
                if ($column_mapped = $block['map_to_column']) {
                    $form_data[$column_mapped] = $answer;
                }
                if($block['block_type'] == 'file' && $answer){
                    $form_attachments[] = $answer;
                }
            }
            //if the form data is empty, no field is mapped, we will not proceed
            if (!$form_data) {
                return 0;
            }
            $form_type = $form['type'];
            switch ($form_type) {
                case 'tickets':
                    $post_data = [
                        'email' => $form_data['email'],
                        'name' => $form_data['name'],
                        'subject' => $form_data['subject'],
                        'department' => $form_data['department'],
                        'priority' => $form_data['priority'],
                        'service' => isset($form_data['service']) && is_numeric($form_data['service'])
                            ? $form_data['service']
                            : null,
                        'custom_fields' => isset($form_data['custom_fields']) && is_array($form_data['custom_fields'])
                            ? $form_data['custom_fields']
                            : [],
                        'message' => $form_data['message'],
                    ];

                    if ($responsible) {
                        $post_data['assigned'] = $responsible;
                    }
                    $success = false;

                    $this->db->where('email', $post_data['email']);
                    $result = $this->db->get(db_prefix() . 'contacts')->row();

                    if ($result) {
                        $post_data['userid'] = $result->userid;
                        $post_data['contactid'] = $result->id;
                        unset($post_data['email']);
                        unset($post_data['name']);
                    }

                    $this->load->model('tickets_model');

                    $post_data = hooks()->apply_filters('ticket_external_form_insert_data', $post_data);
                    $ticket_id = $this->tickets_model->add($post_data);

                    if ($ticket_id) {
                        $success = true;
                        //add ticket attachments
                        $uploaded_files = [];
                        if (isset($form_attachments) && is_array($form_attachments)) {
                            //we have attached files to this form
                            //we want to copy these files to the ticket attachments
                            $path = get_upload_path_by_type('ticket') . $ticket_id . '/';
                            _maybe_create_upload_path($path);
                            //let us loop through the uploaded files and copy them to the ticket attachments path
                            foreach ($form_attachments as $attachment) {
                                //each attachment can contain multiple files, attachment is an array
                                foreach ($attachment as $file) {
                                    $file_path = FLEXFORM_FOLDER . $file;
                                    $new_file_name = md5(time() . $file) . '.' . pathinfo($file, PATHINFO_EXTENSION);
                                    $new_file_path = $path . $new_file_name;
                                if (copy($file_path, $new_file_path)) {
                                    array_push($uploaded_files, [
                                        'file_name' => $new_file_name,
                                        'filetype' => $this->getMimeType($new_file_path),
                                        ]);
                                    }
                                }
                            }
                        }
                        //add ticket attachments
                        if ($uploaded_files) {
                            $this->tickets_model->insert_ticket_attachments_to_database($uploaded_files, $ticket_id);
                        }
                    }
                    break;
                case 'customers':
                    //load the customers model
                    $this->load->model('clients_model');

                    $primary_contact_info = [];
                    $primary_contact_keys = ['primary_contact_firstname','primary_contact_lastname','primary_contact_email','primary_contact_phonenumber','primary_contact_position'];
                    foreach($primary_contact_keys as $key){
                        if(isset($form_data[$key])){
                            $primary_contact_info[$key] = $form_data[$key];
                            //remove the primary contact key from the form data
                            unset($form_data[$key]);
                        }
                    }    
                    //insert the customer
                    $form_data['datecreated'] = date('Y-m-d H:i:s');
                    $form_data['active'] = 1;
                    $customer_id = $this->clients_model->add($form_data);

                    if($primary_contact_info){
                        //check if the contact already exists
                        $contact_email = isset($primary_contact_info['primary_contact_email']) ? $primary_contact_info['primary_contact_email'] : '';
                        if($contact_email){
                            $this->db->where('email', $contact_email);
                            $total_rows = $this->db->count_all_results(db_prefix() . 'contacts');
                            if ($total_rows > 0) {
                                //the contact already exists, we will not add it again
                               //contact already exists, we will update the contact
                               //update the contact_Email
                               $contact_email =  '_' . time() . '_' . $contact_email;
                            }
                        }

                        $contact_id = $this->clients_model->add_contact([
                            'firstname' => isset($primary_contact_info['primary_contact_firstname']) ? $primary_contact_info['primary_contact_firstname'] : '',
                            'lastname' => isset($primary_contact_info['primary_contact_lastname']) ? $primary_contact_info['primary_contact_lastname'] : '',
                            'email' => $contact_email,
                            'phonenumber' => isset($primary_contact_info['primary_contact_phonenumber']) ? $primary_contact_info['primary_contact_phonenumber'] : '',
                            'title' => isset($primary_contact_info['primary_contact_position']) ? $primary_contact_info['primary_contact_position'] : '',
                            'is_primary' => 1,
                            'invoice_emails' => 1,
                            'estimate_emails' => 1,
                            'credit_note_emails' => 1,
                            'contract_emails' => 1,
                            'task_emails' => 1,
                            'project_emails' => 1,
                            'ticket_emails' => 1,
                            'send_set_password_email' => 1,
                        ], $customer_id);
                    }

                    //if the primary contact info is provided, we will add it to the customer
                    //add customer attachments
                    if (isset($form_attachments) && is_array($form_attachments)) {
                        $path          = get_upload_path_by_type('customer') . $customer_id . '/';
                        _maybe_create_upload_path($path);
                        $this->load->model('misc_model');
                        //we will loop through the attachments and add them to the customer
                        foreach ($form_attachments as $attachment) {
                            //each attachment can contain multiple files, attachment is an array
                            foreach ($attachment as $file) {
                                $new_attachment = [];
                                $file_path = FLEXFORM_FOLDER . $file;
                                $new_file_name = md5(time() . $file) . '.' . pathinfo($file, PATHINFO_EXTENSION);
                                $new_file_path = $path . $new_file_name;
                                if (copy($file_path, $new_file_path)) {
                                    $new_attachment[] = [
                                        'file_name' => $new_file_name,
                                        'filetype' => $this->getMimeType($new_file_path),
                                    ];
                                }
                                $this->misc_model->add_attachment_to_database($customer_id, 'customer', $new_attachment);
                            }
                        }
                    }
                    break;
                case 'leads':
                    //check if the lead already exists, we are checking by email
                    $this->load->model('leads_model');
                    $allow_duplicate_leads = $form['allow_duplicate_leads'];
                    if ($allow_duplicate_leads == 0 && isset($form_data['email'])) {
                        $where = ['email' => $form_data['email']];
                        $this->db->where($where);
                        $duplicateLead = $this->db->get(db_prefix() . 'leads')->row();
                        if ($duplicateLead) {
                            return 0;
                        }
                    }
                    //check if the lead name prefix is set
                    $lead_name_prefix = $form['lead_name_prefix'];
                    if ($lead_name_prefix) {
                        $form_data['name'] = $lead_name_prefix . ' ' . $form_data['name'];
                    }
                    $custom_fields = [];
                    $regular_fields = [];
                    foreach ($form_data as $key => $value) {
                        if (strpos($key, 'cf-') !== false) {
                            $custom_fields[$key] = $value;
                            unset($form_data[$key]);
                        } else {
                            $regular_fields[$key] = $value;
                        }
                    }
                    //let us add the lead now, form @Todo Info comes from source

                    $regular_fields['status'] = $form['lead_status'];
                    if ((isset($regular_fields['name']) && empty($regular_fields['name'])) || !isset($regular_fields['name'])) {
                        $regular_fields['name'] = 'Unknown';
                    }
                    $regular_fields['source'] = $form['lead_source'];
                    $regular_fields['addedfrom'] = 0;
                    $regular_fields['lastcontact'] = null;
                    $regular_fields['assigned'] = $form['responsible'];
                    $regular_fields['dateadded'] = date('Y-m-d H:i:s');
                    $regular_fields['from_form_id'] = 0;
                    $regular_fields['is_public'] = 0;

                    $this->db->insert(db_prefix() . 'leads', $regular_fields);
                    $lead_id = $this->db->insert_id();

                    hooks()->do_action('lead_created', [
                        'lead_id' => $lead_id,
                        'web_to_lead_form' => true,
                    ]);

                    $success = false;
                    if ($lead_id) {
                        $success = true;

                        $this->leads_model->log_lead_activity($lead_id, 'not_lead_imported_from_form', true, serialize([
                            $form_name,
                        ]));
                        // /handle_custom_fields_post
                        $custom_fields_build['leads'] = [];
                        foreach ($custom_fields as $cf => $value) {
                            $cf_id = strafter($cf, 'form-cf-');
                            $custom_fields_build['leads'][$cf_id] = $value;
                        }

                        handle_custom_fields_post($lead_id, $custom_fields_build);

                        $this->leads_model->lead_assigned_member_notification($lead_id, $responsible, true);

                        //handle_lead_attachments($lead_id, 'file-input', $form_name);
                        if (isset($form_attachments) && is_array($form_attachments)) {
                            $path           = get_upload_path_by_type('lead') . $lead_id . '/';
                            _maybe_create_upload_path($path);
                            foreach ($form_attachments as $attachment) {
                                foreach ($attachment as $file) {
                                    $file_path = FLEXFORM_FOLDER . $file;
                                    $new_file_name = md5(time() . $file) . '.' . pathinfo($file, PATHINFO_EXTENSION);
                                    $new_file_path = $path . $new_file_name;
                                    if (copy($file_path, $new_file_path)) {
                                        $new_attachment[] = [
                                            'file_name' => $new_file_name,
                                            'filetype' => $this->getMimeType($new_file_path),
                                        ];
                                        $this->leads_model->add_attachment_to_database($lead_id, $new_attachment,false,$form_name);
                                    }   
                                }
                            }
                        }

                        if (isset($regular_fields['email']) && $regular_fields['email'] != '') {
                            $lead = $this->leads_model->get($lead_id);
                            send_mail_template('lead_web_form_submitted', $lead);
                        }
                        return $lead_id;
                    }
                    break;
                default:
                    break;
            }
        }
        return $table_id;
    }

    private function getMimeType($filename)
    {
        $mimeTypes = $mimeTypes = [
            // Images
            'jpg' => 'image/jpeg',
            'jpeg' => 'image/jpeg',
            'png' => 'image/png',
            'gif' => 'image/gif',
            'bmp' => 'image/bmp',
            'webp' => 'image/webp',
            'svg' => 'image/svg+xml',
            'ico' => 'image/vnd.microsoft.icon',
            'tiff' => 'image/tiff',

            // Documents
            'pdf' => 'application/pdf',
            'doc' => 'application/msword',
            'docx' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            'xls' => 'application/vnd.ms-excel',
            'xlsx' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'ppt' => 'application/vnd.ms-powerpoint',
            'pptx' => 'application/vnd.openxmlformats-officedocument.presentationml.presentation',
            'odt' => 'application/vnd.oasis.opendocument.text',
            'ods' => 'application/vnd.oasis.opendocument.spreadsheet',
            'odp' => 'application/vnd.oasis.opendocument.presentation',
            'rtf' => 'application/rtf',
            'txt' => 'text/plain',
            'csv' => 'text/csv',
            'md' => 'text/markdown',

            // Audio
            'mp3' => 'audio/mpeg',
            'wav' => 'audio/wav',
            'ogg' => 'audio/ogg',
            'flac' => 'audio/flac',
            'm4a' => 'audio/x-m4a',
            'aac' => 'audio/aac',
            'wma' => 'audio/x-ms-wma',

            // Video
            'mp4' => 'video/mp4',
            'mov' => 'video/quicktime',
            'avi' => 'video/x-msvideo',
            'wmv' => 'video/x-ms-wmv',
            'flv' => 'video/x-flv',
            'webm' => 'video/webm',
            'mkv' => 'video/x-matroska',
            '3gp' => 'video/3gpp',
            'mpeg' => 'video/mpeg',

            // Archives
            'zip' => 'application/zip',
            'rar' => 'application/x-rar-compressed',
            'tar' => 'application/x-tar',
            'gz' => 'application/gzip',
            '7z' => 'application/x-7z-compressed',

            // Executables
            'exe' => 'application/vnd.microsoft.portable-executable',
            'msi' => 'application/x-msdownload',
            'bin' => 'application/octet-stream',
            'sh' => 'application/x-sh',
            'bat' => 'application/x-msdos-program',

            // Web
            'html' => 'text/html',
            'htm' => 'text/html',
            'css' => 'text/css',
            'js' => 'application/javascript',
            'json' => 'application/json',
            'xml' => 'application/xml',
            'php' => 'application/x-httpd-php',

            // Fonts
            'ttf' => 'font/ttf',
            'otf' => 'font/otf',
            'woff' => 'font/woff',
            'woff2' => 'font/woff2',
            'eot' => 'application/vnd.ms-fontobject',

            // Misc
            'ics' => 'text/calendar',
            'epub' => 'application/epub+zip',
            'azw' => 'application/vnd.amazon.ebook',
            'dmg' => 'application/x-apple-diskimage',
            'iso' => 'application/x-iso9660-image',
            'sqlite' => 'application/vnd.sqlite3',
        ];


        $extension = strtolower(pathinfo($filename, PATHINFO_EXTENSION));

        return isset($mimeTypes[$extension]) ? $mimeTypes[$extension] : $extension;
    }

    private function update_block_navigation_history($block_id, $form_session)
    {
        //get session id
        /*$block_navigation_history = $this->session->userdata('bnh_' . $form_session);
        if (!$block_navigation_history) {
            $block_navigation_history = [];
        }*/
        $block_navigation_history = $this->navigation_history;
        //check if the block is already in the history
        if (in_array($block_id, $block_navigation_history)) {
            //if it is, we will remove it and the ones after it
            $block_navigation_history = array_slice($block_navigation_history, 0, array_search($block_id, $block_navigation_history));
            //$block_navigation_history = array_diff($block_navigation_history, [$block_id]);
        }
        $block_navigation_history[] = $block_id;
        //$this->session->set_userdata('bnh_' . $form_session, $block_navigation_history);
        $this->navigation_history = $block_navigation_history;
        return $block_navigation_history;
    }

    private function get_block_navigation_history($form_session)
    {
        //return $this->session->userdata('bnh_' . $form_session);
        return $this->navigation_history;
    }

    private function upload_limit($block)
    {
        $block_type = $block['block_type'];
        if ($block_type == 'file') {
            if (($block['allow_multiple'] == 1)) {
                return 10;
            }
        }
        return 1;
    }

    private function get_percentage_completed($blocks, $current_block, $next = 1)
    {
        $total_blocks = count($blocks);
        $current_block_index = 1;
        foreach ($blocks as $key => $block) {
            if ($block['id'] == $current_block['id']) {
                $current_block_index = $key;
            }
        }
        return (($current_block_index + $next) / $total_blocks) * 100;
    }

    public function nav($slug)
    {
        //if request is not ajax, redirect to start of the form
        if (!$this->input->is_ajax_request()) {
            redirect(site_url('flexform/vf/' . $slug));
        }
        //load the model
        $this->load->model('flexform_model');
        $form = $this->flexform_model->get_by_slug_or_id($slug);
        if (!$form) {
            echo json_encode(['status' => 'error', 'message' => _flexform_lang('form-not-found')]);
            exit();
        }
        $block_id = $this->input->get('current');
        $current_block = flexform_get_block($block_id);
        if (!$current_block) {
            echo json_encode(['status' => 'error', 'message' => _flexform_lang('block-not-found')]);
            exit();
        }
        $nav_type = $this->input->get('type');
        //get the navigation logs
        //$form_session = $this->session->userdata('flexform_' . $form['slug']);
        $form_session = $this->input->get('svalue');
        //$nav_logs = $this->get_block_navigation_history($form_session);
        $this->navigation_history = explode(',', $this->input->get('ff_bnh'));
        $nav_logs = $this->navigation_history;

        //print_r($nav_logs);die();
        //what is the next block to go to
        $next_block_id = 0;
        $current_block_position = array_search($block_id, $nav_logs);
        if ($nav_type == 'next') {
            if ($current_block_position < count($nav_logs) - 1) {
                $next_block_id = $nav_logs[$current_block_position + 1];
            }
        } else {
            if ($current_block_position > 0) {
                $next_block_id = $nav_logs[$current_block_position - 1];
            }
        }
        if (!$next_block_id) {
            echo json_encode(['status' => 'error', 'message' => _flexform_lang('no-more-questions')]);
            exit();
        }
        $is_submit = false;
        $all_blocks = flexform_get_all_blocks($form['id']);
        $next_block = flexform_arrange_block(flexform_get_block($next_block_id));
        $nav_button_html = $this->load->view('client/footer-nav', ['form' => $form, 'showing_block' => $next_block, 'nav_logs' => $nav_logs], true);
        $html = flexform_get_display_partial($next_block, $is_submit, false, false, $form_session); //block view
        $percentage_completed = $this->get_percentage_completed($all_blocks, $next_block, 0);
        echo json_encode([
            'status' => 'success',
            'html' => $html,
            'current_percentage_completed' => $percentage_completed,
            'nav_footer_link' => $nav_button_html,
            'nav_logs' => implode(',', $nav_logs)
        ]);
    }
}