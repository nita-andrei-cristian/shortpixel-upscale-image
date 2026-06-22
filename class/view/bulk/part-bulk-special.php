<?php
namespace SPUI;

if ( ! defined( 'ABSPATH' ) ) {
 exit; // Exit if accessed directly.
}

use \SPUI\Controller\BulkController as BulkController;

	$spui_bulk = BulkController::getInstance();
	$spui_queue_running = $spui_bulk->isAnyBulkRunning();
?>

<section class='panel bulk-restore' data-panel="bulk-restore"  >
  <h3 class='heading'>
    <?php esc_html_e("Bulk Restore", 'shortpixel-upscale-image'); ?>
  </h3>


	<div class='bulk-special-wrapper'>

	  <h4 class='warning'><?php esc_html_e('Warning', 'shortpixel-upscale-image'); ?></h4>

	  <p><?php
    /* translators: 1: Opening bold tag. 2: Closing bold tag. 3: Opening bold tag around "all images". 4: Closing bold tag. */
    printf( wp_kses_post( __( 'By starting the %1$s bulk restore %2$s process, the plugin will try to restore %3$s all images %4$s to the original state. All images will become unoptimized.', 'shortpixel-upscale-image' ) ), '<b>', '</b>', '<b>', '</b>' );
    ?></p>

		<p><?php
    /* translators: 1: Opening bold contact link. 2: Closing bold contact link. */
    printf( wp_kses_post( __( 'We recommend users to %1$s contact us %2$s before restoring the images - many times the restoring is not necessary and we can help. But if you choose to continue then we strongly recommend to create a full backup before starting the process.', 'shortpixel-upscale-image' ) ), '<b><a href="https://shortpixel.com/contact" target="_blank">', '</a></b>' );
    ?>
		</p>
				<p class='warning'><?php esc_html_e('It is strongly advised to create a full backup before starting this process.', 'shortpixel-upscale-image'); ?></p>

<?php if ($this->view->approx->custom->has_custom === true) : ?>
					<div class='optiongroup' data-check-visibility data-control="data-check-custom-hascustom">

						<div class='switch_button'>
							<label>
								<input type="checkbox" class="switch" id="restore_media_checkbox" >
								<div class="the_switch">&nbsp; </div>
							</label>
						</div>
						<h4><label for="restore_media_checkbox"><?php esc_html_e('Restore media library','shortpixel-upscale-image'); ?></label></h4>
					</div>


					<div class='optiongroup' data-check-visibility data-control="data-check-custom-hascustom">
						<div class='switch_button'>
							<label>
								<input type="checkbox" class="switch" id="restore_custom_checkbox" value='1' >
								<div class="the_switch">&nbsp; </div>
							</label>
						</div>
						<h4><label for="restore_custom_checkbox"><?php esc_html_e('Restore custom media','shortpixel-upscale-image'); ?></label></h4>
					</div>
<?php endif ?>
		<p class='optiongroup warning hidden' id="restore_media_warn"><?php esc_html_e('Please select one of the options', 'shortpixel-upscale-image'); ?></p>

	  <p class='optiongroup' ><input type="checkbox" id="bulk-restore-agree" value="agree" data-action="ToggleButton" data-target="bulk-restore-button"> <?php esc_html_e('I want to restore all selected images. I understand this action is permanent and nonreversible', 'shortpixel-upscale-image'); ?></p>


	  <nav>
    	<button type="button" class="button" data-action="open-panel" data-panel="dashboard"><?php esc_html_e('Back','shortpixel-upscale-image'); ?></button>

			<button type="button" class="button button-primary disabled" id='bulk-restore-button' data-action="BulkRestoreAll" disabled><?php esc_html_e('Bulk Restore All Images', 'shortpixel-upscale-image') ?></button>

	  </nav>

</div>
</section>


<section class='panel bulk-migrate' data-panel="bulk-migrate"  >
  <h3 class='heading'>
    <?php esc_html_e("Bulk Migrate", 'shortpixel-upscale-image'); ?>
  </h3>

	<div class='bulk-special-wrapper'>

	  <h4 class='warning'><?php esc_html_e('Warning', 'shortpixel-upscale-image'); ?></h4>

	  <p><?php
    /* translators: 1: Opening bold tag. 2: Closing bold tag. 3: Opening bold tag around "all the images". 4: Closing bold tag. */
    printf( wp_kses_post( __( 'By starting the %1$s bulk metadata migration %2$s process, the plugin will try to migrate the old format of optimization information (used by the plugin for versions prior to 5.0) to the new format used from version 5.0 onward for %3$s all the images. %4$s It is possible to have exceptions and some of the image information migration may fail. You should get all the details for these cases at the end of the process, in the Errors section.', 'shortpixel-upscale-image' ) ), '<b>', '</b>', '<b>', '</b>' );
    ?></p>

		<p class='warning optiongroup'><?php esc_html_e('It is strongly advised to create a full backup before starting this process.', 'shortpixel-upscale-image'); ?></p>

	  <p class='optiongroup'><input type="checkbox" id="bulk-migrate-agree" value="agree" data-action="ToggleButton" data-target="bulk-migrate-button"> <?php esc_html_e('I want to migrate the metadata for all images. I understand this action is permanent. I made a backup of my site including images and database.', 'shortpixel-upscale-image'); ?></p>


	  <nav>


	    <button class="button" type="button" data-action="open-panel" data-panel="dashboard"><?php esc_html_e('Back','shortpixel-upscale-image'); ?></button>

			 <button type="button" type="button" class="button disabled button-primary" disabled id='bulk-migrate-button' data-action="BulkMigrateAll"  ><?php esc_html_e('Search and migrate All Images', 'shortpixel-upscale-image') ?>
			 </button>

	  </nav>
	</div>
