<?php

defined('BASEPATH') or exit('No direct script access allowed');

/*
Module Name: Flex Form Builder for Perfex CRM
Description: Create Reactive Forms to collect data for leads, contacts, customers, opportunities, projects, tasks, tickets, estimates e.t.c.
Version: 1.0.6
Requires at least: 2.3.*
*/


define('FLEXFORM_MODULE_NAME', 'flexform');
register_merge_fields("flexform/merge_fields/flexform_response_merge_fields");
hooks()->add_action('admin_init', FLEXFORM_MODULE_NAME . '_permissions');
hooks()->add_action('admin_init', FLEXFORM_MODULE_NAME . '_module_init_menu_items');
hooks()->add_action('clients_init', FLEXFORM_MODULE_NAME . '_clients_init');

define('FLEXFORM_FOLDER', FCPATH . 'uploads/flexform' . '/');
/**
 * Register activation module hook
 */
register_activation_hook(FLEXFORM_MODULE_NAME, FLEXFORM_MODULE_NAME . '_module_activation_hook');

function flexform_module_activation_hook()
{
    $CI = &get_instance();
    require_once(__DIR__ . '/install.php');
}

function flexform_clients_init()
{
    //add menu item to the client area
    if (is_client_logged_in()) {
        $count = flexform_count_unsubmitted_forms('customers');
        if ($count > 0) {
            add_theme_menu_item('flexform', [
                'name' => _l('flexform_my_forms') . ' (' . $count . ')',
                'href' => flexform_client_url(),
                'position' => 40,
            ]);
        }
    }
}

function flexform_client_url()
{
    return site_url('flexform/pending');
}

function flexform_count_unsubmitted_forms($type)
{
    $userid = ($type == 'customers') ? get_client_user_id() : get_staff_user_id();
    $CI = &get_instance();
    $CI->load->model('flexform/flexform_model');
    //can check active forms (a check to see if the privacy column is available on flexform_forms table)
    if(!$CI->flexform_model->can_check_active_forms()) return 0;
    $all_active_forms = $CI->flexform_model->get_all_active_forms($type);
    $count = 0;
    if ($all_active_forms) {
        foreach ($all_active_forms as $form) {
            $form_id = $form['id'];
            $staff = ($form['staffids']) ? unserialize($form['staffids']) : [];
            $customer = ($form['customerids']) ? unserialize($form['customerids']) : [];

            if ($type == 'customers') {
                if (empty($customer) || in_array($userid, $customer)) {
                    $completed = flexform_has_user_completed_form($form_id, $type, $userid);
                    if (!$completed) {
                        $count++;
                    }
                }
            } else {
                if (empty($staff) || in_array($userid, $staff)) {
                    $completed = flexform_has_user_completed_form($form_id, $type, $userid);
                    if (!$completed) {
                        $count++;
                    }
                }
            }
        }
    }
    return $count;
}

function flexform_has_user_completed_form($form_id, $type, $userid)
{
    $CI = &get_instance();
    $CI->load->model('flexform/flexformcompleted_model');
    $param = [
        'form_id' => $form_id,
    ];
    if ($type == 'customers') {
        $param['customerid'] = $userid;
    } else {
        $param['staffid'] = $userid;
    }
    $completed = $CI->flexformcompleted_model->get($param);
    return $completed;
}
/**
 * Get all active forms
 * @param string $type
 * @param bool $exclude_completed
 * @param int|null $user_id
 * @param bool $only_active
 * @return array
 */
