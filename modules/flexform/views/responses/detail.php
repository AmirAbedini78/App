<?php  $i = 0; ?>
<div class="flexform-detail-wrapper">
    <p class="text-center tw-mb-4 tw-text-lg tw-font-semibold"><?php echo $response[$i]['date_added']; ?> (<?php echo time_ago($response[$i]['date_added']); ?>)</p>
    <p class="text-right">
        <button onclick="flexformDownloadModal(this)" class="btn btn-secondary btn-sm"><i class="fa fa-download"></i> <?php echo _flexform_lang('download-as-pdf') ?></button>
    </p>
    <div class="flexform-answers" id="flexformSubmissionAnswers">
        <?php foreach ($all_blocks as $block) : ?>
            <?php if($block['block_type'] == 'thank-you' || $block['block_type'] == 'statement') {
                continue;
            } ?>
            <div class="flexform-detail-block">
                <h5 class="flexform-title-preview tw-mb-4"><i class="fa <?php echo $block['static']['icon'] ?> tw-text-primary tw-mr-2"></i> <?php echo $block['title']; ?></h5>
                <div class=""><?php echo (isset($response[$i])) ? flexform_render_answer($response[$i]) : '' ?></div>
                <br/>
            </div>
            <hr/>
        <?php $i++; endforeach; ?>
    </div>
</div>