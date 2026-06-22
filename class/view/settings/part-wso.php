<?php
namespace SPUI;

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
      <img src="<?php echo esc_url( \wpSPUI()->plugin_url() . 'res/img/fastpixel-logo.svg' ); ?>" />
    </a>
    </span>
    <span class="line"><h3>
      <?php
      /* translators: 1: Opening red highlight span tag. 2: Closing red highlight span tag. */
      $spui_fastpixel_title = __( 'FAST%1$sPIXEL%2$s - the new website accelerator plugin from ShortPixel', 'shortpixel-upscale-image' );
      echo wp_kses_post(
      	sprintf(
      		$spui_fastpixel_title,
      		'<span class="red">',
      		'</span>'
      	)
      );
      ?>
      </h3>
    </span>
  <!--  <span class="line"><h3>
       <?php
       /* translators: %s: Line break tag. */
       printf( esc_html__( 'ALLOW ShortPixel SPECIALISTS TO %s FIND THE  SOLUTION FOR YOU.', 'shortpixel-upscale-image' ), '<br>' );
       ?>
     </h3>
   </span> -->
  <span class="button-wrap">
	      <a href="<?php echo esc_url('https://test.fastpixel.io/result/' . wp_parse_url( home_url(), PHP_URL_HOST )); ?>" target="_blank" class='button' ><?php esc_html_e('TRY NOW!', 'shortpixel-upscale-image'); ?></a>
  </span>
</section>
