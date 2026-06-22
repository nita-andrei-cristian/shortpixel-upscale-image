<?php
namespace SPUI;

if ( ! defined( 'ABSPATH' ) ) {
 exit; // Exit if accessed directly.
}


?>


<section id="tab-processing" class="<?php echo ($this->display_part == 'processing') ? 'active setting-tab' :'setting-tab'; ?>" data-part="processing" >

<settinglist>

  <h2><?php esc_html_e('Upscaling','shortpixel-upscale-image');?></h2>

  <!-- Upscale Media On Upload -->
  <setting class='switch'>
    <content>

      <?php $this->printSwitchButton(
            ['name' => 'autoMediaLibrary',
             'checked' => $view->data->autoMediaLibrary,
             'label' => esc_html__('Upscale media on upload','shortpixel-upscale-image'),
             'data' => ['data-dashboard="' . __('New images are not upscaled', 'shortpixel-upscale-image') . '"'],
            ]);
      ?>

     <i class='documentation dashicons dashicons-editor-help' data-link="https://shortpixel.com/knowledge-base/article/settings-upscale-media-on-upload/?target=iframe"></i>
      <name>

        <?php esc_html_e('Automatically upscale images after they are uploaded (recommended).','shortpixel-upscale-image');?>

      </name>
    </content>
  </setting>
  <!-- // Upscale -->

  <!-- Background mode -->
  <setting class='switch'>

    <content>
      <?php $this->printSwitchButton(
            ['name' => 'doBackgroundProcess',
             'checked' => $view->data->doBackgroundProcess,
             'label' => esc_html__('Background mode','shortpixel-upscale-image'),
             'data' => ['data-toggle="background_warning"', 'data-dashboard="' . __( 'Background mode is recommended', 'shortpixel-upscale-image' ) . '"'],
            ]);
      ?>

     <i class='documentation dashicons dashicons-editor-help' data-link="https://shortpixel.com/knowledge-base/article/background-processing-using-cron-jobs-in-shortpixel-image-upscaler/?target=iframe"></i>

     <name>
            <?php esc_html_e('Utilize this feature to upscale images without the need to keep a browser window open, using cron jobs.','shortpixel-upscale-image');?>
     </name>

    </content>
    <warning class="background_warning">
        <message>
        <?php esc_html_e( 'I understand that background upscaling may pause if there are no visitors on the website.', 'shortpixel-upscale-image' ); ?>
      </message>
    </warning>
  </setting>

  <!-- Backup -->
    <setting class='switch'>
      <content>

        <?php $this->printSwitchButton(
              ['name' => 'backupImages',
               'checked' => $view->data->backupImages,
               'label' => esc_html__('Backup Originals','shortpixel-upscale-image'),
               'data' => ['data-dashboard="' . __('Backups are strongly recommended!', 'shortpixel-upscale-image') . '"'],
              ]);
        ?>

        <i class='documentation dashicons dashicons-editor-help' data-link="https://shortpixel.com/knowledge-base/article/settings-image-backup/?target=iframe"></i>
        <name>
          <?php esc_html_e('Create a backup of the original images, saved on your server in /wp-content/uploads/ShortpixelBackups/.','shortpixel-upscale-image');?>
        </name>


        <info>
          <?php
          /* translators: 1: Opening backup documentation link. 2: Closing backup documentation link. */
          $spui_backup_info = __( 'You can delete the backup folder at any time, but it is best to %1$skeep a local or cloud copy.%2$s This way, you can easily restore the upscaled files to their originals or upscale the images with a different compression type if needed.', 'shortpixel-upscale-image' );
          printf(
          	wp_kses_post( $spui_backup_info ),
             '<a href="https://shortpixel.com/knowledge-base/article/where-is-the-backup-folder-located/" target="_blank">',
             '</a>'
          );
         ?>
        </info>
      </content>
      <warning id="backup-warning">
        <message>
          <?php esc_html_e('Make sure you have a backup in place. When upscaling, ShortPixel will overwrite your images without recovery, which may result in lost images.', 'shortpixel-upscale-image') ?>
        </message>
      </warning>
    </setting>
  <!-- // Backup -->

  <!-- Custom Media Folders -->
  <setting class='switch'>
    <content>
      <?php $this->printSwitchButton(
            ['name' => 'showCustomMedia',
             'checked' => $view->data->showCustomMedia,
             'label' => esc_html__('Custom Media folders','shortpixel-upscale-image'),
            ]);
      ?>

      <name>
        <?php esc_html_e('Display the Media > Custom Media menu, which allows upscaling of images not listed in the Media Library.','shortpixel-upscale-image');?>

      </name>

    </content>
  </setting>
  <!-- // Custom media Folders -->

</settinglist>

<?php $this->loadView('settings/part-savebuttons', false); ?>


</section>