function flexform_get_all_active_forms($type, $exclude_completed = true, $user_id = null, $only_active = true)
{
    $CI = &get_instance();
    $CI->load->model('flexform/flexform_model');
    $active_forms = $CI->flexform_model->get_all_active_forms($type, $only_active);
    if (!$user_id) {
        $user_id = ($type == 'customers') ? get_client_user_id() : get_staff_user_id();
    }
    $forms = [];
    //get all the forms for customers or staff
    foreach ($active_forms as $form) {
        $form_id = $form['id'];
        //check if user can view the form, it is possible is for specific customers or staff or for all staff or customers
        $can_view = flexform_can_view_form($form, $user_id);
        if ($can_view) {
            //check if exclude completed is true
            if ($exclude_completed) {
                $completed = flexform_has_user_completed_form($form_id, $type, $user_id);
                if (!$completed) {
                    $forms[] = $form;
                }
            } else {
                $forms[] = $form;
            }
        }
    }
    return $forms;
}

/**
 * Register language files, must be registered if the module is using languages
 */
register_language_files(FLEXFORM_MODULE_NAME, [FLEXFORM_MODULE_NAME]);
function flexform_permissions()
{
    $capabilities = [];

    $capabilities['capabilities'] = [
        'view' => _l('permission_view') . '(' . _l('permission_global') . ')',
        'create' => _l('permission_create'),
        'edit' => _l('permission_edit'),
        'delete' => _l('permission_delete'),
    ];

    register_staff_capabilities(FLEXFORM_MODULE_NAME, $capabilities, _l(FLEXFORM_MODULE_NAME));

    //let us try to kit menu for customer
    $CI = &get_instance();

    $CI->app_tabs->add_customer_profile_tab(FLEXFORM_MODULE_NAME, [
        'name'     => _l('flexform_forms'),
        'icon'     => 'fa-solid fa-file-lines',
        'view'     => 'flexform/admin/tabs',
        'position' => 50,
        'badge'    => [],
    ]);
}
/**
 * Init Form module menu items in setup in admin_init hook
 * @return null
 */
function flexform_module_init_menu_items()
{
    /**
     * If user has permission to see Flexform module
     */
    $CI = &get_instance();
    if (has_permission(FLEXFORM_MODULE_NAME, '', 'view')) {
        $CI->app_menu->add_sidebar_menu_item(FLEXFORM_MODULE_NAME, [
            'name' => _flexform_lang('flexform'),
            'href' => admin_url('flexform'),
            'position' => 16,
            'icon' => 'fa-solid fa-cubes',
        ]);

        //form builder
        $CI->app_menu->add_sidebar_children_item(FLEXFORM_MODULE_NAME, [
            'slug' => 'flexform-form-builder',
            'name' => _flexform_lang('form_builder'),
            'href' => admin_url('flexform'),
            'position' => 15,
            'icon' => 'fa-solid fa-puzzle-piece',
        ]);

        //submenu for staff forms
        $CI->app_menu->add_sidebar_children_item(FLEXFORM_MODULE_NAME, [
            'slug' => 'flexform-staff-forms',
            'name' => _flexform_lang('staff_forms'),
            'href' => admin_url('flexform/staff'),
            'position' => 17,
            'icon' => 'fa-regular fa-file-lines',
        ]);
    }

    //show form with count for staff who have forms pending
    if (is_staff_logged_in()) {
        $count = flexform_count_unsubmitted_forms('staff');
        if ($count > 0) {
            $CI->app_menu->add_sidebar_menu_item(FLEXFORM_MODULE_NAME . '_staff', [
                'name' => _flexform_lang('my_forms'),
                'href' => admin_url('flexform/pending'),
                'position' => 17,
                'icon' => 'fa-regular fa-file-lines',
                'badge'    => [
                    'value' => $count,
                    'color' => 'info',
                ],
            ]);
        }
    }
}

/**
 * Create storage directory for the module
 * @return void
 */
function flexform_create_storage_directory()
{
    if (!is_dir(FLEXFORM_FOLDER)) {
        mkdir(FLEXFORM_FOLDER, 0755);
        $fp = fopen(rtrim(FLEXFORM_FOLDER, '/') . '/' . 'index.html', 'w');
        fclose($fp);
    }
}

/**
 * Get the language string for the module
 * @param $slug
 * @return string
 */
function _flexform_lang($slug)
{
    return _l('flexform_' . $slug);
}

