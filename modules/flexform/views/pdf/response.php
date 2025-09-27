<?php

defined('BASEPATH') or exit('No direct script access allowed');

$dimensions = $pdf->getPageDimensions();

$info_right_column = '';
$info_left_column = '';

$info_right_column .= '<span style="font-weight:bold;font-size:27px;">' . _flexform_lang('form-submission-data') . '</span><br />';
$info_right_column .= '<b style="color:#4e4e4e;">' . $form['name'] . '</b><br/>';


// Add logo
$info_left_column .= pdf_logo_url();


// Write top left logo and right column info/text
pdf_multi_row($info_left_column, $info_right_column, $pdf, ($dimensions['wk'] / 2) - $dimensions['lm']);

$pdf->ln(10);

$i = 0;
$dated_added = $responses[$i]['date_added'];
$submitted_info = '<b>' . _flexform_lang('submitted-at') . ':</b>';
$submitted_info .= '<div style="color:#424242;">';
$submitted_info .= $dated_added;
$submitted_info .= '</div>';
$question_answer_submitted = "";
$blocks = flexform_get_all_blocks($form['id']);
foreach($blocks as $b){
    if($b['block_type'] == 'thank-you' || $b['block_type'] == 'statement') {
        continue;
    }
    $question_answer_submitted .= '<br /><b>' . $b['title'] . ':</b> <br/>' . (isset($responses[$i]) ? flexform_render_answer($responses[$i]) : '');
    //add a line break
    $question_answer_submitted .= '<br/><br/>';
    $i++;
}
// Purchased by
$left_info = $question_answer_submitted;
$right_info = $submitted_info;

pdf_multi_row($left_info, $right_info, $pdf, ($dimensions['wk'] / 2) - $dimensions['lm']);
