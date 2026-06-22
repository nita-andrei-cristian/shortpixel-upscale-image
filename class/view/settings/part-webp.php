<?php
namespace SPUI;

if ( ! defined( 'ABSPATH' ) ) {
 exit; // Exit if accessed directly.
}

use \SPUI\Helper\UiHelper as UiHelper;

?>

<?php
// @todo This mess should be unmessed
$spui_deliver_webp_altered_disabled   = '';
$spui_deliver_webp_unaltered_disabled = '';
$spui_deliver_webp_altered_disabled_notice = false;
$spui_deliver_webp_unaltered_label    = '';
$spui_deliver_avif_label              = '';
$spui_avif_disabled                   = false;

if ( $this->is_nginx ) {
    $spui_deliver_webp_unaltered_disabled = 'disabled';
    $spui_deliver_webp_unaltered_label = __( 'It looks like you\'re running your site on an NGINX server. This means that you can only achieve this functionality by directly configuring the server config files. Please follow this link for instructions:', 'shortpixel-upscale-image' ) . " <a class=\"shortpixel-help-link\" href=\"https://shortpixel.com/knowledge-base/article/configure-nginx-to-transparently-serve-webp-files-when-supported/\" target=\"_blank\" data-beacon-article=\"5bfeb9de2c7d3a31944e78ee\"><span class=\"dashicons dashicons-editor-help\"></span></a>";
    $spui_deliver_avif_label = '<strong>' . esc_html__( 'It looks like you\'re running your site on an NGINX server. You may need additional configuration for the AVIF delivery to work as expected', 'shortpixel-upscale-image' ) . '</strong>' . " <a class=\"shortpixel-help-link\" href=\"https://shortpixel.com/knowledge-base/article/how-do-i-configure-my-web-server-to-deliver-avif-images/\" target=\"_blank\"><span class=\"dashicons dashicons-editor-help\"></span></a>";
} else {
    if ( ! $this->is_htaccess_writable ) {
        $spui_deliver_webp_unaltered_disabled = 'disabled';
        if ( 3 === (int) $view->data->deliverWebp ) {
            $spui_deliver_webp_altered_disabled = 'disabled';
            $spui_deliver_webp_unaltered_label = __( 'It looks like you recently moved from an Apache server to an NGINX server, while the option to use .htacces was in use. Please follow this tutorial to see how you could implement by yourself this functionality, outside of the WP plugin: ', 'shortpixel-upscale-image' ) . '<a href="https://shortpixel.com/knowledge-base/article/configure-nginx-to-transparently-serve-webp-files-when-supported/" target="_blank" data-beacon-article="5bfeb9de2c7d3a31944e78ee"></a>';
        } else {
            $spui_deliver_webp_unaltered_label = __( 'It looks like your .htaccess file cannot be written. Please fix this and then return to refresh this page to enable this option.', 'shortpixel-upscale-image' );
        }
    } elseif ( isset( $_SERVER['HTTP_USER_AGENT'] ) && strpos( sanitize_text_field( wp_unslash( $_SERVER['HTTP_USER_AGENT'] ) ), 'Chrome' ) !== false ) {
        // Show a message about the risks and caveats of serving WEBP images via .htaccess
        $spui_deliver_webp_unaltered_label = '<span style="color: initial;">'. esc_html__('Based on testing your particular hosting configuration, we determined that your server','shortpixel-upscale-image').
            '&nbsp;<img alt="can or can not" src="'. esc_url(plugins_url( 'res/img/test.jpg' , SPUI_PLUGIN_FILE)) .'">&nbsp;'.
            esc_html__('serve the WebP or AVIF versions of the JPEG files seamlessly, via .htaccess.','shortpixel-upscale-image').' <a href="https://shortpixel.com/knowledge-base/article/delivering-webp-images-via-htaccess/" target="_blank" data-beacon-article="5c1d050e04286304a71d9ce4">Open article to read more about this.</a></span>';
    }
}

?>

<section id="tab-webp" class="<?php echo ($this->display_part == 'webp') ? 'active setting-tab' :'setting-tab'; ?>" data-part="webp" >

