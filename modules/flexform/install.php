<?php

defined('BASEPATH') or exit('No direct script access allowed');

if (!$CI->db->table_exists(db_prefix() .'flexforms' )) {
    $CI->db->query('CREATE TABLE `' . db_prefix() . 'flexforms` (
    `id` int(11) NOT NULL,
    `name` mediumtext NOT NULL,
    `slug` mediumtext NOT NULL,
    `type` mediumtext NOT NULL,
    `type_id` int(11) NOT NULL,
    `staffid` int(11) NOT NULL,
    `published` enum("0","1") NOT NULL DEFAULT "0",
    `allow_duplicate_leads` enum("0","1") NOT NULL DEFAULT "0",
    `require_terms_and_conditions` enum("0","1") NOT NULL DEFAULT "0",
    `enable_captcha` enum("0","1") NOT NULL DEFAULT "0",
    `enable_single_page` enum("0","1") NOT NULL DEFAULT "0",
    `lead_name_prefix` mediumtext NOT NULL,
    `data_submission_notification_emails` mediumtext NOT NULL,
    `lead_source` int(11) NOT NULL,
    `lead_status` int(11) NOT NULL,
    `notify_form_submission` enum("0","1") NOT NULL DEFAULT "0",
    `notify_type` mediumtext NOT NULL,
    `notify_ids` mediumtext NOT NULL,
    `responsible` int(11) NOT NULL,
    `submit_btn_name` mediumtext NOT NULL,
    `submit_btn_text_color` mediumtext NOT NULL,
    `submit_btn_bg_color` mediumtext NOT NULL,
    `description` LONGTEXT NOT NULL,
    `date_added` datetime NOT NULL,
    `date_updated` datetime NOT NULL,
    `end_date` datetime NOT NULL,
    `privacy` VARCHAR(250) NOT NULL DEFAULT "public",
    `customerids` TEXT NOT NULL,
    `staffids` TEXT NOT NULL
  ) ENGINE=InnoDB DEFAULT CHARSET=' . $CI->db->char_set . ';');

    $CI->db->query('ALTER TABLE `' . db_prefix() . 'flexforms`
    ADD PRIMARY KEY (`id`);');

    $CI->db->query('ALTER TABLE `' . db_prefix() . 'flexforms`
    MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;');

}

if (!$CI->db->table_exists(db_prefix() .'flexformblocks' )) {
    $CI->db->query('CREATE TABLE `' . db_prefix() . 'flexformblocks` (
    `id` int(11) NOT NULL,
    `form_id` int(11) NOT NULL,
    `block_type` mediumtext NOT NULL,
    `block_order` int(11) NOT NULL,
    `date_added` datetime NOT NULL,
    `date_updated` datetime NOT NULL,
    `end_date` datetime NOT NULL,
    `title` TEXT NOT NULL,
    `description` TEXT NOT NULL,
    `required` enum("0","1") NOT NULL DEFAULT "0",
    `random` enum("0","1") NOT NULL DEFAULT "0",
    `horizontal` enum("0","1") NOT NULL DEFAULT "0",
    `is_country` enum("0","1") NOT NULL DEFAULT "0",
    `options` TEXT NOT NULL,
    `maximum_number` int(11) NOT NULL,
    `rating` int(11) NOT NULL,
    `minimum_number` int(11) NOT NULL,
    `placeholder` TEXT NOT NULL,
    `default_value` TEXT NOT NULL,
    `validation_logic` TEXT NOT NULL,
    `text_align` TEXT NOT NULL,
    `button_text` TEXT NOT NULL,
    `images` TEXT NOT NULL,
    `allow_multiple` enum("0","1") NOT NULL DEFAULT "0",
    `simple_uploader` enum("0","1") NOT NULL DEFAULT "0",
    `file_types` VARCHAR(250) NOT NULL DEFAULT "gif,jpg,png,jpeg",
    `left_label` VARCHAR(250) NOT NULL DEFAULT "",
    `right_label` VARCHAR(250) NOT NULL DEFAULT "",
    `map_to_column` TEXT NOT NULL,
    `redirect_url` VARCHAR(250) NOT NULL,
    `redirect_message` VARCHAR(250) NOT NULL,
    `ticket_list_type` mediumtext NOT NULL,
    `redirect_delay` int(11) NOT NULL,
    `confetti` INT(1) NOT NULL DEFAULT 0
  ) ENGINE=InnoDB DEFAULT CHARSET=' . $CI->db->char_set . ';');

    $CI->db->query('ALTER TABLE `' . db_prefix() . 'flexformblocks`
    ADD PRIMARY KEY (`id`);');

    $CI->db->query('ALTER TABLE `' . db_prefix() . 'flexformblocks`
    MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;');

    // Add a foreign key and cascade on delete
    $CI->db->query('ALTER TABLE `' . db_prefix() . 'flexformblocks` ADD CONSTRAINT `fk_form_id` FOREIGN KEY (`form_id`) REFERENCES `' . db_prefix() . 'flexforms`(`id`) ON DELETE CASCADE ON UPDATE CASCADE;');
}

//create table for blocks logic
if (!$CI->db->table_exists(db_prefix() .'flexformblockslogic' )) {
    $CI->db->query('CREATE TABLE `' . db_prefix() . 'flexformblockslogic` (
    `id` int(11) NOT NULL,
    `block_id` int(11) NOT NULL,
    `if_block_id` TEXT NOT NULL,
    `if_operator` TEXT NOT NULL,
    `if_value` TEXT NOT NULL,
    `next_condition` TEXT NOT NULL,
    `goto` TEXT NOT NULL,
    `other_cases_goto` int(11) NOT NULL,
    `date_added` datetime NOT NULL,
    `date_updated` datetime NOT NULL
  ) ENGINE=InnoDB DEFAULT CHARSET=' . $CI->db->char_set . ';');

    $CI->db->query('ALTER TABLE `' . db_prefix() . 'flexformblockslogic`
    ADD PRIMARY KEY (`id`);');

    $CI->db->query('ALTER TABLE `' . db_prefix() . 'flexformblockslogic`
    MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;');

    // Add a foreign key to block_id  and cascade on delete
    $CI->db->query('ALTER TABLE `' . db_prefix() . 'flexformblockslogic` ADD CONSTRAINT `fk_block_id` FOREIGN KEY (`block_id`) REFERENCES `' . db_prefix() . 'flexformblocks`(`id`) ON DELETE CASCADE ON UPDATE CASCADE;');
}

//create table for form submissions
if (!$CI->db->table_exists(db_prefix() .'flexformblockanswer' )) {
    $CI->db->query('CREATE TABLE `' . db_prefix() . 'flexformblockanswer` (
    `id` int(11) NOT NULL,
    `form_id` int(11) NOT NULL,
    `session_id` TEXT NOT NULL,
    `block_id` int(11) NOT NULL,
    `block_title` TEXT NOT NULL,
    `block_description` TEXT NOT NULL,
    `completed` enum("0","1") NOT NULL DEFAULT "0",
    `answers` TEXT NOT NULL,
    `date_added` datetime NOT NULL,
    `date_updated` datetime NOT NULL,
    `customerid` INT(11) NOT NULL DEFAULT 0,
    `staffid` INT(11) NOT NULL DEFAULT 0
  ) ENGINE=InnoDB DEFAULT CHARSET=' . $CI->db->char_set . ';');

    $CI->db->query('ALTER TABLE `' . db_prefix() . 'flexformblockanswer`
    ADD PRIMARY KEY (`id`);');

    $CI->db->query('ALTER TABLE `' . db_prefix() . 'flexformblockanswer`
    MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;');

    // Add a foreign key to form_id  and cascade on delete
    $CI->db->query('ALTER TABLE `' . db_prefix() . 'flexformblockanswer` ADD CONSTRAINT `fkanswer_form_id` FOREIGN KEY (`form_id`) REFERENCES `' . db_prefix() . 'flexforms`(`id`) ON DELETE CASCADE ON UPDATE CASCADE;');
}
//create table for form completed submissions
if (!$CI->db->table_exists(db_prefix() .'flexformcompleted' )) {
    $CI->db->query('CREATE TABLE `' . db_prefix() . 'flexformcompleted` (
    `id` int(11) NOT NULL,
    `form_id` int(11) NOT NULL,
    `session_id` TEXT NOT NULL,
    `date_added` datetime NOT NULL,
    `customerid` INT(11) NOT NULL DEFAULT 0,
    `staffid` INT(11) NOT NULL DEFAULT 0
  ) ENGINE=InnoDB DEFAULT CHARSET=' . $CI->db->char_set . ';');

    $CI->db->query('ALTER TABLE `' . db_prefix() . 'flexformcompleted`
    ADD PRIMARY KEY (`id`);');

    $CI->db->query('ALTER TABLE `' . db_prefix() . 'flexformcompleted`
    MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;');

    // Add a foreign key to form_id  and cascade on delete
    $CI->db->query('ALTER TABLE `' . db_prefix() . 'flexformcompleted` ADD CONSTRAINT `fkcompleted_form_id` FOREIGN KEY (`form_id`) REFERENCES `' . db_prefix() . 'flexforms`(`id`) ON DELETE CASCADE ON UPDATE CASCADE;');
}

//create flexform storage folder
flexform_create_storage_directory();

//create submission email template
$CI->load->library('flexform/flexform_module');
$CI->flexform_module->create_email_template();