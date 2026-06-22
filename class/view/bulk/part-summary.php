<?php
namespace SPUI;

use SPUI\Helper\UiHelper;

if ( ! defined( 'ABSPATH' ) ) {
 exit; // Exit if accessed directly.
}


?>

<section class="panel summary" data-panel="summary">
  <div class="panel-container">

    <!--<h3 class="heading"><span>
      <?php esc_html_e('ShortPixel Bulk Upscale - Summary','shortpixel-upscale-image'); ?>
    </h3>

    <p class='description'><?php esc_html_e('Welcome to the bulk upscaling wizard, where you can select the images that ShortPixel will upscale in the background for you.','shortpixel-upscale-image'); ?></p>
-->

    <?php 
    
    $this->loadView('bulk/part-progressbar',false, ['part' => 'summary']); ?>

      <h3><?php esc_html_e('Review & Start Processing', 'shortpixel-upscale-image'); ?>
       <!-- <span>
            <img src="<?php echo esc_url(wpSPUI()->plugin_url('res/img/robo-notes.png')); ?>" style="transform: scale(-1, 1);height: 50px;"/>
        </span> -->
      </h3>

  <div class='credits-wrapper summary-list'>
    <div class='credits-sub-wrapper'>
      <!--- ### MEDIA BOX #### --> 
      <div class="section-wrapper" data-check-visibility data-control="data-check-media-total">
      <h4><span class='dashicons dashicons-images-alt2'>&nbsp;</span>
				<?php esc_html_e('Media Library','shortpixel-upscale-image'); ?> (<span data-stats-media="in_queue">0</span> <?php esc_html_e('items','shortpixel-upscale-image'); ?>)</h4>
        <div class="list-table">

						<div  class='images'><span><?php esc_html_e('Images','shortpixel-upscale-image'); ?></span>
								<span data-stats-media="images-images_basecount">n/a</span>
						</div>

            <div class='filetypes' data-check-visibility data-control="data-check-has-webp">
							<span>&nbsp; <?php esc_html_e('+ WebP images','shortpixel-upscale-image'); ?> </span><span data-stats-media="images-images_webp" data-check-has-webp>&nbsp;</span>
						</div>
            <div class='filetypes' data-check-visibility data-control="data-check-has-avif">
							<span>&nbsp; <?php esc_html_e('+ AVIF images','shortpixel-upscale-image'); ?> </span><span data-stats-media="images-images_avif" data-check-has-avif>&nbsp;</span>
						</div>

          <div><h4 class="totals"><?php esc_html_e('Total from Media Library','shortpixel-upscale-image'); ?></h4><span class="totals" data-stats-media="images-total_images_without_ai">0</span></div>

        </div>
      </div>

      <!--- ### CUSTOM BOX #### --> 
    <div class="section-wrapper" data-check-visibility data-control="data-check-custom-total">
    <h4><span class='dashicons dashicons-open-folder'>&nbsp;</span><?php esc_html_e('Custom Media', 'shortpixel-upscale-image') ?> (<span data-stats-custom="in_queue">0</span> <?php esc_html_e('items','shortpixel-upscale-image'); ?>)</h4>
      <div class="list-table">

				<div><span><?php esc_html_e('Images','shortpixel-upscale-image'); ?></span>
					<span data-stats-custom="images-images_basecount">n/a</span>
				</div>

					<div class='filetypes' data-check-visibility data-control="data-check-has-custom-webp" ><span>&nbsp; <?php esc_html_e('+ WebP images','shortpixel-upscale-image'); ?></span>
						<span data-stats-custom="images-images_webp" data-check-has-custom-webp>&nbsp;</span>
					</div>

					<div class='filetypes' data-check-visibility data-control="data-check-has-custom-avif">
						<span>&nbsp; <?php esc_html_e('+ AVIF images','shortpixel-upscale-image'); ?></span><span data-stats-custom="images-images_avif" data-check-has-custom-avif>&nbsp;</span>
					</div>

        <div><h4 class="totals"><?php esc_html_e('Total from Custom Media','shortpixel-upscale-image'); ?></h4><span class="totals" data-stats-custom="images-images">0</span></div>
      </div>
    </div>

  <?php
    $spui_quota_data = $this->view->quotaData;