/**
 * Get the connected to Types for the form, this is used to map the form to a table
 * @return array[]
 */
function flexform_get_connected_to(): array
{
    return [
        [
            'id' => '',
            'name' => _flexform_lang('none')
        ],
        [
            'id' => 'customers',
            'name' => _flexform_lang('customers')
        ],
        [
            'id' => 'leads',
            'name' => _l('leads')
        ],
        [
            'id' => 'tickets',
            'name' => _l('tickets')
        ],
    ];
}

/**
 * Get the partial for the block display
 * @param $block
 * @return object|string
 */
function flexform_get_display_partial($block, $is_submit = false, $preview = true, $is_first_block = false, $form_session = null)
{
    $type = $block['block_type'];
    $path =  'setup/display/' . $type;
    $CI = &get_instance();
    return $CI->load->view($path, ['block' => $block, 'is_submit' => $is_submit, 'preview' => $preview, 'is_first_block' => $is_first_block, 'form_session' => $form_session], true);
}

/**
 * Get the partial for the block setup
 * @param array $block
 * @return string
 */
function flexform_get_setup_autosubmitform_partial($block)
{
    $type = $block['block_type'];
    $folder_name = 'auto-submit-form';
    $path = 'setup/' . $folder_name . '/' . $type;
    $CI = &get_instance();
    return $CI->load->view($path, ['block' => $block], true);
}

/**
 * Get the List of Blocks UI for the form setup
 * @param $block
 * @param $index
 * @param $is_active
 * @return object|string
 */
function flexform_get_block_partial($block, $index, $is_active = false)
{
    $path = 'setup/blocks/each-block-li';
    $CI = &get_instance();
    //increase index by 1 because index start from 0
    return $CI->load->view($path, ['block' => $block, 'is_active' => $is_active, 'index' => $index + 1], true);
}

/**
 * Get the list of all the modals for the form setup
 * @param $form
 * @return object|string
 */
function flexform_init_modal($props)
{
    $CI = &get_instance();
    $CI->load->view('setup/modals', array('props' => $props));
}

function flexform_get_block_form($block)
{
    $CI = &get_instance();
    $CI->load->model('flexform_model');
    $form_id = $block['form_id'];
    return $CI->flexform_model->get(['id' => $form_id]);
}

function flexform_get_form($form_id)
{
    $CI = &get_instance();
    $CI->load->model('flexform_model');
    return $CI->flexform_model->get(['id' => $form_id]);
}

function flexform_get_all_blocks($form_id)
{
    $CI = &get_instance();
    $CI->load->model('flexformblocks_model');
    $blocks = $CI->flexformblocks_model->all(['form_id' => $form_id]);
    $blocks_arranged = [];
    //arrange each blocks using flexform_arrange_block
    foreach ($blocks as $block) {
        $blocks_arranged[] = flexform_arrange_block($block);
    }
    return $blocks_arranged;
}

function flexform_get_image_url($image)
{
    return site_url('uploads/flexform/' . $image);
}

function flexform_upload_config()
{
    return [
        'upload_path' => FLEXFORM_FOLDER,
        'allowed_types' => 'gif|jpg|png|jpeg|webp|heic|bmp|svg',
        'max_size' => 10000,
        'encrypt_name' => true,
    ];
}

