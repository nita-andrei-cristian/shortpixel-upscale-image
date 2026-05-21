<?php
namespace SPUI;
use SPUI\ShortPixelLogger\ShortPixelLogger as Log;
use SPUI\Helper\UiHelper as UiHelper;

if ( ! defined( 'ABSPATH' ) ) {
 exit; // Exit if accessed directly.
}
?>

<hr class="wp-header-end">

<div class="wrap is-shortpixel-bulk-page">
<header>
  <h1>
      <?php echo UIHelper::getIcon('res/images/illustration/logo_settings.svg'); ?>
      <?php //esc_html_e('ShortPixel Plugin Settings','shortpixel-upscale-image');?>
  </h1>

<!--
  <div class='top-buttons'>
    <button><i class='shortpixel-icon notifications'></i><?php _e('Notifications','shortpixel-upscale-image'); ?></button>

  </div>
-->
</header>
<div class="shortpixel-bulk-wrapper">

  <div id="processPaused" class="processor-paused" data-action="ResumeBulk"><span class='dashicons dashicons-controls-pause' data-action="ResumeBulk"></span>
    <?php if (true === \wpSPUI()->settings()->doBackgroundProcess)
    {
        $title = esc_html__('Bulk Processing is paused in this browser and continues to run in the background as long as visitors are on the website','shortpixel-upscale-image');
        $alt = __('Click here to continue processing in this browser, which may be faster', 'shortpixel-upscale-image');
    }
    else {
        $title = esc_html__('The Bulk Processing is paused, please click to resume','shortpixel-upscale-image');
        $alt = '';
    }
    ?>
    <?php echo $title ?>
    <p class='small'><?php echo $alt ?></p>
  </div>

  <div id="processorOverQuota" class="processor-overquota">
			<h3><?php esc_html_e('There are no credits left. The Bulk Processing is paused.','shortpixel-upscale-image'); ?></h3>
			<p><a href="javascript:window.location.reload()"><?php esc_html_e('Click to reload page after adding credits','shortpixel-upscale-image'); ?></a></p>
	</div>

  <div class="screen-wrapper">

  <?php
  $this->loadview('bulk/part-dashboard');
  $this->loadView('bulk/part-selection');
  $this->loadView('bulk/part-summary');
  $this->loadView('bulk/part-process');
  $this->loadView('bulk/part-finished');

  $this->loadView('bulk/part-bulk-special');

  ?>

  </div>

</div> <!-- wrapper -->
