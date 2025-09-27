<div class="ff-statement-wrapper <?php echo $block['text_align'] ?>">
    <?php echo $this->load->view('partials/cover', ['block' => $block], true); ?>
    <div class="preview-statement-title tw-mb-4">
        <?php echo $this->load->view('partials/title-label', ['block' => $block], true); ?>
        <?php echo $this->load->view('partials/description-label', ['block' => $block], true); ?>
    </div>
    <?php echo $this->load->view('partials/submit-button', ['block' => $block,'is_submit'=>$is_submit], true); ?>
</div>