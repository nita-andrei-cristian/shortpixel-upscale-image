<?php

namespace SPUI;
use SPUI\ShortPixelLogger\ShortPixelLogger as Log;

use SPUI\Helper\UiHelper as UiHelper;

use SPUI\Controller\OtherMediaController as OtherMediaController;


if ( ! defined( 'ABSPATH' ) ) {
 exit; // Exit if accessed directly.
}

// phpcs:ignore WordPress.Security.NonceVerification.Recommended  -- This is not a form
if ( isset($_GET['noheader']) ) {
    require_once(ABSPATH . 'wp-admin/admin-header.php');
}

	 $this->loadView('custom/part-othermedia-top');

	 $spui_folder_url = esc_url(add_query_arg('part', 'folders', $this->url));

	 $spui_other_media_controller = OtherMediaController::getInstance();
?>

<div class='scan-area'>

  <div class='scan-actions'>
		<h2><?php esc_html_e( 'Actions', 'shortpixel-upscale-image' ) ?></h2>
		<p><?php
    /* translators: 1: Opening folders tab link. 2: Closing folders tab link. */
    printf( wp_kses_post( __( 'Scan folders for images that are not yet included in custom media. If you only want to check specific folders, you can do this in the %1$sFolders tab%2$s.', 'shortpixel-upscale-image' ) ), '<a href="' . esc_url( $spui_folder_url ) . '">', '</a>' );
    ?>
		</p>

		<div class='action-scan'>
			<button type="button" name="scan" class='scan-button button button-primary'>
				<?php esc_html_e( 'Refresh all folders', 'shortpixel-upscale-image' ); ?>
			</button>
			<label><?php esc_html_e( 'Refresh all folders since the last refresh time. This is faster.', 'shortpixel-upscale-image' ); ?>
			</label>
		</div>

		<div class='action-scan'>
			<button type="button" name="fullscan" class='scan-button full button button-primary' data-mode="force">
				 <?php esc_html_e( 'Full scan of all folders', 'shortpixel-upscale-image' ); ?>
			</button>
			<label>
				<?php esc_html_e( 'Fully scan all folders and check all files again.', 'shortpixel-upscale-image' ); ?>
			</label>

		</div>

		<div class='action-stop not-visible' >
			<button type="button" name="stop" class="stop-button button">
					<?php esc_html_e( 'Stop Scanning', 'shortpixel-upscale-image' ); ?>
			</button>
			<label>
				<?php esc_html_e( 'Stop current scan process', 'shortpixel-upscale-image' ); ?>
			</label>
		</div>
	</div>

  <div class='output result not-visible'>
			<h2><?php esc_html_e( 'Results', 'shortpixel-upscale-image' ); ?></h2>
			<div class='result-table'>

			</div>
  </div>

  <div class='scan-help'>
    <p><?php
    /* translators: 1: Opening Knowledge Base link. 2: Closing Knowledge Base link. */
    printf( wp_kses_post( __( 'If new images are regularly added to your Custom Media folders from outside WordPress (e.g. via (S)FTP or SSH), you must manually click on "Refresh all folders" so that the new images are recognized and upscaled. Alternatively, you can also set up a regular cron job as described in our %1$s Knowledge Base %2$s.', 'shortpixel-upscale-image' ) ),
    '<a href="https://shortpixel.com/knowledge-base/article/how-to-schedule-a-cron-event-to-run-shortpixel-image-optimizer/" target="_blank">', '</a>'
    ); ?></p>
  </div>


</div> <!-- / scan-area -->

</div> <!--- wrap from othermedia-top -->
