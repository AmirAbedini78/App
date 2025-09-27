<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Migration_Version_105 extends App_module_migration
{
    public function up()
    {
        $CI = &get_instance();
        $table_name = 'flexforms';
        try {
            $CI->db->query('ALTER TABLE `' . db_prefix() . $table_name . '`  ADD COLUMN  `privacy` VARCHAR(250) NOT NULL DEFAULT "public"');
            //customerids column Text
            $CI->db->query('ALTER TABLE `' . db_prefix() . $table_name . '`  ADD COLUMN  `customerids` TEXT NOT NULL');
            //staffids column Text
            $CI->db->query('ALTER TABLE `' . db_prefix() . $table_name . '`  ADD COLUMN  `staffids` TEXT NOT NULL');

            //when the user answer a form, we need to add the customerid and staffid to the answer, DEFAULT 0
            $table_name = "flexformblockanswer";
            $CI->db->query('ALTER TABLE `' . db_prefix() . $table_name . '`  ADD COLUMN  `customerid` INT(11) NOT NULL DEFAULT 0');
            $CI->db->query('ALTER TABLE `' . db_prefix() . $table_name . '`  ADD COLUMN  `staffid` INT(11) NOT NULL DEFAULT 0');

            //when the user complete a form, we need to add the customerid and staffid to the completed
            $table_name = "flexformcompleted";
            $CI->db->query('ALTER TABLE `' . db_prefix() . $table_name . '`  ADD COLUMN  `customerid` INT(11) NOT NULL DEFAULT 0');
            $CI->db->query('ALTER TABLE `' . db_prefix() . $table_name . '`  ADD COLUMN  `staffid` INT(11) NOT NULL DEFAULT 0');

            //confetti column 0 or 1
            $table_name = "flexformblocks";
            $CI->db->query('ALTER TABLE `' . db_prefix() . $table_name . '`  ADD COLUMN  `confetti` INT(1) NOT NULL DEFAULT 0');
        }catch (Exception $e) {
            // do nothing
        }
    }
}