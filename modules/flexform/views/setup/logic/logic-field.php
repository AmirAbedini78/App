<?php
$next_questions = flexform_get_next_blocks($block);
$index = (isset($index)) ? $index : 0;
$logic = (isset($logic)) ? $logic : [];
$goto = (isset($logic['goto'])) ? $logic['goto'] : 0;
?>
<div class="flexform_block_logic_wrapper_each tw-mb-4" data-index="<?php echo $index; ?>">
    <input type="hidden" name="index[]" value="<?php echo $index; ?>" />
    <a href="" class="pull-right flexform-remove-logic-btn text-danger tw-underline"> <i class="fa-solid fa-trash-can"></i></a>
    <div class="flexform-condition-wrapper">
        <?php if($logic): ?>
            <?php
            $if_block_id = flexformPerfectUnserialize($logic['if_block_id']);
            $if_operator = flexformPerfectUnserialize($logic['if_operator']);
            $if_value = flexformPerfectUnserialize($logic['if_value']);
            $if_next_condition = flexformPerfectUnserialize($logic['next_condition']);
            $limit = count($if_block_id);
            ?>
            <?php for($i=0; $i < $limit; $i++): ?>
                <?php $if_block = flexform_get_block($if_block_id[$i]);
                if(!$if_block) continue;
                ?>
                <?php echo $this->load->view('setup/logic/condition-field-with-operator', [
                        'block' => $if_block,
                        'index'=>$index,
                        'if_block_id'=>$if_block_id[$i],
                        'if_operator'=>$if_operator[$i] ,
                        'if_value'=>$if_value[$i],
                        'if_next_condition'=>($i != 0) ? $if_next_condition[$i-1] : ''
                        ], true); ?>
            <?php endfor; ?>
        <?php else: ?>
            <?php echo $this->load->view('setup/logic/condition-field-with-operator', ['block' => $block,'index'=>$index,'condition'=>$logic], true); ?>
        <?php endif; ?>
    </div>
    <a href="" class="pull-right flexform-add-logic-condition" data-index="<?php echo $index ?>"> <i class="fa fa-plus"></i> <?php echo _flexform_lang('add-condition')?></a>
    <div class="row tw-m-2">
        <div class="col-sm-12">
            <h5><?php echo _flexform_lang('then-goto') ?></h5>
        </div>
        <div class="col-sm-12">
            <select name="logic_<?php echo $index ?>_goto" class="form-control" required>
                <option value=""><?php echo _flexform_lang('please-select') ?></option>
                <?php foreach($next_questions as $question): ?>
                    <option value="<?php echo $question['id']; ?>" <?php echo ($question['id'] == $goto) ? 'selected' : '' ?>><?php echo $question['title']; ?></option>
                <?php endforeach; ?>
            </select>
        </div>
    </div>
</div>