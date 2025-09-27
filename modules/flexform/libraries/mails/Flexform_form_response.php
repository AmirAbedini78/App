<?php


defined('BASEPATH') or exit('No direct script access allowed');

class Flexform_form_response extends App_mail_template
{
    protected $for = 'flexform';

    protected $email;
    protected $files;
    protected $form_name;
    protected $form;
    protected $form_id;
    public $slug = 'flexform-form-response';
    public $rel_type = 'flexform';
    /**
     * @var mixed
     */

    public function __construct($email, $form, $files)
    {
        parent::__construct();

        $this->email = $email;
        $this->form_name = $form['name'];
        $this->form_id = $form['id'];
        $this->form = $form;
        $this->files = $files;
    }

    public function build()
    {
        foreach($this->files as $file){
            $this->add_attachment($file);
        }
        $this->set_merge_fields('flexform_response_merge_fields', $this->form);
        $this->to($this->email)->set_rel_id($this->form_id);
    }
}