function flexform_blocks($block_key = ''): array
{
    $blocks = array(
        'statement' => [
            'name' => _flexform_lang('statement'),
            'icon' => 'fa-file-pen',
            'img' => module_dir_url('flexform', 'assets/images/statement.png'),
            'heading' => _flexform_lang('statement-block'),
            'description' => _flexform_lang('statement-block-description'),
            'default_label' => _flexform_lang('please_provide_info_below'),
        ],
        'short-text' => [
            'name' => _flexform_lang('short-text'),
            'icon' => ' fa-grip-lines',
            'img' => module_dir_url('flexform', 'assets/images/short-text.png'),
            'heading' => _flexform_lang('short-text-block'),
            'description' => _flexform_lang('short-text-block-description'),
            'default_label' => _flexform_lang('please_provide_short_text'),
        ],
        'long-text' => [
            'name' => _flexform_lang('long-text'),
            'icon' => 'fa-bars',
            'img' => module_dir_url('flexform', 'assets/images/long-text.png'),
            'heading' => _flexform_lang('long-text-block'),
            'description' => _flexform_lang('long-text-block-description'),
            'default_label' => _flexform_lang('please_provide_long_text'),
        ],
        'multiple-choice' => [
            'name' => _flexform_lang('multiple-choice'),
            'icon' => 'fa-regular fa-square',
            'img' => module_dir_url('flexform', 'assets/images/multiple-choice.png'),
            'heading' => _flexform_lang('multiple-choice-block'),
            'description' => _flexform_lang('multiple-choice-block-description'),
            'default_label' => _flexform_lang('please_pick_one_or_more_options'),
        ],
        'single-choice' => [
            'name' => _flexform_lang('single-choice'),
            'icon' => 'fa-regular fa-circle',
            'img' => module_dir_url('flexform', 'assets/images/single-choice.png'),
            'heading' => _flexform_lang('single-choice-block'),
            'description' => _flexform_lang('single-choice-block-description'),
            'default_label' => _flexform_lang('please_pick_one_option'),
        ],
        'dropdown' => [
            'name' => _flexform_lang('dropdown-list'),
            'icon' => 'fa-arrow-down-short-wide',
            'img' => module_dir_url('flexform', 'assets/images/dropdown.png'),
            'heading' => _flexform_lang('dropdown-list-block'),
            'description' => _flexform_lang('dropdown-list-block-description'),
            'default_label' => _flexform_lang('please_pick_one_option_from_list'),
        ],
        'date' => [
            'name' => _flexform_lang('date'),
            'icon' => 'fa-regular fa-calendar-days',
            'img' => module_dir_url('flexform', 'assets/images/date.png'),
            'heading' => _flexform_lang('date-block'),
            'description' => _flexform_lang('date-block-description'),
            'default_label' => _flexform_lang('please_provide_date'),
        ],
        'datetime' => [
            'name' => _flexform_lang('date-time'),
            'icon' => 'fa-solid fa-business-time',
            'img' => module_dir_url('flexform', 'assets/images/datetime.png'),
            'heading' => _flexform_lang('date-time-block'),
            'description' => _flexform_lang('date-time-block-description'),
            'default_label' => _flexform_lang('please_provide_date_and_time'),
        ],
        'time' => [
            'name' => _flexform_lang('time'),
            'icon' => 'fa-solid fa-clock',
            'img' => module_dir_url('flexform', 'assets/images/time.png'),
            'heading' => _flexform_lang('time-block'),
            'description' => _flexform_lang('time-block-description'),
            'default_label' => _flexform_lang('please_provide_time'),
        ],
        'number' => [
            'name' => _flexform_lang('number'),
            'icon' => 'fa-solid fa-hashtag',
            'img' => module_dir_url('flexform', 'assets/images/number.png'),
            'heading' => _flexform_lang('number-block'),
            'description' => _flexform_lang('number-block-description'),
            'default_label' => _flexform_lang('please_provide_number'),
        ],
        'file' => [
            'name' => _flexform_lang('file-upload'),
            'icon' => 'fa-upload',
            'img' => module_dir_url('flexform', 'assets/images/file.png'),
            'heading' => _flexform_lang('file-upload-block'),
            'description' => _flexform_lang('file-upload-block-description'),
            'default_label' => _flexform_lang('please_provide_file'),
        ],
        'star-rating' => [
            'name' => _flexform_lang('star-rating'),
            'icon' => 'fa-regular fa-star',
            'img' => module_dir_url('flexform', 'assets/images/star-rating.png'),
            'heading' => _flexform_lang('star-rating-block'),
            'description' => _flexform_lang('star-rating-block-description'),
            'default_label' => _flexform_lang('please_provide_star_rating'),
        ],
        'opinion-scale' => [
            'name' => _flexform_lang('opinion-scale'),
            'icon' => 'fa-signal',
            'img' => module_dir_url('flexform', 'assets/images/opinion-scale.png'),
            'heading' => _flexform_lang('opinion-scale-block'),
            'description' => _flexform_lang('opinion-scale-block-description'),
            'default_label' => _flexform_lang('please_provide_opinion_scale'),
        ],
        'color-picker' => [
            'name' => _flexform_lang('color-picker'),
            'icon' => 'fa-solid fa-palette',
            'img' => module_dir_url('flexform', 'assets/images/color-picker.png'),
            'heading' => _flexform_lang('color-picker-block'),
            'description' => _flexform_lang('color-picker-block-description'),
            'default_label' => _flexform_lang('please_provide_color'),
        ],
        'thank-you' => [
            'name' => _flexform_lang('thank-you'),
            'icon' => 'fa-solid fa-hourglass-end',
            'img' => module_dir_url('flexform', 'assets/images/thank-you.png'),
            'heading' => _flexform_lang('thank-you-block'),
            'description' => _flexform_lang('thank-you-block-description'),
            'default_label' => _flexform_lang('thank-you-message'),
        ],
        'signature' => [
            'name' => _flexform_lang('signature'),
            'icon' => 'fa-signature',
            'img' => module_dir_url('flexform', 'assets/images/signature.png'),
            'heading' => _flexform_lang('signature-block'),
            'description' => _flexform_lang('signature-block-description'),
            'default_label' => _flexform_lang('please_provide_signature'),
        ],
    );
    if ($block_key) {
        return $blocks[$block_key];
    }
    return $blocks;
}

