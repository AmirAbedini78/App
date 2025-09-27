<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Migration_Version_102 extends App_module_migration
{
    public function up()
    {
        $CI = &get_instance();
        $table_name = 'flexforms';
        try {
            //add enable captcha column
            $CI->db->query('ALTER TABLE `' . db_prefix() . $table_name . '`  ADD COLUMN  `enable_captcha` ENUM("0","1") NOT NULL DEFAULT "0"');
            //add require terms and conditions column
            $CI->db->query('ALTER TABLE `' . db_prefix() . $table_name . '`  ADD COLUMN  `require_terms_and_conditions` ENUM("0","1") NOT NULL DEFAULT "0"');

            //ALTER TABLE tblflexformblocks ADD COLUMN ticket_list_type mediumtext NOT NULL;
            $table_name = 'flexformblocks';
            $CI->db->query('ALTER TABLE `' . db_prefix() . $table_name . '`  ADD COLUMN `ticket_list_type` mediumtext NOT NULL');

        } catch (Exception $e) {
            // do nothing
        }

    }
}