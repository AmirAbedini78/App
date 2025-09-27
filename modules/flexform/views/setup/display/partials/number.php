<?php
$fsession = isset($form_session) ? $form_session : null;
$default_value = ($preview) ? '' : flexform_get_block_answer($block,$fsession);
echo render_input('answer_'.$block['id'], '', $default_value, 'number', ['autocomplete' => 'off','min'=>$block['minimum_number'],'max'=>$block['maximum_number']], [], '', 'flexform-question-number');