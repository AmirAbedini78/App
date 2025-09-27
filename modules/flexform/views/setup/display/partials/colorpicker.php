<?php
$fsession = isset($form_session) ? $form_session : null;
$default_value = ($preview) ? '' : flexform_get_block_answer($block,$fsession);
echo render_color_picker('answer_'.$block['id'], '', $default_value);