<?php
namespace SPUI;
use SPUI\ShortPixelLogger\ShortPixelLogger as Log;

if ( ! defined( 'ABSPATH' ) ) {
 exit; // Exit if accessed directly.
}
?>
<div class='top-menu'>

  <div class='links'>

		<?php if (false === $view->is_unlimited): ?>
    <a href="https://shortpixel.com/<?php
        echo esc_attr(($view->key->apiKey ? "login/". $view->key->apiKey . '/spui-unlimited': "pricing"));
    ?>" target="_blank"><?php esc_html_e( 'Buy credits', 'shortpixel-upscale-image' );?></a> |
	  <?php endif; ?>

    <a href="https://shortpixel.com/knowledge-base/" target="_blank"><?php esc_html_e('Knowledge Base','shortpixel-upscale-image');?></a> |
    <a href="https://shortpixel.com/contact" target="_blank"><?php esc_html_e('Contact Support','shortpixel-upscale-image');?></a> |
    <a href="https://shortpixel.com/<?php
        echo esc_attr(($view->key->apiKey ? "login/". $view->key->apiKey . "/dashboard" : "login"));
    ?>" target="_blank">
        <?php esc_html_e('ShortPixel account','shortpixel-upscale-image');?>
    </a>
	    | <a href="mailto:help@shortpixel.com?subject=SPUI Feature Request"><?php esc_html_e('Feature Request', 'shortpixel-upscale-image'); ?>
	    </a>
	    | <a href="https://wordpress.org/support/plugin/shortpixel-upscale-image/reviews/#new-post" target="_blank">   <?php esc_html_e('Rate Us', 'shortpixel-upscale-image'); ?><img src="<?php echo esc_url(\wpSPUI()->plugin_url('res/img/stars.png')); ?>" width="80" /></a>
  </div>


</div>