?>

    <div class="credits">
      <p class='heading totals'><span>
        
        <?php  $spui_quota_data->unlimited ? esc_html_e('Total','shortpixel-upscale-image') : esc_html_e('Total credits needed','shortpixel-upscale-image');
              ?>: 
        </span>
         <span class='hidden' data-stats-total="images-images" data-check-total-total>0</span>
        <span class="number" data-stats-total="images-total_images_without_ai" data-check-total-without-ai >0</span>
      </p>
  <?php 
      if ( true === $spui_quota_data->unlimited ) : ?>

				<p><span><?php esc_html_e( 'This site is currently on the ShortPixel Unlimited plan, so you do not have to worry about credits. Enjoy!', 'shortpixel-upscale-image' ); ?></span></p>
      
      <!--	</div> -->
	    <?php else: ?>
      <p class='heading'><span><?php esc_html_e('Your ShortPixel Credits Available', 'shortpixel-upscale-image'); ?></span>
        <span><b><?php echo esc_html($this->formatNumber($spui_quota_data->total->remaining, 0)) ?></b></span>

      </p>

      <p><span><?php esc_html_e('Your monthly plan','shortpixel-upscale-image'); ?></span>
         <span><b><?php echo esc_html($spui_quota_data->monthly->text) ?></b> |
              <?php esc_html_e('Used:', 'shortpixel-upscale-image'); ?> <b><?php echo esc_html($this->formatNumber($spui_quota_data->monthly->consumed, 0)); ?></b> |
              <?php esc_html_e('Remaining:', 'shortpixel-upscale-image'); ?> <b><?php echo esc_html($this->formatNumber($spui_quota_data->monthly->remaining, 0)); ?></b>
          </span>
      </p>

      <p>
          <span><?php esc_html_e( 'Your one-time credits', 'shortpixel-upscale-image' ) ?></span>
          <span><b><?php echo esc_html($spui_quota_data->onetime->text) ?></b> |
             <?php esc_html_e('Used:', 'shortpixel-upscale-image'); ?> <b><?php echo esc_html($this->formatNumber($spui_quota_data->onetime->consumed, 0)); ?></b> |
             <?php esc_html_e('; Remaining:', 'shortpixel-upscale-image'); ?> <b><?php echo esc_html($this->formatNumber($spui_quota_data->onetime->remaining, 0)) ?></b>
         </span>
      </p>

      <p>	<span>
        <a href="<?php echo esc_url($this->view->buyMoreHref) ?>" target="_new" class='button button-primary unlimited'>
        <span><?php echo wp_kses_post( UIHelper::getIcon('res/images/icon/shortpixel.svg') ); ?></span>
        <?php esc_html_e('Buy unlimited credits','shortpixel-upscale-image'); ?>
        </a></span>
      </p>
      <?php endif;
	    ?>


  </div>
  </div>
  <div class='ai-credits-sub-wrapper'>
    <!--- ### AI BOX #### --> 
    <div class='section-wrapper ai' data-check-visibility data-control="data-check-has-ai">
    <h4><span class='dashicons dashicons-open-folder'>&nbsp;</span><?php esc_html_e('AI Image SEO', 'shortpixel-upscale-image') ?></h4>
      <div class="list-table">

            <div class='' >
							<span>&nbsp; <?php esc_html_e('Images ','shortpixel-upscale-image'); ?> </span><span data-stats-media="images-images_ai" data-check-has-ai>&nbsp;</span>
						</div>
        <div><h4 class="totals"><?php esc_html_e('Total images for AI Image SEO','shortpixel-upscale-image'); ?></h4><span class="totals" data-stats-media="images-images_ai" data-check-has-ai>0</span></div>

      </div>
      
    </div>

    <div class='credits ai' data-check-visibility data-control="data-check-has-ai">

      <p class='heading totals'><span>
        
        <?php   $quotaData->unlimited ? esc_html_e('Total','shortpixel-upscale-image') : esc_html_e('Total AI credits needed','shortpixel-upscale-image');
              ?>: 
        </span>
        <span class="number" data-stats-media="images-images_ai" >0</span>
      </p>

      <?php if ( false === $spui_quota_data->unlimited ) : ?>
      <p>				
        <span>
          <a href="<?php echo esc_url($this->view->buyMoreHref) ?>" target="_new" class='button button-primary unlimited'>
          <span><?php echo wp_kses_post( UIHelper::getIcon('res/images/icon/shortpixel.svg') ); ?></span>
          <?php esc_html_e('Buy Unlimited AI credits','shortpixel-upscale-image'); ?>
          </a>
        </span>
      </p>
      <?php endif;  ?>

  </div>
  </div> <!--- // credits wrapper --> 

  <?php if ( false == $spui_quota_data->unlimited ) : ?>
  <div class="over-quota" data-check-visibility="false" data-control="data-quota-remaining" data-control-check="data-check-total-total">
      <span><img src="<?php echo esc_url(wpSPUI()->plugin_url('res/img/bulk/over-quota.svg')) ?>" /></span>
            <p><?php
            /* translators: 1: Opening red span. 2: Remaining credit count. 3: Closing red span. 4: Bold selected image count element. */
            printf( wp_kses_post( __( 'In your ShortPixel account you %1$shave only %2$s credits available %3$s, but you have chosen %4$s images to be optimized in this bulk process. You can either go back and select less images, or you can upgrade to a higher plan or buy one-time credits.', 'shortpixel-upscale-image' ) ), '<span class="red">', esc_html( $this->formatNumber( $spui_quota_data->total->remaining, 0 ) ), '</span>', '<b data-stats-total="images-images">0</b>' );
            ?>

       <button type="button" class="button" onClick="SPUI.proposeUpgrade();"><?php esc_html_e( 'Show me the best options', 'shortpixel-upscale-image' ) ?></button>
     </p>

       <span class='hidden' data-quota-remaining><?php
             // This is hidden check, no number format.
                echo esc_html($spui_quota_data->total->remaining);
             ?></span>
    </div>
    <?php $this->loadView('snippets/part-upgrade-options'); ?>
    

    <?php endif; // check unlimited ?> 
  
    <div class='no-images' data-check-visibility="false" data-control="data-check-total-total">
        <?php esc_html_e('The current selection contains no images. The bulk process cannot start.', 'shortpixel-upscale-image'); ?>
    </div>

    </div> <!-- // credits --> 
    <nav>
      <button class="button" type="button" data-action="open-panel" data-panel="selection">
				<span class='dashicons dashicons-arrow-left' ></span>
				<p><?php esc_html_e('Back','shortpixel-upscale-image'); ?></p>
			</button>
      <button class="button-primary button" type="button" data-action="StartBulk" data-control="data-check-total-total" data-check-presentation="disable">
				<span class='dashicons dashicons-arrow-right'></span>
        <?php if ($view->customOperationMedia !== false) 
        {
            printf( '%s %s %s', '<p>', esc_html( $view->customOperationMedia ), '</p>' );
        }
        else
        {
          ?>
            <p><?php esc_html_e('Start Bulk Upscaling', 'shortpixel-upscale-image'); ?></p>
          <?php 
        } ?>
				
			</button>
    </nav>
  </div>
</section>
