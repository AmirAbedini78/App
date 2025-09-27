<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Flexformblockanswer_model extends App_Model
{
    protected $table = 'flexformblockanswer';

    public function __construct()
    {
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

    /**
     * @param $data
     * @return mixed
     * insert model
     */
    public function add($data)
    {
        $this->db->insert(db_prefix() . $this->table, $data);
        return $this->db->insert_id();
    }

    /**
     * @param $data
     * @param $where
     * @return mixed
     * update model
     */
    public function update($data, $where)
    {
        $this->db->where($where);
        $this->db->update(db_prefix() . $this->table, $data);
        return $this->db->affected_rows();
    }

    /**
     * @param $where
     * @ return mixed
     * delete model
     */
    public function delete($where)
    {
        $this->db->where($where);
        $this->db->delete(db_prefix() . $this->table);
        return $this->db->affected_rows();
    }

    public function get_completed_group_by_session($form_id){
        //get all responses for the form where completed is 1
        $this->db->select('session_id');
        $this->db->from(db_prefix() . $this->table);
        $this->db->where('form_id', $form_id);
        $this->db->where('completed', '1');
        //group by session_id
        $this->db->group_by('session_id');
        $query = $this->db->get();
        return  $query->result_array();
    }

    public function get_partial_group_by_session($form_id){
        //get all responses for the form where completed is 0
        $this->db->select('session_id');
        $this->db->from(db_prefix() . $this->table);
        $this->db->where('form_id', $form_id);
        $this->db->where('completed', '0');
        //group by session_id
        $this->db->group_by('session_id');
        $query = $this->db->get();
        return  $query->result_array();
    }

    public function count_form_responses($form_id){
        //get all responses for the form
        $this->db->select('session_id');
        $this->db->from(db_prefix() . $this->table);
        $this->db->where('form_id', $form_id);
        $this->db->group_by('session_id');
        $query = $this->db->get();
        return  $query->num_rows();
    }
}