function flexform_get_block_answer($block, $fs = null)
{
    if (!$fs) return '';
    $localformsession = $fs;
    $CI = &get_instance();
    $CI->load->model('flexformblockanswer_model');
    $block_id = $block['id'];
    $block_answer = $CI->flexformblockanswer_model->get(['block_id' => $block_id, 'session_id' => $localformsession]);
    return ($block_answer) ? flexformPerfectUnserialize($block_answer['answers']) : '';
}

function flexform_can_block_have_logic($block): bool
{
    $block_type = $block['block_type'];
    $blocks = ['signature', 'file', 'statement', 'thank-you'];
    return !in_array($block_type, $blocks);
}

function flexform_logic_commands($block)
{
    $block_type = $block['block_type'];
    $commands = [];
    switch ($block_type) {
        case 'short-text':
        case 'long-text':
            $commands = [
                'is' => _flexform_lang('is'),
                'is-not' => _flexform_lang('is-not'),
                'contains' => _flexform_lang('contains'),
                'does-not-contain' => _flexform_lang('does-not-contain'),
                'starts-with' => _flexform_lang('starts-with'),
                'ends-with' => _flexform_lang('ends-with'),
            ];
            break;
        case 'multiple-choice':
        case 'single-choice':
        case 'dropdown':
            $commands = [
                'is' => _flexform_lang('is'),
                'is-not' => _flexform_lang('is-not'),
            ];
            break;
        case 'date':
        case 'datetime':
            $commands = [
                'is' => _flexform_lang('is'),
                'is-not' => _flexform_lang('is-not'),
                'is-before' => _flexform_lang('is-before'),
                'is-after' => _flexform_lang('is-after'),
            ];
            break;
        case 'number':
        case 'star-rating':
            $commands = [
                'is' => _flexform_lang('is'),
                'is-not' => _flexform_lang('is-not'),
                'is-greater-than' => _flexform_lang('is-greater-than'),
                'is-less-than' => _flexform_lang('is-less-than'),
            ];
            break;
    }
    return $commands;
}

