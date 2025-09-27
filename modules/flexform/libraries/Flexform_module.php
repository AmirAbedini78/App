<?php

class Flexform_module{

    private $ci;

    public function __construct()
    {
        $this->ci = &get_instance();
        $this->ci->load->model('flexform/flexform_model');
        $this->ci->load->model('flexform/flexformcompleted_model');
        $this->ci->load->model('flexform/flexformblockanswer_model');
    }

    public function send_form_data_email(array $emails, string $form_id, string $session_id){
        $this->ci->email->initialize();
        $this->ci->load->library('email');
        //check if form exists
        $form = $this->ci->flexform_model->get(['id' => $form_id]);
        if(!$form){
            return false;
        }
        $responses = flexform_get_response($form_id, $session_id,'complete');

        $pdf = flexform_response_pdf($responses[$session_id],$form);

        $file_path = FLEXFORM_FOLDER . $session_id . '.pdf';
        $pdf->Output($file_path, 'F');
        //get file extension
        $file_extension = pathinfo($file_path, PATHINFO_EXTENSION);

        //get file name without extension
        $file_name = pathinfo($file_path, PATHINFO_FILENAME);
        $response_pdf[] = [
            'attachment' => $file_path,
            'filename' => $file_name,
            'type' => $file_extension,
            'read' => true,
        ];
        $template_name = 'Flexform_form_response'; //this is the email class
        foreach ($emails as $email) {
            $template = mail_template($template_name, "flexform", $email, $form, $response_pdf);
            $template->send();
        }
    }

    public function create_email_template(){
        $template_name = 'flexform-form-response';
        $template_message = 'Hello, <br/><br/> A new form response has been submitted for {form_name}. Please find the attached PDF file for the submission data. <br/><br/> Regards';
        create_email_template('You have a new Form Submission - {form_name}', $template_message,'staff', 'Email Template for Form Submission Data', $template_name);
    }
}