<settinglist>

  <h2><?php esc_html_e('Deliver Next Generation Images & CDN','shortpixel-upscale-image');?></h2>

  <!-- next generation -->
  <setting class='switch step-highlight-3'>

    <content>

      <?php $this->printSwitchButton(
            ['name' => 'createWebp',
             'checked' => $view->data->createWebp,
             'label' => esc_html__('Create WebP Images','shortpixel-upscale-image'),
             'data' => ['data-dashboard="' . __('WebP or AVIF files are not generated', 'shortpixel-upscale-image') . '"'],
            ]);
      ?>
        <i class='documentation dashicons dashicons-editor-help' data-link="https://shortpixel.com/knowledge-base/article/how-to-serve-webp-files/?target=iframe"></i>

      </content>
      <info><?php
      /* translators: 1: Opening WebP info link. 2: Closing WebP info link. 3: Opening Unlimited plan link. 4: Closing Unlimited plan link. */
      printf( wp_kses_post( __( 'Generate %1$sWebP versions%2$s of images. Each image or thumbnail will use an additional credit unless you are on the %3$sUnlimited plan.%4$s', 'shortpixel-upscale-image' ) ), '<a href="https://shortpixel.com/blog/how-webp-images-can-speed-up-your-site/" target="_blank">', '</a>', '<a href="https://shortpixel.com/knowledge-base/article/how-does-the-unlimited-plan-work/" target="_blank">', '</a>' );
      ?></info>
  </setting>
  <!-- /next generation -->

  <!-- avif -->
  <setting class='switch step-highlight-3'>

      <content>
        <?php
          $spui_avif_enabled = $this->access()->isFeatureAvailable('avif');
          $spui_create_avif_checked = ( 1 === (int) $view->data->createAvif && true === $spui_avif_enabled ) ? 1 : 0;
          $spui_avif_disabled = ( false === $spui_avif_enabled );
          $spui_avif_enabled_notice = false;
          if ( false === $spui_avif_enabled )
          {
             $spui_avif_enabled_notice = '<div class="sp-notice sp-notice-warning avifNoticeDisabled">';
             $spui_avif_enabled_notice .=  esc_html__( 'The creation of AVIF files is not possible with this license type.', 'shortpixel-upscale-image' );
             $spui_avif_enabled_notice .=  '<div class="spui-inline-help"><span class="dashicons dashicons-editor-help" title="Click for more info" data-link="https://shortpixel.com/knowledge-base/article/how-does-the-unlimited-plan-work/"></span></div>';
             $spui_avif_enabled_notice .= '</div>';
          }
        ?>
        <?php $this->printSwitchButton(
              ['name' => 'createAvif',
               'checked' => $spui_create_avif_checked,
               'label' => esc_html__('Create AVIF Images','shortpixel-upscale-image'),
               'disabled' => $spui_avif_disabled,
               'data' => ['data-dashboard="' . __('WebP or AVIF files are not generated', 'shortpixel-upscale-image') . '"'],
              ]);
        ?>

       <i class='documentation dashicons dashicons-editor-help' data-link="https://shortpixel.com/knowledge-base/article/how-to-create-and-serve-avif-files-using-shortpixel-image-upscaler/?target=iframe"></i>


       <?php if ( strlen( $spui_deliver_avif_label ) ) { ?>
                    <p class="sp-notice sp-notice-warning">
                   <?php echo wp_kses_post( $spui_deliver_avif_label );?>
                    </p>
       <?php } ?>
       <?php if ( false !== $spui_avif_enabled_notice ) { echo wp_kses_post( $spui_avif_enabled_notice ); } ?>

      </content>
      <info>
         <?php
         /* translators: 1: Opening AVIF info link. 2: Closing AVIF info link. 3: Opening Unlimited plan link. 4: Closing Unlimited plan link. */
         printf( wp_kses_post( __( 'Generate %1$sAVIF versions%2$s of images. Each image or thumbnail will use an additional credit unless you are on the %3$sUnlimited plan.%4$s', 'shortpixel-upscale-image' ) ), '<a href="https://shortpixel.com/blog/what-is-avif-and-why-is-it-good/" target="_blank">', '</a>', '<a href="https://shortpixel.com/knowledge-base/article/how-does-the-unlimited-plan-work/" target="_blank">', '</a>' );
         ?>
      </info>
  </setting>
  <!-- // avif -->


