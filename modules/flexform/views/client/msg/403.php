<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php echo $this->load->view('client/navigation'); ?>
<div id="wrapper" class="flexform-client-wrapper">
    <div id="content">
        <div class="container">
            <div class="row">
                <div class="col-md-12">
                    <div class="alert alert-danger flexform-client-wrapper-content tw-p-4">
                        <h3 class="alert-heading tw-mt-0"><?php echo _flexform_lang('permission-error') ?></h3>
                        <p><?php echo _flexform_lang('permission-error-message') ?></p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>