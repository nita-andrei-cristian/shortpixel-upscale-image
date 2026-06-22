<?php
namespace SPUI;

if ( ! defined( 'ABSPATH' ) ) {
 exit; // Exit if accessed directly.
}

// phpcs:ignore WordPress.Security.NonceVerification.Recommended  -- This is not a form
if ( isset( $_GET['noheader'] ) ) {
    require_once(ABSPATH . 'wp-admin/admin-header.php');
}



$this->loadView('custom/part-othermedia-top');

?>

<!--- add Custom Folder -->
<div class='addCustomFolder'>

  <p class='add-folder-text'><strong><?php esc_html_e('Add a custom folder', 'shortpixel-upscale-image'); ?></strong></p>
  <input type="text" name="addCustomFolderView" id="addCustomFolderView" class="regular-text" value="" disabled >&nbsp;

  <a class="button open-selectfolder-modal" title="<?php esc_html_e('Select the images folder on your server.','shortpixel-upscale-image');?>" href="javascript:void(0);">
      <?php esc_html_e('Select','shortpixel-upscale-image');?>
  </a>
<input type="submit" name="save" id="saveAdvAddFolder" class="button button-primary hidden" title="<?php esc_html_e('Add this Folder','shortpixel-upscale-image');?>" value="<?php esc_html_e('Add this Folder','shortpixel-upscale-image');?>">
<p class="settings-info">
    <?php
    /* translators: 1: Opening Custom Media list link. 2: Closing Custom Media list link. */
    $spui_custom_media_text = __( 'Use the Select... button to select site folders. ShortPixel will upscale images and PDFs from the specified folders and their subfolders. In the %1$s Custom Media list %2$s, under the Media menu, you can see the upscale status for each image or PDF in these folders.', 'shortpixel-upscale-image' );
    printf(
    	wp_kses_post( $spui_custom_media_text ),
    	'<a href="upload.php?page=wp-short-pixel-custom">',
    	'</a>'
    );
    ?>
</p>

<div class="sp-modal-shade sp-folder-picker-shade" ></div>
    <div class="shortpixel-modal modal-folder-picker shortpixel-hide">
        <div class="sp-modal-title"><?php esc_html_e('Select folder to add','shortpixel-upscale-image');?></div>
        <div class="sp-folder-picker">

        </div>
        <input type="button" class="button button-info select-folder-cancel" value="<?php esc_html_e('Cancel','shortpixel-upscale-image');?>" style="margin-right: 30px;">
        <input type="button" class="button button-primary select-folder" value="<?php esc_html_e('Add','shortpixel-upscale-image');?>" disabled>

        <span class='sp-folder-picker-selected'>&nbsp;</span>
        <div class="folder-message hidden"></div>
        <div class='description'><?php esc_html_e( 'The greyed out folders are either already active in Custom Media folders or part of the WordPress Media Library', 'shortpixel-upscale-image' ); ?></div>
    </div>


    <div class='add-folder-message'>

    </div>
</div> <!-- end of AddCustomFolder -->


<div class='list-overview'>
	<div class='heading'>
		<?php foreach ( $this->view->headings as $spui_heading_name => $spui_heading ) :

          $spui_title_context = ( isset( $spui_heading['title_context'] ) ) ? ' title="' . esc_attr( $spui_heading['title_context'] ) . '"' : '';
		?>
			<span class='heading <?php echo esc_attr( $spui_heading_name );?>' <?php echo wp_kses_post( $spui_title_context ); ?> >
					<?php echo wp_kses_post( $this->getDisplayHeading( $spui_heading ) ); ?>
			</span>

		<?php endforeach; ?>
	</div>

		<?php if (count($this->view->items) == 0) : ?>
			<div class='no-items'> <p>
				<?php
				if ($this->search === false):
					esc_html_e( 'No folders available. ', 'shortpixel-upscale-image' );
				 else:
					 echo esc_html__('Your search query didn\'t result in any images. ', 'shortpixel-upscale-image');
				endif; ?>
			</p>
			</div>

		<?php endif; ?>

		<?php
		foreach ( $view->items as $spui_index => $spui_item ) {
        $this->view->current_item = $spui_item; // not the best pass
        $this->loadView('custom/part-single-folder', false);

		 }?>
				</div> <!-- shortpixel-folders-list -->

	<?php // view -> customerFolders

  $this->loadView('custom/part-othermedia-bottom');