<?php

  if (true === apply_filters('spui/settings/allow_cdn', true)): ?>
    <setting class='switch step-highlight-3'>
      <content>
    <?php
        $spui_input_class = ( true == $view->is_wpoffload ) ? 'switch is-wpoffload' : 'switch';
        $this->printSwitchButton(
          ['name' => 'useCDN',
           'checked' =>  ($view->data->useCDN > 0) ? 1 : 0,
           'label' => esc_html__('Deliver the next generation images using the ShortPixel CDN:','shortpixel-upscale-image'),
           'input_class' => $spui_input_class,
           'data' => ['data-toggle="useCDN"', 'data-exclude="deliverWebp"', 'data-dashboard="' . __('Next generation images are not delivered', 'shortpixel-upscale-image') . '"', ],
          ]);
    ?>

    <i class='documentation dashicons dashicons-editor-help' data-link="https://shortpixel.com/knowledge-base/article/deliver-webp-avif-images-using-the-shortpixel-cdn/?target=iframe"></i>

    </content>
    <?php echo wp_kses_post( UiHelper::getIcon( 'res/images/icon/new.svg' ) ); ?>
    <info>
           <?php
           /* translators: 1: Opening associated domains link. 2: Closing associated domains link. 3: Opening CDN docs link. 4: Closing CDN docs link. */
           printf( wp_kses_post( __( 'When enabled, the plugin replaces images with CDN URLs and delivers next-generation formats (e.g. WebP, AVIF, if enabled above). Otherwise, images are served locally, as usual. For this delivery method to work, your %1$sdomain will be associated%2$s automatically to your ShortPixel account. %3$sRead more%4$s.', 'shortpixel-upscale-image' ) ), '<a href="https://shortpixel.com/associated-domains" target="_blank">', '</a>', '<a href="https://shortpixel.com/knowledge-base/article/deliver-webp-avif-images-using-the-shortpixel-cdn/" target="_blank">', '</a>' );
           ?>
    </info>

    <?php
    $spui_cdn_domain = $view->data->CDNDomain;
    // in 6.0 original release, the other domain was used. This was changed. At some point this can be removed.
    if ( 'https://cdn.shortpixel.ai/spio' == $spui_cdn_domain || 'https://cdn.shortpixel.ai/spui' == $spui_cdn_domain )
    {
       $spui_cdn_domain = 'https://spcdn.shortpixel.ai/spui';
    }
    elseif ( 'https://spcdn.shortpixel.ai/spio' == $spui_cdn_domain )
    {
       $spui_cdn_domain = 'https://spcdn.shortpixel.ai/spui';
    }
    ?>


    <name class='useCDN toggleTarget'><?php esc_html_e('CDN Domain', 'shortpixel-upscale-image'); ?></name>
    <content class='useCDN toggleTarget'>
        <input type="text" name="CDNDomain" class='regular-text' value="<?php echo esc_attr( $spui_cdn_domain ); ?>" >
        <i class='documentation dashicons dashicons-editor-help' data-link="https://shortpixel.com/knowledge-base/article/how-to-serve-the-images-from-a-custom-domain/?target=iframe"></i>
    </content>
    <info class='useCDN toggleTarget'>
           <?php
           /* translators: 1: Opening custom domain link. 2: Closing custom domain link. 3: Default ShortPixel CDN URL. */
           printf( wp_kses_post( __( 'Change this only if you want to set up your %1$scustom domain%2$s. ShortPixel CDN: %3$s', 'shortpixel-upscale-image' ) ), '<a href="https://shortpixel.com/knowledge-base/article/how-to-serve-the-images-from-a-custom-domain/" target="_blank">', '</a>', 'https://spcdn.shortpixel.ai/spui' );
           ?>
    </info>

    <?php if($this->view->is_wpoffload)
    {
       ?>
        <warning class="cdn-offload">
          <?php esc_html_e( 'It looks like you have the Offload Media plugin enabled. Please note that the CDN delivery will not work because usually Offload Media is taking care of this. We recommend you to disable the CDN delivery in this case.', 'shortpixel-upscale-image' );
          ?>
    </warning>
       <?php 
    }
    ?>
  </setting>