function flexform_str_limit($string, $limit = 30)
{
    if (!$string) return $string;
    return strlen($string) > $limit ? substr($string, 0, $limit) . '...' : $string;
}
function flexform_arrange_block($block)
{
    if (!$block) return [];
    $block_type = $block['block_type'];
    $block['static'] = flexform_blocks($block_type);
    //if it has options, then we need to decode it
    if ($block['options'] && is_string($block['options'])) {
        $block['options'] = flexformPerfectUnserialize($block['options']);
        //if the block is random, then we need to shuffle the options
        if ($block['random']) {
            shuffle($block['options']);
        }
    }
    return $block;
}

function flexform_requires_mapping($form): bool
{
    if ($form['type'] != '') return true;
    return false;
}


function flexform_block_label($block)
{
    $label = $block['title'];
    if (!$label) {
        return _flexform_lang('please_provide_info_below');
    }
    return $label;
}

function flexform_get_pre_and_current_blocks($block)
{
    $CI = &get_instance();
    $CI->load->model('flexformblocks_model');
    $blocks = $CI->flexformblocks_model->get_pre_and_current_blocks($block);
    return $blocks;
}

function flexform_get_next_blocks($block)
{
    $CI = &get_instance();
    $CI->load->model('flexformblocks_model');
    $blocks = $CI->flexformblocks_model->get_next_blocks($block);
    return $blocks;
}

function flexform_get_block_logics($block_id)
{
    $CI = &get_instance();
    $CI->load->model('flexformblockslogic_model');
    return $CI->flexformblockslogic_model->get_block_logics($block_id);
}

function flexform_get_block($block_id)
{
    $CI = &get_instance();
    $CI->load->model('flexformblocks_model');
    return flexform_arrange_block($CI->flexformblocks_model->get(['id' => $block_id]));
}

function flexformPerfectSerialize($string)
{
    return base64_encode(serialize($string));
}

function flexformPerfectUnserialize($string)
{
    if (base64_decode($string, true) == true) {
        return @unserialize(base64_decode($string));
    } else {
        return @unserialize($string);
    }
}

function flexform_html_status($status)
{
    switch ($status) {
        case '1':
            $html = ' <span class="tw-text-success-500 tw-text-sm"><i class="fa-solid fa-check-circle"></i> ' . _flexform_lang('published') . '</span>';
            break;
        default:
            $html = ' <span class="tw-text-warning-500 tw-text-sm"><i class="fa-solid fa-exclamation-circle"></i> ' . _flexform_lang('unpublished') . '</span>';
    }
    return $html;
}

function flexform_render_answer($response)
{
    $html = '';
    $answer = $response['answer'];
    if (!$answer) return '';
    $block = $response['block'];
    $block_type = $block['block_type'];
    if ($block_type == 'file') {
        foreach ($answer as $file) {
            $html .= '<a href="' . flexform_get_image_url($file) . '" target="_blank">' . _flexform_lang('view') . '</a><br><br>';
        }
    } elseif ($block_type == 'signature') {
        $html = '<a href="' . flexform_get_image_url($answer) . '"  target="_blank"> <img src="' . flexform_get_image_url($answer) . '" alt="signature" style="width: 100px; height: 100px"></a>';
    } elseif ($block_type == 'color-picker') {
        $html = '<div style="width: 20px; height: 20px; background-color: ' . $answer . '"></div>' . $answer;
    } elseif ($block_type == 'multiple-choice' || $block_type == 'dropdown') {
        //if the type is country
        if ($block['is_country'] == 1) {
            return get_country_name($answer);
        }
        if ($block['ticket_list_type'] == 'department') {
            $CI = &get_instance();
            $CI->load->model('departments_model');
            $department = $CI->departments_model->get($answer);
            return $department->name;
        } elseif ($block['ticket_list_type'] == 'priority') {
            return ticket_priority_translate($answer);
        } elseif ($block['ticket_list_type'] == 'service') {
            $CI = &get_instance();
            $CI->load->model('tickets_model');
            $service = $CI->tickets_model->get_service($answer);
            //check if the service is found
            if (!$service) return $answer;
            return $service->name;
        }
        if (!is_array($answer)) $answer = [$answer];
        $html = '<ol>';
        foreach ($answer as $ans) {
            $html .= '<li>' . $ans . '</li>';
        }
        $html .= '</ol>';
    } else {
        $html = nl2br($answer);
    }
    return $html;
}

