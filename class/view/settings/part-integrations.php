<?php
namespace SPUI;

if ( ! defined( 'ABSPATH' ) ) {
 exit; // Exit if accessed directly.
}


?>

<section id="tab-integrations" class="<?php echo ($this->display_part == 'integrations') ? 'active setting-tab' :'setting-tab'; ?>" data-part="integrations" >

<settinglist>
  <h2>
    <?php esc_html_e('Integrations','shortpixel-upscale-image');?>
  </h2>
</settinglist>

  <h3><?php esc_html_e('HTTP AUTH credentials', 'shortpixel-upscale-image') ?></h3>
<settinglist>
  <setting>
      <content>
        <?php if (! defined('SPUI_HTTP_AUTH_USER')): ?>
        <inputlabel>User</inputlabel> <input name="siteAuthUser" type="text" id="siteAuthUser" value="<?php echo( esc_html(wp_unslash($view->data->siteAuthUser )));?>" class="regular-text" placeholder="<?php esc_html_e('User','shortpixel-upscale-image');?>" style="margin-bottom: 8px"><br>
        <inputlabel>Password</inputlabel> <input name="siteAuthPass" type="password" id="siteAuthPass" value="<?php echo( esc_html(wp_unslash($view->data->siteAuthPass )));?>" class="regular-text" placeholder="<?php esc_html_e('Password','shortpixel-upscale-image');?>" style="margin-bottom: 8px">
        <info>
            <?php
            /* translators: 1: Opening strong tag for the "leave these fields empty" text. 2: Closing strong tag. */
            $spui_http_auth_info = __( 'Only fill in these fields if your website\'s front end is not publicly accessible and requires a username and password for visitors to connect. If you\'re unsure, simply %1$sleave these fields empty%2$s. Please note that the CDN delivery method will not work if your site is protected by HTTP AUTH.', 'shortpixel-upscale-image' );
            printf(
            	wp_kses_post( $spui_http_auth_info ),
            	'<strong>',
            	'</strong>'
            );
            ?>
        </info>
        <?php else:  ?>
            <p><?php esc_html_e('The HTTP AUTH credentials have been defined in the wp-config file.', 'shortpixel-upscale-image'); ?></p>
        <?php endif; ?>
      </content>

  </setting>
</settinglist>



<?php
if(! $this->is_curl_installed) {
    echo('<p style="font-weight:bold;color:red">' . esc_html__("Please enable PHP cURL extension for the Cloudflare integration to work.", 'shortpixel-upscale-image') . '</p>' );
}
?>

<h3><?php esc_html_e('Cloudflare', 'shortpixel-upscale-image') ?></h3>

<p>
  <?php esc_html_e("If you are using Cloudflare on your site, we recommend filling in the details below. This allows ShortPixel to work seamlessly with Cloudflare, ensuring that any images upscaled or restored by ShortPixel are automatically updated on Cloudflare as well.",'shortpixel-upscale-image');?>
  <i class="documentation up dashicons dashicons-editor-help" title="Click for more info" data-link="https://shortpixel.com/knowledge-base/article/cloudlfare/?target=iframe"></i>
</p>

<settinglist>
   <setting>
      <content>
      <inputlabel>Zone ID  </inputlabel> <input name="cloudflareZoneID" type="text" id="cloudflare-zone-id" <?php echo(! $this->is_curl_installed ? 'disabled' : '');?>
               value="<?php echo( esc_attr(wp_unslash($view->data->cloudflareZoneID))); ?>"
               class="regular-text">
        <i class='documentation dashicons dashicons-editor-help' data-link="https://shortpixel.com/knowledge-base/article/cloudlfare/?target=iframe"></i>

        <info>
            <?php esc_html_e('You can find this in your Cloudflare account in the "Overview" section for your domain.','shortpixel-upscale-image');?>
        </info>

        <inputlabel>Token</inputlabel> <input name="cloudflareToken" type="text"  id="cloudflare-token" <?php echo(! $this->is_curl_installed ? 'disabled' : '');?>  value="<?php echo esc_attr($view->data->cloudflareToken) ?>" class='regular-text' autocomplete="off">
        <info>
            <?php
            /* translators: 1: Opening Cloudflare token link. 2: Closing Cloudflare token link. 3: Opening Cache Purge permission documentation link. 4: Closing Cache Purge permission documentation link. */
            $spui_cloudflare_token_info = __( 'Enter your %1$s site token %2$s for authentication. This token must have %3$s Cache Purge permission %4$s! ', 'shortpixel-upscale-image' );
            printf(
            	wp_kses_post( $spui_cloudflare_token_info ),
            	'<a href="https://dash.cloudflare.com/profile/api-tokens" target="_blank">',
            	'</a>',
            	'<a href="https://shortpixel.com/knowledge-base/article/using-shortpixel-image-upscaler-with-cloudflare-api-token/" target="_blank">',
            	'</a>'
            );
            ?>
        <a href="https://shortpixel.com/knowledge-base/article/using-shortpixel-image-upscaler-with-cloudflare-api-token/" target="_blank" class="shortpixel-help-link">
              <?php esc_html_e('How to set it up','shortpixel-upscale-image');?>
          </a>
        </info>
     </content>
   </setting>


<!--
   <setting>
      <name><?php esc_html_e('Cloudflare Token', 'shortpixel-upscale-image'); ?>
      </name>
      <content>
        <input name="cloudflareToken" type="text"  id="cloudflare-token" <?php echo(! $this->is_curl_installed ? 'disabled' : '');?>  value="<?php echo esc_attr($view->data->cloudflareToken) ?>" class='regular-text' autocomplete="off">
        <info>
            <?php
            /* translators: 1: Opening Cloudflare token link. 2: Closing Cloudflare token link. 3: Opening Cache Purge permission docs link. 4: Closing Cache Purge permission docs link. */
            printf( wp_kses_post( __( 'Enter your %1$s site token %2$s for authentication. This token needs %3$s Cache Purge permission %4$s! ', 'shortpixel-upscale-image' ) ), '<a href="https://dash.cloudflare.com/profile/api-tokens" target="_blank">', '</a>', '<a href="https://shortpixel.com/knowledge-base/article/using-shortpixel-image-upscaler-with-cloudflare-api-token/" target="_blank">', '</a>' ); ?>
        <a href="https://shortpixel.com/knowledge-base/article/using-shortpixel-image-upscaler-with-cloudflare-api-token/" target="_blank" class="shortpixel-help-link">
              <?php esc_html_e('How to set it up','shortpixel-upscale-image');?>
          </a>
        </info>
     </content>
  </setting>
-->
</settinglist>



<?php $this->loadView('settings/part-savebuttons', false); ?>
</section>