<gridbox class="width_half">

	<setting class="useCDN toggleTarget">
			<content>
	<?php
	$this->printSwitchButton(
				 ['name' => 'cdn_css',
					'checked' =>  ($view->data->cdn_css > 0) ? 1 : 0,
					'label' => esc_html__('Minify the CSS, replace  background image URLs and serve the CSS files from the CDN, as well as all the locally referred fonts.','shortpixel-upscale-image'),

			//		'data' => ['data-toggle="useCDN"' ],
					'disabled' => false,
					'switch_class' => '',
				 ]);

		?>

		</content>
	</setting>
	<setting class="useCDN toggleTarget">
		<content>
		<?php
		$this->printSwitchButton(
					 ['name' => 'cdn_js',
						'checked' =>  ($view->data->cdn_js > 0) ? 1 : 0,
						'label' => esc_html__('Minify and serve the JavaScript files from the CDN. The JS files from other domains are not affected by this option.','shortpixel-upscale-image'),

	//s					'data' => ['data-toggle="useCDN"'],
						'disabled' => false,
						'switch_class' => '',
					 ]);

			?>
		</content>
		</setting>
</gridbox>

<?php
// sadly this field need to be present, because of field checks
else:

 $this->printSwitchButton(
        ['name' => 'useCDN',
         'checked' =>  ($view->data->useCDN > 0) ? 1 : 0,
         'label' => esc_html__('Deliver the next generation images using the ShortPixel CDN:','shortpixel-upscale-image'),

         'data' => ['data-toggle="useCDN"', 'data-exclude="deliverWebp"', 'data-dashboard="' . __('Next generation images are not delivered', 'shortpixel-upscale-image') . '"', ],
         'disabled' => true,
         'switch_class' => 'hidden',
        ]);

   ?>

<?php endif; ?>



