<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Flexformcompleted_model extends App_Model
{
    protected $table = 'flexformcompleted';

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
     * @param $conditions
     * @return mixed
     * update model
     */
    public function delete($conditions)
    {
        $this->db->where($conditions);
        return $this->db->delete(db_prefix() . $this->table);
    }
}
