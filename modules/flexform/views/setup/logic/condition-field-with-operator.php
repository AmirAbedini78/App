<?php
$pre_questions = flexform_get_pre_and_current_blocks($block);
$current_block_id = $block['id'];
$index = (isset($index)) ? $index : 0;
$if_block_id = (isset($if_block)) ? $if_block : 0;
$if_operator = (isset($if_operator)) ? $if_operator : '';
$if_value = (isset($if_value)) ? $if_value : '';
$if_next_condition = (isset($if_next_condition)) ? $if_next_condition : '';
$if_block_arr = ($if_block_id) ? flexform_get_block($if_block_id) : [];
$commands = ($if_block_id) ?  flexform_logic_commands($if_block_arr) : flexform_logic_commands($block);
$value_field_block = ($if_block_id) ? $if_block_arr : $block;
?>
<div class="flexform-condition-wrapper_each">
    <div class="row tw-mb-4 flexform-next-condition-wrapper">
        <div class="col-sm-4">
            <select name="logic_<?php echo $index ?>_next_condition[]" class="form-control">
                <option value="and" <?php echo $if_next_condition == 'and' ? 'selected' : '' ?>><?php echo _flexform_lang('and') ?></option>
                <option value="or" <?php echo $if_next_condition == 'or' ? 'selected' : '' ?>><?php echo _flexform_lang('or') ?></option>
            </select>
        </div>
        <div class="col-sm-8">
            <a href="" class="pull-right flexform-remove-next-logic-condition text-muted tw-underline"> <i class="fa fa-trash"></i> <?php echo _flexform_lang('remove')?></a>
        </div>
    </div>
    <div class="row tw-mb-4">
        <div class="col-sm-1">
            <h5><?php echo _flexform_lang('if'); ?></h5>
        </div>
        <div class="col-sm-11">
            <select name="logic_<?php echo $index ?>_if_block[]" class="form-control" onchange="return flexform_logic_if_question_changed(this,<?php echo $index; ?>)">
                <?php foreach($pre_questions as $question): ?>
                    <option value="<?php echo $question['id']; ?>" <?php echo ($if_block_id == $question['id'] || $current_block_id == $question['id']) ? 'selected' : ''?>><?php echo $question['title']; ?></option>
                <?php endforeach; ?>
            </select>
        </div>
    </div>
    <div class="row tw-mb-4 flexform-command-and-form-wrapper">
        <!-- The block here should be the if_block if it is available-->
        <?php echo $this->load->view('setup/logic/block-logic-operator-and-value-field', ['commands' => $commands, 'block' => $value_field_block,'if_value'=>$if_value,'if_operator'=>$if_operator], true); ?>
    </div>
</div>
