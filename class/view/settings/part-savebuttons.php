
<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>

<div class='save-buttons'>
    <button type="submit" class='save'>
        <i class='shortpixel-icon save'></i>
	        <?php esc_html_e('Save', 'shortpixel-upscale-image'); ?>
    </button>
    <button type="submit" class='save-bulk' name='save-bulk' value='check'>
        <i class='shortpixel-icon bulk'></i>
        <?php esc_attr_e('Save and Go to Bulk Process','shortpixel-upscale-image');?>
    </button>


</div>
