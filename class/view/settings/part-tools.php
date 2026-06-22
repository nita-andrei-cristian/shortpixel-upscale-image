<?php
namespace SPUI;
use \SPUI\Controller\BulkController as BulkController;
use \SPUI\Helper\UiHelper as UiHelper;

if ( ! defined( 'ABSPATH' ) ) {
 exit; // Exit if accessed directly.
}

$spui_url = esc_url_raw(remove_query_arg('part'));

$spui_bulk = BulkController::getInstance();
$spui_queue_running = $spui_bulk->isAnyBulkRunning();

?>

<section id="tab-tools" class="<?php echo ($this->display_part == 'tools') ? 'active setting-tab' :'setting-tab'; ?>" data-part="tools">


    <settinglist>
      <h2><?php esc_html_e( 'Tools', 'shortpixel-upscale-image' ); ?></h2>

	<p><?php
  /* translators: 1: Opening bold tag. 2: Closing bold tag. */
  printf( wp_kses_post( __( 'The tools below are designed for making bulk changes to your image and upscaling data. It is %1$s highly recommended %2$s to back up your entire website before using them. ', 'shortpixel-upscale-image' ) ), '<b>', '</b>' );
  ?></p>

		<?php if ( $spui_queue_running === true ) : ?>
		<div class='option'>

			<div class='field action queue-warning'>
				 	<?php esc_html_e('It looks like a bulk process is still active. Please note that bulk actions will reset running bulk processes. ', 'shortpixel-upscale-image'); ?>
			 </div>
		</div>
		<?php endif; ?>

        <setting>

            <content>
              <a href="<?php echo esc_url(add_query_arg(array('sp-action' => 'action_debug_redirectBulk', 'bulk' => 'migrate', 'noheader' => true), $spui_url)); ?>" class="button">
                  <?php esc_html_e('Search and Migrate All', 'shortpixel-upscale-image'); ?>
              </a>

                <i class='documentation down dashicons dashicons-editor-help' data-link="https://shortpixel.com/knowledge-base/article/spui-5-tells-me-to-convert-legacy-data-what-is-this/?target=iframe"></i>

                <info>
                  <?php
                  /* translators: 1: Opening bold block with line break. 2: Closing bold tag. */
                  printf( wp_kses_post( __( 'ShortPixel Image Upscaler version 5.0 brings a new format for saving the image upscaling information. If you have upgraded from a version prior to version 5.0, you may want to convert all your image data to the new format. This conversion will speed up the plugin and ensure that all data is preserved. %1$sThis process is also useful for resolving errors that may occur during upscaling due to leftover metadata.%2$s', 'shortpixel-upscale-image' ) ), '<br><b>', '</b>' );
                  ?>
                </info>
            </content>
    <!--        <name>
              <?php esc_html_e('Migrate data', 'shortpixel-upscale-image'); ?>
            </name> -->
        </setting>

        <setting>
          <!--  <name>
              <?php esc_html_e('Clear Queue','shortpixel-upscale-image'); ?>
            </name> -->
            <content>
        				<a href="<?php echo esc_url(add_query_arg(array('sp-action' => 'action_debug_resetQueue', 'queue' => 'all', 'part' => 'tools', 'noheader' => true), $spui_url)); ?>" class="button"><?php esc_html_e('Clear the Queue','shortpixel-upscale-image'); ?></a>

                <info>
                  <?php esc_html_e('Removes all items currently waiting or being processed from all queues. This stops all upscaling processes in the entire installation.', 'shortpixel-upscale-image'); ?>
                </info>
            </content>
        </setting>

        <setting>
        <!--    <name>
              <?php esc_html_e('Clear Upscaling Errors','shortpixel-upscale-image'); ?>
            </name> -->
            <content>
                <a href="<?php echo esc_url(add_query_arg(array('sp-action' => 'action_debug_removePrevented', 'queue' => 'all', 'part' => 'tools', 'noheader' => true), $spui_url)); ?>" class="button"><?php esc_html_e('Clear Upscaling Errors','shortpixel-upscale-image'); ?></a>

                <info>
                  <?php
                  /* translators: 1: Line break. 2: Opening bold tag. 3: Closing bold tag. */
                  printf( wp_kses_post( __( 'Removes the blocks from the items where the upscaling failed for some reason. This usually happens when the plugin is not able to save the backups. %1$s %2$sImportant!%3$s The cause of the error should be fixed, otherwise data corruption may occur.', 'shortpixel-upscale-image' ) ), '<br>', '<b>', '</b>' );
                  ?>
                </info>
            </content>
        </setting>

    </settinglist>

    <h3><?php esc_html_e( 'CDN Tools', 'shortpixel-upscale-image' ); ?></h3>
    <settinglist>

      <setting>
          <name>
          </name>
          <content>
            <button setting-action="PurgeCacheEvent" data-purge="cssjs">
              <?php esc_html_e( 'Purge CSS & JS CDN Cache', 'shortpixel-upscale-image' ); ?>
            </button>

            <info>
                <?php esc_html_e('This cleanup only affects the CSS and JS files when they are processed and served by our CDN. This process is very useful when you update the layout of your website.
', 'shortpixel-upscale-image'); ?>
              </info>
            </content>
        </setting>

      <setting>
          <name></name>    
          <content>

          <button setting-action="PurgeCacheEvent" data-purge="all">
              <?php esc_html_e( 'Purge All','shortpixel-upscale-image' ); ?>
            </button>

            <info>
                <?php esc_html_e('It deletes everything from the CDN: CSS, JS and images. Normally, this process is not needed unless important updates have been made to your website (e.g. theme change, thumbnail regeneration, etc.)', 'shortpixel-upscale-image'); 
                ?>
            </info>
          </content>

      </setting>

      <div id='settings-purge-message' class='tools-message purge-message'>&nbsp;</div>
      
    </settinglist>



    <h3><?php esc_html_e( 'Settings Import / Export', 'shortpixel-upscale-image' ); ?></h3>
    <settinglist class='setting-importexport'>
      <setting>
        <name><?php esc_html_e( 'Export all settings', 'shortpixel-upscale-image' ); ?></name>
        <content>
          <button class='button secondary' setting-action="ExportSettingsEvent"><?php esc_html_e('Export','shortpixel-upscale-image'); ?></button>
        </content>
      </setting>

      <div id='settings-importexport-message' class='tools-message export-message'>&nbsp;</div>

      <setting>
        <name><?php esc_html_e( 'Import settings', 'shortpixel-upscale-image' ); ?></name>
        <content>
            <info><?php esc_html_e( 'Import settings will change all submitted settings', 'shortpixel-upscale-image' ); ?></info>
            <textarea name="import-settings" id='spui-tools-import' class='import-textarea' placeholder="<?php esc_attr_e('Paste settings JSON', 'shortpixel-upscale-image'); ?>">&nbsp;</textarea>
            <br>
            <button setting-action="ImportSettingsEvent"><?php esc_html_e('Import', 'shortpixel-upscale-image'); ?></button>
        </content>
        <warning><message>This will remove all current settings!</message></warning>
      
      </setting>
    </settinglist>

		<hr />





		<div class='danger-zone'>
			<h3><?php esc_html_e('Danger Zone - please read carefully!', 'shortpixel-upscale-image'); ?></h3>
			<p><?php
      /* translators: 1: Opening strong tag. 2: Closing strong tag. */
      printf( wp_kses_post( __( 'The following actions are related to cleaning up and uninstalling the plugin. %1$s They cannot be undone %2$s. It is important that you create a new backup copy before performing any of these actions, as this may result in data loss.', 'shortpixel-upscale-image' ) ), '<strong>', '</strong>' );
      ?></p>
			<hr />

      <settinglist>

       <!-- Bulk Restore -->
       <setting>
         <name>
              <?php esc_html_e('Undo upscaling: Restore all images to original state','shortpixel-upscale-image'); ?>
         </name>
         <content>
           <a href="<?php echo esc_url(add_query_arg(array('sp-action' => 'action_debug_redirectBulk', 'bulk' => 'restore', 'noheader' => true), $spui_url)) ?>" class="button danger"><?php esc_html_e('Bulk Restore', 'shortpixel-upscale-image'); ?></a>

             <i class='documentation down dashicons dashicons-editor-help' data-link="https://shortpixel.com/knowledge-base/article/can-i-restore-my-images-what-happens-with-the-originals/?target=iframe"></i>
           <info>
             <?php
             /* translators: 1: Opening bold tag. 2: Closing bold tag. */
             printf( wp_kses_post( __( '%1$sUndoes%2$s all upscales and restores all your backed-up images to their original state. Credits used will not be refunded and you will have to upscale your images again.', 'shortpixel-upscale-image' ) ), '<b>', '</b>' );
             ?>
           </info>
         </content>
      </setting>

       <!-- Bulk Restore AI DATA -->
       <setting>
         <name>
              <?php esc_html_e('Undo AI generation :  Restore all images to previous state ','shortpixel-upscale-image'); ?>
         </name>
         <content>
           <a href="<?php echo esc_url(add_query_arg(array('sp-action' => 'action_debug_redirectBulk', 'bulk' => 'restoreAI', 'noheader' => true), $spui_url)) ?>" class="button danger"><?php esc_html_e('Bulk Undo AI', 'shortpixel-upscale-image'); ?></a>

           <info>
             <?php
             /* translators: 1: Opening bold tag. 2: Closing bold tag. */
             printf( wp_kses_post( __( '%1$sUndoes%2$s all generated AI Data. Will restore AI Generated fields back to previous state', 'shortpixel-upscale-image' ) ), '<b>', '</b>' );
             ?>
           </info>
         </content>
      </setting>

      <!-- Remove Legacy Data -->
      <setting>
      <!--  <name>
            &nbsp;
        </name> -->
        <content>
						<a href="<?php echo esc_url(add_query_arg(array('sp-action' => 'action_debug_redirectBulk', 'bulk' => 'removeLegacy', 'noheader' => true), $spui_url)); ?>" class="button danger"><?php esc_html_e('Remove Legacy Data', 'shortpixel-upscale-image'); ?></a>

          <info>
            <?php
            /* translators: 1: Opening bold tag. 2: Closing bold tag. */
            printf( wp_kses_post( __( '%1$sRemoves Legacy Data%2$s (the old format for storing image upscaling information in the database, which was used before version 5). This may result in data loss. It is not recommended to do this manually.', 'shortpixel-upscale-image' ) ), '<b>', '</b>' );
            ?>
          </info>
        </content>
     </setting>

     <!-- Remove All Data -->
     <setting>
       <!-- <name>
          &nbsp;
       </name> -->
       <content>
         <button type="button" class='button danger' data-action="open-modal" data-target="ToolsRemoveAll">
                       <?php esc_html_e('Remove all ShortPixel Data', 'shortpixel-upscale-image'); ?></button>

           <i class='documentation down dashicons dashicons-editor-help' data-link="https://shortpixel.com/knowledge-base/article/remove-all-the-shortpixel-related-data-on-a-wp-website/?target=iframe"></i>
         <info>
            <?php
            /* translators: 1: Opening bold tag. 2: Closing bold tag. */
            printf( wp_kses_post( __( '%1$sRemoves all ShortPixel data (including backups) %2$s and deactivates the plugin. Your images will not be changed (the upscaled images will remain), but the next time ShortPixel is activated, it will no longer recognize previous upscales.', 'shortpixel-upscale-image' ) ), '<b>', '</b>' );
            ?>
         </info>
         <div class='remove-all modalTarget' id="ToolsRemoveAll">

           <input type="hidden" name="screen_action" value="toolsRemoveAll" />
           <?php  wp_nonce_field('remove-all', 'tools-nonce'); ?>

           <p>&nbsp;</p>
           <p><?php esc_html_e('This will remove all ShortPixel Data including data about upscaling and image backups.', 'shortpixel-upscale-image'); ?></p>
           <?php esc_html_e('Type confirm to delete all ShortPixel data', 'shortpixel-upscale-image'); ?>
           <input type="text" name="confirm" value=""  data-required='confirm' />

           <p><b><?php esc_html_e('I understand that all ShortPixel data will be removed.','shortpixel-upscale-image'); ?></b></p>

           <button type="button" class='button modal-send' name="uninstall" data-action='ajaxrequest'><?php esc_html_e('Remove all data', 'shortpixel-upscale-image'); ?></button>

         </div> <!-- modal -->
       </content>
    </setting>


    <!-- Remove Backups -->
    <setting>
      <!-- <name>
        &nbsp;
      </name> -->
      <content>
        <button type="button" class='button danger' data-action="open-modal" data-target="ToolsRemoveBackup">
                      <?php esc_html_e('Remove backups', 'shortpixel-upscale-image'); ?></button>

          <i class='documentation down dashicons dashicons-editor-help' data-link="https://shortpixel.com/knowledge-base/article/how-to-remove-the-backed-up-images-in-wordpress/?target=iframe"></i>
        <info>
            <?php esc_html_e('When backups are enabled, original images are stored in a backup folder. If you remove the backup folder, you will not be able to restore or re-upscale the images. We strongly recommend that you keep a copy of the backup folder (/wp-content/uploads/ShortpixelBackups/) somewhere safe.','shortpixel-upscale-image');?>
        </info>
              <?php wp_nonce_field('empty-backup', 'tools-nonce'); ?>

              <div class='remove-backup modalTarget' id="ToolsRemoveBackup">

                <input type="hidden" name="screen_action" value="toolsRemoveBackup" />
                <?php  wp_nonce_field('empty-backup', 'tools-nonce'); ?>

                <p>&nbsp;</p>
                <p><?php esc_html_e('This will delete all the backup images. You won\'t be able to restore from backup or to re-upscale with different settings if you delete the backups.', 'shortpixel-upscale-image'); ?></p>
                <?php esc_html_e('Type confirm to delete all ShortPixel backups', 'shortpixel-upscale-image'); ?>
                <input type="text" name="confirm" value="" data-required='confirm' />

                <p><b><?php esc_html_e('I understand that all Backups will be removed.','shortpixel-upscale-image'); ?>  </b></p>

                <p class='center'>
                  <button type="button" class='button modal-send' name="removebackups" data-action='ajaxrequest'><?php esc_html_e('Remove backups', 'shortpixel-upscale-image'); ?></button>
                </p>
              </div>
           <!-- backup modal -->
      </content>
   </setting>

      </settinglist>




			</div> <!-- danger zone -->
</section>
