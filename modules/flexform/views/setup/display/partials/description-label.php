<?php
$description = $block['description'];;
$pattern = '/(https?:\/\/[^\s]+)/';
$textWithLinks = preg_replace($pattern, '<a href="$1" target="_blank">$1</a>', $description);

?>
<div class="flexform-desc-preview tw-mb-4"><?php echo nl2br($textWithLinks); ?></div>