function flexform_count_responses($form_id)
{
    $CI = &get_instance();
    $CI->load->model('flexformblockanswer_model');
    return $CI->flexformblockanswer_model->count_form_responses($form_id);
}

function flexform_add_default_blocks($form_id)
{
    $CI = &get_instance();
    $CI->load->model('flexformblocks_model');
    $CI->load->model('flexform_model');
    $form = $CI->flexform_model->get(['id' => $form_id]);
    //add statement block
    $statement = [
        'form_id' => $form_id,
        'block_type' => 'statement',
        'title' => _flexform_lang('welcome-to-form'),
        'description' => _flexform_lang('welcome-to-form-description'),
        'block_order' => 1,
        'text_align' => 'center',
        'button_text' => _flexform_lang('lets-get-started'),
    ];
    $CI->flexformblocks_model->add($statement);
    $prefill_blocks = flexform_get_form_columns($form);
    $order = 2;
    if ($prefill_blocks) {
        foreach ($prefill_blocks as $key => $label) {
            $block = [
                'form_id' => $form_id,
                'block_type' => 'short-text',
                'title' => ucfirst(str_replace('_', ' ', $label)),
                'description' => _flexform_lang('please_provide_short_text'),
                'block_order' => $order,
                'button_text' => _flexform_lang('next'),
                'placeholder' => $label,
                'map_to_column' => $key,
            ];
            if ($key == 'country') {
                $block['is_country'] = '1';
                $block['block_type'] = 'dropdown';
                $block['description'] = _flexform_lang('please_pick_one_option');
            }
            if ($key == 'name') {
                $block['title'] = _flexform_lang('your-name');
            }
            if ($key == 'department' || $key == 'priority' || $key == 'service') {
                $block['block_type'] = 'dropdown';
                $block['ticket_list_type'] = $key;
            }
            if ($key == "attachments") {
                $block['block_type'] = 'file';
                $block['description'] = _flexform_lang('attachments');
                $block['allow_multiple'] = '1';
            }
            if ($key == 'description' || $key == 'address' || $key == 'message') {
                $block['block_type'] = 'long-text';
            }
            $required_fields = ['name', 'email', 'subject', 'message', 'department', 'priority'];
            if (in_array($key, $required_fields)) {
                $block['required'] = '1';
            }
            $CI->flexformblocks_model->add($block);
            $order++;
        }
    }
    //add thank you block
    $thank_you = [
        'form_id' => $form_id,
        'block_type' => 'thank-you',
        'title' => _flexform_lang('thank-you-message'),
        'description' => _flexform_lang('thank-you-message-description'),
        'block_order' => $order,
    ];
    $CI->flexformblocks_model->add($thank_you);
}

function flexform_get_response($form_id, $session_id, $active_tab = 'complete')
{
    $CI = &get_instance();
    $CI->load->model('flexformcompleted_model');
    $CI->load->model('flexformblockanswer_model');
    $all_form_blocks = flexform_get_all_blocks($form_id);
    $responses = [];
    //loop through all the blocks
    foreach ($all_form_blocks as $block) {
        //skip thank you and statement
        if ($block['block_type'] == 'thank-you' || $block['block_type'] == 'statement') {
            continue;
        }
        $answer =  $CI->flexformblockanswer_model->get(['session_id' => $session_id, 'block_id' => $block['id']]);
        $date_added = $answer ? $answer['date_added'] : '';
        //if we are getting completed responses, the time will be on the form completed table
        if ($active_tab == 'complete') {
            $completed = $CI->flexformcompleted_model->get(['session_id' => $session_id]);
            $date_added = $completed ? $completed['date_added'] : '';
        }
        $responses[$session_id][] = array(
            'answer' => $answer ? flexformPerfectUnserialize($answer['answers']) : '',
            'block' => $block,
            'date_added' => $date_added,
        );
    }
    return $responses;
}

