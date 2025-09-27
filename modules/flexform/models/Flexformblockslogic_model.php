<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Flexformblockslogic_model extends App_Model
{

    protected $table = 'flexformblockslogic';

    public function __construct(){
        parent::__construct();
    }

    /**
     * @param array $conditions
     * @return array|array[]
     * get all models
     */
    public function all($conditions = [])
    {
        $this->db->select('*');
        $this->db->from(db_prefix() . $this->table);
        if (!empty($conditions)) {
            $this->db->where($conditions);
        }
        $query = $this->db->get();
        return $query->result_array();
    }

    /**
     * @param $conditions
     * @return array
     * get model by id
     */

    public function get($conditions)
    {
        $this->db->select('*');
        $this->db->from(db_prefix() . $this->table);
        if (!empty($conditions)) {
            $this->db->where($conditions);
        }
        $query = $this->db->get();
        return $query->row_array();
    }

    //delete all block logic by block_id
    public function delete_all($block_id)
    {
        $this->db->where('block_id', $block_id);
        $this->db->delete(db_prefix() . $this->table);
        return $this->db->affected_rows();
    }

    //add new logic
    public function add($data)
    {
        $this->db->insert(db_prefix() . $this->table, $data);
        return $this->db->insert_id();
    }

    function get_block_logics($block_id){
        $this->db->select('*');
        $this->db->from(db_prefix() . $this->table);
        $this->db->where('block_id', $block_id);
        $query = $this->db->get();
        return $query->result_array();
    }

}