<div class="ff-signature-wrapper signature-pad--body">
    <canvas id="signature" height="130" width="550"></canvas>
    <input type="text" style="width:1px; height:1px; border:0px;" tabindex="-1" name="signature<?php echo $block['id'] ?>" id="signatureInput">
    <div class="dispay-block">
        <button type="button" class="btn btn-default btn-xs clear" tabindex="-1" data-action="clear"><?php echo _l('clear'); ?></button>
        <button type="button" class="btn btn-default btn-xs" tabindex="-1" data-action="undo"><?php echo _l('undo'); ?></button>
    </div>
</div>