function flexform_app_pdf($path, ...$params)
{
    $basename = ucfirst(basename(strbefore($path, EXT)));
    if (!endsWith($path, EXT)) {
        $path .= EXT;
    }
    include_once($path);
    return (new $basename(...$params))->prepare();
}

function flexform_response_pdf($responses, $form)
{
    return flexform_app_pdf(APP_MODULES_PATH . FLEXFORM_MODULE_NAME . '/libraries/pdf/Form_submission_data_pdf', $responses, $form);
}

function flexform_is_thank_you_block($block)
{
    return $block['block_type'] == 'thank-you';
}

function flexform_is_form_single_page($form)
{
    return $form['enable_single_page'] == 1;
}

function flexform_is_statement_block($block)
{
    return $block['block_type'] == 'statement';
}
//version 105
function flexform_get_privacy()
{
    return [
        [
            'id' => 'public',
            'name' => _flexform_lang('public')
        ],
        [
            'id' => 'customers',
            'name' => _flexform_lang('customers')
        ],
        [
            'id' => 'staff',
            'name' => _flexform_lang('staff')
        ],
    ];
}

function flexform_can_view_form($form, $user_id = null)
{
    //check if form is public
    if ($form['privacy'] == 'public' || $form['privacy'] == '') {
        return true;
    }
    //check if form is for customers
    if ($form['privacy'] == 'customers') {
        $customer_id = ($user_id) ? $user_id : get_client_user_id();
        //is it for specific customers
        if ($customer_id) {
            if ($form['customerids']) {
                $customerids = unserialize($form['customerids']);
                if (in_array($customer_id, $customerids)) {
                    return true;
                }
            } else {
                return true; //if no specific customers, then all customers can view
            }
        }
    }
    //check if form is for staff
    if ($form['privacy'] == 'staff') {
        $staff_id = ($user_id) ? $user_id : get_staff_user_id();
        if ($staff_id) {
            if ($form['staffids']) {
                $staffids = unserialize($form['staffids']);
                if (in_array($staff_id, $staffids)) {
                    return true;
                }
            } else {
                return true; //if no specific staff, then all staff can view
            }
        }
    }
    return false;
}

function flexform_get_completed_forms_by_customer($customer_id)
{
    $CI = &get_instance();
    //get completed forms by the customer
    $CI->load->model('flexformcompleted_model');
    $completed_forms = $CI->flexformcompleted_model->all(['customerid' => $customer_id]);
    return $completed_forms;
}

function flexform_get_privacy_name($form)
{
    $privacy_name = '';
    switch ($form['privacy']) {
        case 'public':
            $privacy_name = _flexform_lang('public');
            break;
        case 'customers':
            $privacy_name = _flexform_lang('customers');
            //it could be for specific customers
            if ($form['customerids']) {
                $customerids = unserialize($form['customerids']);
                if ($customerids) {
                    $privacy_name = _flexform_lang('selected_customers') . ' (' . count($customerids) . ')';
                }
            } else {
                $privacy_name = _flexform_lang('all_customers');
            }
            break;
        case 'staff':
            $privacy_name = _flexform_lang('staff');
            //it could be for specific staff
            if ($form['staffids']) {
                $staffids = unserialize($form['staffids']);
                if ($staffids) {
                    $privacy_name = _flexform_lang('selected_staff') . ' (' . count($staffids) . ')';
                }
            } else {
                $privacy_name = _flexform_lang('all_staff');
            }
            break;
        default:
            $privacy_name = _flexform_lang('public');
            break;
    }
    return $privacy_name;
}