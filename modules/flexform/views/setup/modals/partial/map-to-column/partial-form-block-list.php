<?php $blocks = flexform_get_all_blocks($form['id']);
?>
<select name="block[]" class="form-control">
    <option value="0"><?php echo _flexform_lang('not-mapped'); ?></option>
    <?php foreach($blocks as $block) :
        if($block['block_type'] == 'statement' || $block['block_type'] == 'thank-you'){
            continue;
        }
        ?>
        <option value="<?php echo $block['id']; ?>" <?php echo ($column_key == $block['map_to_column']) ? 'selected' : '' ?>><?php echo $block['title']; ?></option>
    <?php endforeach; ?>
</select>
