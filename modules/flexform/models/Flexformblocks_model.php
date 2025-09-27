<?php


defined('BASEPATH') or exit('No direct script access allowed');

class Flexformblocks_model extends App_Model
{
    protected $table = 'flexformblocks';

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
        $this->db->order_by('block_order', 'asc');
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
     * @return bool
     * add model
     */
    public function add($data)
    {
        $data['date_added'] = date('Y-m-d H:i:s');
        $data['date_updated'] = date('Y-m-d H:i:s');
        $this->db->insert(db_prefix() . $this->table, $data);
        $insert_id = $this->db->insert_id();
        if ($insert_id) {
            return $insert_id;
        }
        return false;
    }

    /**
     * @param $data
     * @param $id
     * @return bool
     * update model
     */
    public function update($data, $id)
    {
        $this->db->where('id', $id);
        $this->db->update(db_prefix() . $this->table, $data);
        if ($this->db->affected_rows() > 0) {
            log_activity('Form Block Updated [ID:' . $id . ', ' . ']');
            return true;
        }
        return false;
    }

    /**
     * @param $id
     * @return bool
     * delete model
     */
    public function delete($id)
    {
        $this->db->where('id', $id);
        $this->db->delete(db_prefix() . $this->table);
        if ($this->db->affected_rows() > 0) {
            log_activity('Form Block [ID:' . $id . ']');
            return true;
        }
        return false;
    }

    /**
     * @return int|mixed
     */
    public function get_last_block_order($form_id)
    {
        $this->db->select('block_order');
        $this->db->from(db_prefix() . $this->table);
        $this->db->where('form_id', $form_id);
        $this->db->order_by('block_order', 'desc');
        $query = $this->db->get();
        $result = $query->row_array();
        if ($result) {
            return $result['block_order'];
        }
        return 0;
    }

    /**
     * @param $block
     * @return array|array[]
     */
    public function get_pre_and_current_blocks($block)
    {
        //block_form_id
        $form_id = $block['form_id'];
        $this->db->select('*');
        $this->db->from(db_prefix() . $this->table);
        $this->db->where('form_id', $form_id);
        $this->db->where('block_order <', $block['block_order']);
        //skip block_type with statement, file and signature
        $this->db->where_not_in('block_type', ['statement', 'file', 'signature','thank-you']);
        $this->db->order_by('block_order', 'asc');
        $query = $this->db->get();
        $pre_questions = $query->result_array();
        $pre_questions[] = $block;
        return $pre_questions;
    }

    /**
     * @param $block
     * @return array|array[]
     */
    public function get_next_blocks($block)
    {
        //block_form_id
        $form_id = $block['form_id'];
        $this->db->select('*');
        $this->db->from(db_prefix() . $this->table);
        $this->db->where('form_id', $form_id);
        $this->db->where('block_order >', $block['block_order']);
        $this->db->order_by('block_order', 'asc');
        $query = $this->db->get();
        return $query->result_array();
    }
}