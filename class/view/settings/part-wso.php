<?php
namespace ShortPixel;

if ( ! defined( 'ABSPATH' ) ) {
 exit; // Exit if accessed directly.
}

if (true === $view->hide_banner)
{
   return;
}

?>

<section class='wso banner'>
    <span class="image">
      <a href="https://fastpixel.io/?utm_source=SPUI" target="_blank">
      <img src="<?php echo \wpSPIO()->plugin_url() ?>res/img/fastpixel-logo.svg" />
    </a>
    </span>
    <span class="line"><h3>
      <?php printf(__('FAST%sPIXEL%s - the new website accelerator plugin from ShortPixel', 'shortpixel-upscale-image'), '<span class="red">','</span>'); ?>
      </h3>
    </span>
  <!--  <span class="line"><h3>
       <?php printf(__('ALLOW ShortPixel SPECIALISTS TO %s FIND THE  SOLUTION FOR YOU.', 'shortpixel-upscale-image'), '<br>'); ?>
     </h3>
   </span> -->
  <span class="button-wrap">
      <a href="<?php echo esc_url('https://test.fastpixel.io/result/' . parse_url(home_url(), PHP_URL_HOST)); ?>" target="_blank" class='button' ><?php _e('TRY NOW!', 'shortpixel-upscale-image'); ?></a>
  </span>
</section>
