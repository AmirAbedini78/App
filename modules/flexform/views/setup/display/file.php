<div class="">
    <?php echo $this->load->view('partials/title-label', ['block' => $block], true); ?>
    <?php echo $this->load->view('partials/description-label', ['block' => $block], true); ?>
    <div class="tw-mb-4">
        <?php echo $this->load->view('partials/file-upload', ['block' => $block], true); ?>
    </div>
    <?php echo $this->load->view('partials/submit-button', ['block' => $block,'is_submit'=>$is_submit], true); ?>
</div>