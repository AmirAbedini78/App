<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head(); ?>
<?php
$i = 0;
$first_block = array();
$form = $props['form'];
?>
<div id="wrapper">
    <div class="content">
        <div class="row">
            <div class="col-md-12">
                <div class="tw-flex tw-justify-between tw-items-center tw-mb-2 sm:tw-mb-4">
                    <h4 class="tw-my-0  tw-self-end">
                        <a href="<?php echo admin_url('flexform'); ?>" class="">
                            <i class="fa fa-arrow-left"></i>
                        </a> |
                        <span class="tw-font-semibold tw-text-lg"> <?php echo $form['name']; ?> </span>
                    </h4>
                    <div>
                        <span class="flexform-publish-status">
                        <?php if($form['published'] == '0'): ?>
                         <?php echo flexform_html_status('0'); ?>
                        <?php else: ?>
                            <?php echo flexform_html_status('1'); ?>
                        <?php endif; ?>
                        </span>
                        <a href="<?php echo admin_url('flexform/responses/' . $form['slug']); ?>"
                           class="btn btn-secondary mright5">
                            <i class="fa-solid fa-layer-group"></i>
                            <?php echo _flexform_lang('responses'); ?>
                        </a>
                        <a href="#"
                           data-toggle="modal"
                           data-target="#flexform_share_modal"
                           class="btn btn-secondary mright5">
                            <i class="fa-solid fa-share-nodes"></i>
                            <?php echo _flexform_lang('share'); ?>
                        </a>
                        <a href="#"
                           data-toggle="modal"
                           data-target="#flexform_design_modal"
                           class="btn btn-secondary mright5">
                            <i class="fa-solid fa-wand-magic-sparkles"></i>
                            <?php echo _flexform_lang('design-and-configuration'); ?>
                        </a>
                        <a href=""
                           id="flexform-publish-form-cta"
                           class="btn btn-primary mright5">
                            <i class="fa fa-cloud-bolt"></i> <?php echo _flexform_lang('publish'); ?>
                        </a>
                    </div>
                </div>
                <div class="panel_s">
                    <div class="panel-body panel-table-full flexform-setup-panel-body">
                        <!-- divide the layout in 3 sections with the middle on been the widest -->
                        <div class="row">
                            <div class="col-sm-3">
                                <div class="tw-bg-white tw-p-2 tw-rounded tw-shadow flexform-setup-panel-body_lhs">
                                    <h5 class=" tw-mb-2"><?php echo _flexform_lang('question_block'); ?>
                                        <a class="pull-right" href=""  data-toggle="modal" data-target="#flexform-add-block-modal">
                                            <i class="fa fa-plus "></i>
                                            <?php echo _flexform_lang('add_new'); ?>
                                        </a>
                                    </h5><br/>
                                    <div class="tw-mb-4 flexform_blocks_list" id="flexform_blocks_list_container">
                                        <?php foreach($blocks as $block):
                                            if($i == 0){ $first_block = $block;}
                                            ?>
                                            <?php echo flexform_get_block_partial($block,$i,$i == 0); ?>
                                        <?php $i++; endforeach;?>
                                    </div>
                                </div>
                            </div>
                            <div class="col-sm-6">
                                <div class="tw-bg-white tw-p-4 tw-rounded tw-shadow flexform-setup-panel-body_middle"
                                     id="flexform_blocks_preview"
                                     data-msg="<?php echo _flexform_lang('middle-panel-is-preview-only-edit-on-the-right') ?>">
                                    <?php if(isset($first_block) && $first_block): ?>
                                        <?php echo flexform_get_display_partial($first_block,false,true,true); ?>
                                    <?php endif; ?>
                                </div>
                               
                            </div>
                            <div class="col-sm-3">
                                <div class="tw-bg-white tw-p-4 tw-rounded tw-shadow flexform-setup-panel-body_rhs">
                                    <?php if(isset($first_block) && $first_block): ?>
                                        <?php echo flexform_get_setup_autosubmitform_partial($first_block); ?>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<input type="hidden" id="flexform-setup-block-type" value="" />
<input type="hidden" id="flexform-id" name="id" value="<?php echo $form['id'] ?>" />
<input type="hidden" id="flexform-ajax-url" value="<?php echo admin_url('flexform/ajax') ?>" />
<?php flexform_init_modal($props); ?>
<?php init_tail(); ?>
</body>

</html>