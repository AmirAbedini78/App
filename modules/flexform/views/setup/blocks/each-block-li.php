<div data-id="<?php echo $block['id'] ?>" class="tw-flex tw-items-center tw-mb-4 tw-px-3 tw-py-3  tw-rounded tw-shadow ff-each-block <?php echo (isset($is_active) && $is_active) ? 'active' : '' ?>">
    <a href="#" class="tw-text-primary tw-mr-2 flexform-block__cta" data-id="<?php echo $block['id'] ?>">
        <i class="fa <?php echo $block['static']['icon'] ?> tw-text-primary tw-mr-2"></i>
        <span class="ff-block-index-text"> <?php echo $index.'.'; ?> </span>
        <span class="ff-label"> <?php echo flexform_str_limit($block['title']) ?> </span>
    </a>
    <div class="tw-ml-auto tw-flex ff-block-action">
        <a href="#" class="tw-text-danger tw-mr-2 flexform-delete-block-cta"
           data-id="<?php echo $block['id'] ?>"
           title="<?php echo _flexform_lang('delete'); ?>">
            <i class="fa fa-trash tw-text-danger tw-mr-2"></i>
        </a>
    </div>
</div>