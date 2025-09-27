<h5 class=" tw-mb-2">
    <?php echo _flexform_lang('content'); ?>
    <?php if(flexform_can_block_have_logic($block)): ?>
        <a href="" class="pull-right flexform-block-logic__cta" data-id="<?php echo $block['id'] ?>">
            <i class="fa fa-puzzle-piece" aria-hidden="true"></i> <?php echo _flexform_lang('logic'); ?>
        </a>
    <?php endif; ?>
</h5>
<br/>
<div class="tw-mb-4 flexform-setup-panel-body_rhs_form_wrapper">
    <?php echo form_open(admin_url('flexform/update_block'),array('id'=>'flexform-block-form','enctype'=>'multipart/form-data')); ?>
    <input type="hidden" id="flexform-block-id" name="id" value="<?php echo isset($block['id']) ? $block['id'] : 0  ?>" />
    <div class="flexform-setup-panel-body_rhs_form_wrapper_inner">

        <div class="form-group tw-mb-4">
            <?php $label = (isset($statement) || isset($thankyou)) ? _flexform_lang('title') : _flexform_lang('question'); ?>
            <?php echo render_input('question', $label, $block['title'], 'text', ['autocomplete' => 'off', 'maxlength' => '250'], [], '', 'flexform-question-title'); ?>
        </div>
        <div class="form-group tw-mb-4">
            <?php echo render_textarea('description', _flexform_lang('description'), $block['description'], [], [], '', 'flexform-question-desc'); ?>
        </div>
        <?php if(!isset($thankyou)): ?>
        <div class="form-group tw-mb-4">
            <?php echo render_input('button_text', _flexform_lang('button_text'), $block['button_text'], 'text', ['autocomplete' => 'off', 'maxlength' => '100'], [], '', 'flexform-question-button-text'); ?>
        </div>
        <?php endif; ?>
        <?php if (isset($placeholder)): ?>
            <?php echo $this->load->view('partials/placeholder', ['block' => $block], true); ?>
        <?php endif; ?>

        <?php if (isset($multiple_choice) || isset($single_choice) || isset($dropdown)): ?>
            <?php echo $this->load->view('partials/options', ['block' => $block], true); ?>
            <?php echo $this->load->view('partials/yes-no', ['block' => $block,'label'=>'randomize-options','column'=>'random','name'=>'random'], true); ?>
            <?php if(!isset($dropdown)): ?>
                <?php echo $this->load->view('partials/yes-no', ['block' => $block,'label'=>'horizontal-align-items','column'=>'horizontal','name'=>'horizontal'], true); ?>
            <?php else: ?>
                <?php echo $this->load->view('partials/yes-no', ['block' => $block,'label'=>'is-country-list','column'=>'is_country','name'=>'is_country'], true); ?>
                <?php echo $this->load->view('partials/ticket-list-types', ['block' => $block,'label'=>'is-ticket-list','column'=>'ticket_list_type','name'=>'ticket_list_type'], true); ?>
            <?php endif; ?>
        <?php endif; ?>

        <?php if (isset($number)): ?>
            <?php echo $this->load->view('partials/min', ['block' => $block], true); ?>
            <?php echo $this->load->view('partials/max', ['block' => $block], true); ?>
        <?php endif; ?>

        <?php if(isset($file)): ?>
            <?php echo $this->load->view('partials/yes-no', ['block' => $block,'label'=>'allow-multiple','column'=>'allow_multiple','name'=>'allow_multiple'], true); ?>
            <?php echo $this->load->view('partials/yes-no', ['block' => $block,'label'=>'simple_uploader','column'=>'simple_uploader','name'=>'simple_uploader'], true); ?>
            <!-- file types -->
            <?php echo render_input('file_types', _flexform_lang('file_types'), $block['file_types'], 'text', ['autocomplete' => 'off', 'maxlength' => '250'], [], '', 'flexform-file-types'); ?>
        <?php endif;?>

        <?php if (isset($star_rating)): ?>
            <?php echo $this->load->view('partials/rating', ['block' => $block], true); ?>
        <?php endif; ?>

        <?php if (isset($opinion_scale)): ?>
            <?php echo $this->load->view('partials/scale', ['block' => $block], true); ?>
        <?php endif; ?>

        <?php if (isset($statement) || isset($thankyou)): ?>
            <?php if(isset($statement)): ?>
                <?php echo $this->load->view('partials/align', ['block' => $block], true); ?>
            <?php endif; ?>
            <?php echo $this->load->view('partials/cover-image', ['block' => $block], true); ?>
        <?php else: ?>
            <?php echo $this->load->view('partials/yes-no', ['block' => $block,'label'=>'is-required','column'=>'required','name'=>'is_required'], true); ?>
        <?php endif; ?>
        <?php if (isset($thankyou)): ?>
            <?php echo render_input('redirect_url', _flexform_lang('redirect_url'), $block['redirect_url'], 'text', ['autocomplete' => 'off', 'maxlength' => '250'], [], '', 'flexform-thankyou-redirect-url'); ?>
            <?php echo render_input('redirect_message', _flexform_lang('redirect_message'), $block['redirect_message'], 'text', ['autocomplete' => 'off', 'maxlength' => '250'], [], '', 'flexform-thankyou-redirect-message'); ?>
            <!--yes-no for confetti -->
            <?php echo $this->load->view('partials/yes-no', ['block' => $block,'label'=>'confetti','column'=>'confetti','name'=>'confetti'], true); ?>
            <!-- redirect delay in seconds -->
            <label for="redirect_delay"><?php echo _flexform_lang('redirect_delay'); ?></label>
            <select class="form-control ff-redirect-delay" name="redirect_delay" id="redirect_delay">
                <?php for($i=1; $i<=10; $i++): ?>
                    <option value="<?php echo $i; ?>" <?php echo ($block['redirect_delay'] == $i) ? 'selected' : ''; ?>><?php echo $i; ?></option>
                <?php endfor; ?>
            </select>
        <?php endif; ?>
    </div>
    <?php echo form_close(); ?>
</div>