<setting class='switch step-highlight-3'>
  <content>

   <?php $this->printSwitchButton(
         ['name' => 'deliverWebp',
          'checked' =>  ($view->data->deliverWebp > 0) ? 1 : 0,
          'label' => esc_html__('Serve WebP/AVIF images from locally hosted files (without using a CDN):','shortpixel-upscale-image'),
          'disabled' => $spui_avif_disabled,
          'data' => ['data-toggle="deliverTypes"', 'data-dashboard="' . __('Next generation images are not delivered', 'shortpixel-upscale-image') . '"', 'data-exclude="useCDN" data-hidewarnings'],
         ]);
   ?>

   <i class='documentation dashicons dashicons-editor-help' data-link="https://shortpixel.com/knowledge-base/article/which-webp-files-delivery-method-is-the-best-for-me/?target=iframe"></i>

   <info>
         <?php
         /* translators: 1: Opening local delivery docs link. 2: Closing local delivery docs link. */
         printf( wp_kses_post( __( 'Local delivery skips the CDN and serves next-generation files directly from your website using either the PICTURE tag method or .htaccess/nginx rules. %1$sRead more%2$s.', 'shortpixel-upscale-image' ) ), '<a href="https://shortpixel.com/knowledge-base/article/which-webp-files-delivery-method-is-the-best-for-me/" target="_blank">', '</a>' );
         ?>
   </info>
      <ul  class="deliverTypes deliverWebpTypes toggleTarget">
          <li>
              <input type="radio" name="deliverWebpType" id="deliverWebpAltered" <?php checked( ($view->data->deliverWebp >= 1 && $view->data->deliverWebp <= 2), true); ?> <?php echo esc_attr( $spui_deliver_webp_altered_disabled );?> value="deliverWebpAltered" data-toggle="deliverAlteringTypesPicture">

            <label for="deliverWebpAltered">
                  <?php esc_html_e('Using the &lt;PICTURE&gt; tag syntax','shortpixel-upscale-image');?>
              </label>

              <ul class="toggleTarget deliverAlteringTypesPicture picture-option-list" >
                  <li>
                      <input type="radio" name="deliverWebpAlteringType" id="deliverWebpAlteredWP" <?php checked(($view->data->deliverWebp == 2), true);?> value="deliverWebpAlteredWP">
                      <label for="deliverWebpAlteredWP" >
                          <?php esc_html_e( 'Only via Wordpress hooks (like the_content, the_excerpt, etc)', 'shortpixel-upscale-image' );?>
                      </label>
                  </li>
                  <li>
                      <input type="radio" name="deliverWebpAlteringType" id="deliverWebpAlteredGlobal" <?php checked(($view->data->deliverWebp == 1),true)?>  value="deliverWebpAlteredGlobal" >
                      <label for="deliverWebpAlteredGlobal">
                          <?php esc_html_e('Global (processes the whole output buffer before sending the HTML to the browser)','shortpixel-upscale-image');?>
                      </label>
                  </li>
              </ul>

              <info>
                   <?php
                   /* translators: 1: Opening strong tag. 2: Closing strong tag. */
                   printf( wp_kses_post( __( 'Each &lt;img&gt; tag will be replaced with a &lt;picture&gt; tag, providing AVIF and WebP versions for browsers that support them. You don\'t need to enable this option if you\'re using the Cache Enabler plugin, as it already handles AVIF and WebP images. %1$sPlease test thoroughly before enabling this option!%2$s If your theme\'s styles depend on the position of your &lt;img&gt; tags, display issues may occur.', 'shortpixel-upscale-image' ) ), '<strong>', '</strong>' );
                   ?>
                  <strong><?php esc_html_e('You can revert to the original state at any time by simply deactivating the option and flushing your WordPress cache.','shortpixel-upscale-image'); ?></strong>
              </info>

          </li>
          <li>
              <hr>
              <input type="radio" name="deliverWebpType" id="deliverWebpUnaltered" <?php checked(($view->data->deliverWebp == 3), true);?> <?php echo esc_attr( $spui_deliver_webp_unaltered_disabled );?> value="deliverWebpUnaltered" data-toggle="deliverAlteringTypesHtaccess">

              <label for="deliverWebpUnaltered">
                  <?php esc_html_e('Without altering the page code (via .htaccess)','shortpixel-upscale-image')?>
              </label>

              <?php if ( strlen( $spui_deliver_webp_unaltered_label ) ) { ?>
                  <p class="sp-notice sp-notice-warning"><strong>
                      <?php echo wp_kses_post( $spui_deliver_webp_unaltered_label );?>
                   </strong>
                  </p>
              <?php } ?>
          </li>
      </ul>
    </content>
    <warning id="deliverAlteringTypesPicture" class="deliverAlteringTypesPicture">
       <message>
<?php esc_html_e( 'Warning: Enabling this method will change the structure of the rendered HTML by enclosing &lt;img&gt; tags inside &lt;picture&gt; tags. In rare cases, this can lead to CSS or JavaScript inconsistencies. Please test thoroughly after activation! If you notice any problems, simply deactivate the option, clear the cache and the HTML code will return to its original state.', 'shortpixel-upscale-image' ); ?>
        </message>
    </warning>
    <warning  class="deliverAlteringTypesHtaccess" >
      <message>
        <?php esc_html_e( 'With this option, depending on the capabilities of the web browser, both WebP/AVIF and the original image are delivered from the same URL. Make sure that the images are delivered directly from your server and not via a CDN which may cache them incorrectly, especially if you are using Cloudflare\'s free plan. Read the article above for more details. When making changes, remember to clear your caches to ensure the updates are applied correctly.', 'shortpixel-upscale-image' ) ?>
      </message>
    </warning>

    <?php if ( $spui_deliver_webp_altered_disabled_notice ) : ?>
    <warning class='is-visible'>
        <message>
            <?php esc_html_e('After you selected the option to deliver the images via .htaccess, the .htaccess file has become inaccessible / read-only. Please make the .htaccess file writable again so that you can continue to set up this option.','shortpixel-upscale-image')?>
        </message>
    </warning>
  <?php endif; ?>
  </setting>

</settinglist>

  <?php $this->loadView('settings/part-savebuttons', false); ?>
</section>