</section>

<section class='panel bulk-removeLegacy' data-panel="bulk-removeLegacy"  >
  <h3 class='heading'>
    <?php esc_html_e("Bulk remove legacy data", 'shortpixel-upscale-image'); ?>
  </h3>

	<div class='bulk-special-wrapper'>

	  <h4 class='warning'><?php esc_html_e('Warning', 'shortpixel-upscale-image'); ?></h4>

	  <p><?php
    /* translators: 1: Opening bold tag. 2: Closing bold tag. 3: Opening bold tag around "legacy data". 4: Closing bold tag. */
    printf( wp_kses_post( __( 'By starting the %1$s remove legacy metadata %2$s process, the plugin will try to remove all the %3$s legacy data %4$s (that was used by the plugin to store the optimization information in versions earlier than 5.0). If this legacy metadata isn\'t properly migrated or some of the migration failed for any reason, it will be impossible to undo or redo the process. In these cases, the optimization information for images processed with versions earlier than 5.0 could be lost.', 'shortpixel-upscale-image' ) ), '<b>', '</b>', '<b>', '</b>' );
    ?></p>

		<p class='warning optiongroup'><?php esc_html_e('It is strongly advised to create a full backup before starting this process.', 'shortpixel-upscale-image'); ?></p>
	  <p class='optiongroup'><input type="checkbox" id="bulk-migrate-agree" value="agree" data-action="ToggleButton" data-target="bulk-removelegacy-button"> <?php esc_html_e('I want to remove all legacy data. I understand this action is permanent. I made a backup of my site including images and database.', 'shortpixel-upscale-image'); ?></p>


	  <nav>

	    <button type="button" class="button" data-action="open-panel" data-panel="dashboard"><?php esc_html_e('Back','shortpixel-upscale-image'); ?></button>

			 <button type="button" class="button disabled button-primary" disabled id='bulk-removelegacy-button' data-action="BulkRemoveLegacy"  ><?php esc_html_e('Remove all legacy metadata', 'shortpixel-upscale-image') ?></button>

	  </nav>
	</div>
</section>


<section class='panel bulk-restoreAI' data-panel="bulk-restoreAI"  >
  <h3 class='heading'>
    <?php esc_html_e("Bulk Undo AI", 'shortpixel-upscale-image'); ?>
  </h3>


	<div class='bulk-special-wrapper'>

	  <h4 class='warning'><?php esc_html_e('Warning', 'shortpixel-upscale-image'); ?></h4>

	  <p><?php
    /* translators: 1: Opening bold tag. 2: Closing bold tag. 3: Opening bold tag around "all AI-generated texts". 4: Closing bold tag. */
    printf( wp_kses_post( __( 'By starting the %1$s Bulk undo AI %2$s process, the plugin will try to revert %3$s all AI-generated texts %4$s to the original state. This will impact post and post content and metadata of your installation.  ', 'shortpixel-upscale-image' ) ), '<b>', '</b>', '<b>', '</b>' );
    ?></p>

		<p class='warning'><?php esc_html_e('It is strongly advised to create a full backup before starting this process.', 'shortpixel-upscale-image'); ?></p>


	  <p class='optiongroup' ><input type="checkbox" id="bulk-restore-agree" value="agree" data-action="ToggleButton" data-target="bulk-restoreAI-button"> <?php esc_html_e('I want to undo all the AI-generated data. I understand this action is permanent and nonreversible', 'shortpixel-upscale-image'); ?></p>

	  <nav>
    	<button type="button" class="button" data-action="open-panel" data-panel="dashboard"><?php esc_html_e('Back','shortpixel-upscale-image'); ?></button>

			<button type="button" class="button button-primary disabled" id='bulk-restoreAI-button' data-action="BulkUndoAI" disabled><?php esc_html_e('Bulk Undo AI data', 'shortpixel-upscale-image') ?></button>
	  </nav>

</div>
</section>
