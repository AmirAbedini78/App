<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Migration_Version_104 extends App_module_migration
{
    public function up()
    {
        $CI = &get_instance();
        $table_name = 'flexforms';
        try {
            //add enable captcha column
            $CI->db->query('ALTER TABLE `' . db_prefix() . $table_name . '`  ADD COLUMN  `enable_single_page` ENUM("0","1") NOT NULL DEFAULT "0"');
            //add data_submission_notification_emails column
            $CI->db->query('ALTER TABLE `' . db_prefix() . $table_name . '`  ADD COLUMN  `data_submission_notification_emails` mediumtext NOT NULL');

            //add simple_uploader to the flexform_blocks
            $CI->db->query('ALTER TABLE `' . db_prefix() . 'flexformblocks`  ADD COLUMN  `simple_uploader` ENUM("0","1") NOT NULL DEFAULT "0"');
            //add file_types to the flexform_blocks
            $CI->db->query('ALTER TABLE `' . db_prefix() . 'flexformblocks`  ADD COLUMN  `file_types` VARCHAR(250) NOT NULL DEFAULT "gif,jpg,png,jpeg"');
            //add left label and right label to the flexform_blocks
            $CI->db->query('ALTER TABLE `' . db_prefix() . 'flexformblocks`  ADD COLUMN  `left_label` VARCHAR(250) NOT NULL DEFAULT "Not Likely"');
            $CI->db->query('ALTER TABLE `' . db_prefix() . 'flexformblocks`  ADD COLUMN  `right_label` VARCHAR(250) NOT NULL DEFAULT "Highly Likely"');
        }catch (Exception $e) {
            // do nothing
        }
    }
}