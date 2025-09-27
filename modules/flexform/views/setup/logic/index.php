<?php
$next_questions = flexform_get_next_blocks($block);
$index = 0;
$existing_logics = flexform_get_block_logics($block['id']);
$other_cases_goto = (isset($existing_logics[0]['other_cases_goto'])) ? $existing_logics[0]['other_cases_goto'] : 0;
?>
<div class="block-logic-content">
    <div class="row">
        <div class="col-sm-12">
            <span class="h4"><?php echo _flexform_lang('logic-for-this-question') ?></span>
            <div data-id="<?php echo $block['id'] ?>" class="tw-flex tw-items-center tw-mb-4 tw-px-3 tw-py-3  tw-rounded tw-shadow ff-each-block active">
                <a href="#" class="tw-text-primary tw-mr-2" data-id="<?php echo $block['id'] ?>">
                    <i class="fa <?php echo $block['static']['icon'] ?> tw-text-primary tw-mr-2"></i>
                    <span> <?php echo flexform_str_limit($block['title']) ?> </span>
                </a>
            </div>
        </div>
    </div>
    <input type="hidden" name="block_id" value="<?php echo $block['id'] ?>" />
    <div class="flexform_block_logic_wrapper">
        <?php if($existing_logics): ?>
            <?php foreach($existing_logics as $logic): ?>
                <?php echo $this->load->view('setup/logic/logic-field', ['block' => $block,'index'=>$index,'logic'=>$logic], true); ?>
                <?php $index++; ?>
            <?php endforeach; ?>
        <?php else: ?>
            <?php echo $this->load->view('setup/logic/logic-field', ['block' => $block,'index'=>$index], true); ?>
        <?php endif; ?>
    </div>
    <div class="row tw-m-2">
        <div class="col-sm-12 text-center">
            <button class="flexform-block-add-logic-cta btn btn-secondary btn-sm" type="button"> <i class="fa fa-plus-circle"></i> <?php echo _flexform_lang('add-logic') ?></button>
        </div>
    </div>
    <div class="row tw-m-2">
        <div class="col-sm-12">
            <h5><?php echo _flexform_lang('in-all-other-cases-goto') ?></h5>
        </div>
        <div class="col-sm-12">
            <select name="other_cases_goto" class="form-control" required>
                <option value=""><?php echo _flexform_lang('please-select') ?></option>
                <?php foreach($next_questions as $question): ?>
                    <option value="<?php echo $question['id']; ?>" <?php echo $other_cases_goto == $question['id'] ? 'selected' : '' ?>><?php echo $question['title']; ?></option>
                <?php endforeach; ?>
            </select>
        </div>
    </div>
</div>