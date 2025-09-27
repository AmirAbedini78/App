<?php

defined('BASEPATH') or exit('No direct script access allowed');

include_once(LIBSPATH . 'pdf/App_pdf.php');

class Form_submission_data_pdf extends App_pdf
{
    private $responses;
    private $form;

    public function __construct($responses,$form, $tag = '')
    {
        //call the parent constructor
        parent::__construct();
        $this->responses = $responses;
        $this->form = $form;

        $title = 'Form Submission for ' . $form['name'];

        $this->SetTitle($title);
    }

    public function prepare()
    {
        $this->set_view_vars([
            'responses' => $this->responses,
            'form' => $this->form
        ]);

        return $this->build();
    }

    protected function type()
    {
        return 'flexform';
    }

    protected function file_path()
    {
        $customPath = APP_MODULES_PATH . FLEXFORM_MODULE_NAME . '/views/pdf/response.php';
        if (file_exists($customPath)) {
            $actualPath = $customPath;
        }
        return $actualPath;
    }
}
