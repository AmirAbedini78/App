<div class="modal fade" id="flexform-add-block-modal" tabindex="-1" role="dialog">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title"><?php echo _flexform_lang('add-new-block');  ?></h4>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-sm-4 flexform-setup-block-list scroller tw-px-1">
                        <?php foreach(flexform_blocks() as $type=>$block): ?>
                            <div class="tw-flex tw-items-center tw-mb-4 tw-px-3 tw-py-3  tw-rounded tw-shadow flexform-setup-block-list__item">
                                <a href="#" class="tw-text-primary tw-mr-2 flexform-setup-block-list__cta"
                                   data-key="<?php echo $type ?>"
                                   data-img="<?php echo $block['img'] ?>"
                                   data-heading="<?php echo $block['heading'] ?>"
                                   data-description="<?php echo $block['description'] ?>"
                                >
                                    <i class="fa-solid <?php echo $block['icon'] ?> tw-mr-2"></i> <?php echo $block['name']; ?>
                                </a>
                            </div>
                        <?php endforeach; ?>
                    </div>
                    <div class="col-sm-8 text-center">
                        <div class="tw-bg-white tw-rounded tw-shadow p-4 flexform-setup-block-preview hidden">
                            <h4 class="tw-font-semibold tw-mb-4"></h4>
                            <p class="text-muted tw-p-2"></p>
                            <img src="" alt="" class="img-responsive tw-mt-4" />
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-primary"
                        data-msg="<?php echo _flexform_lang('please-select-a-block') ?>"
                        id="flexform-setup-use-this-block"><?php echo _flexform_lang('use-this-block'); ?></button>
                <button type="button" class="btn btn-default" data-dismiss="modal"><?php echo _l('close'); ?></button>
            </div>
        </div>
    </div>
</div>