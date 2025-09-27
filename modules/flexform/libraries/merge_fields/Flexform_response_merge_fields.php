<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Flexform_response_merge_fields extends App_merge_fields
{
    public function build()
    {
        return [
                [
                    'name'      => 'Form Name',
                    'key'       => '{form_name}',
                    'available' => [
                    ],
                    'templates' => [
                        'flexform-form-response',
                    ],
                ],
            ];
    }

    /**
     * Flexibackup event merge fields
     */
    public function format($form)
    {
        $fields['{form_name}']       = isset($form['name']) ? $form['name'] : '';
        return hooks()->apply_filters('flexform_form_reponse_merge_fields', $fields, [
            'event' => $form,
        ]);
    }
}