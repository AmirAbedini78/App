<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<nav class="navbar flexform-navigation">
    <div class="container">
        <!-- Brand and toggle get grouped for better mobile display -->
        <div class="navbar-header">
            <button type="button" class="navbar-toggle collapsed" data-toggle="collapse"
                    data-target="#theme-navbar-collapse" aria-expanded="false">
                <span class="sr-only">Toggle navigation</span>
                <span class="icon-bar"></span>
                <span class="icon-bar"></span>
                <span class="icon-bar"></span>
            </button>
            <?php if ($this->input->get('with_logo')) { ?>
            <?php get_dark_company_logo('', 'navbar-brand logo'); ?>
            <?php } ?>
        </div>
    </div>
</nav>