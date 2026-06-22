<?php
namespace SPUI;

use SPUI\Controller\Optimizer\OptimizeAiController;
use SPUI\Helper\UiHelper;

if ( ! defined( 'ABSPATH' ) ) {
 exit; // Exit if accessed directly.
}

$spui_approx = $this->view->approx;
?>


<section class='panel selection' data-panel="selection" data-status="loaded" >
  <div class="panel-container">
			<span class='hidden' data-check-custom-hascustom >
				<?php echo  ($this->view->approx->custom->has_custom === true) ? 1 : 0;  ?>
			</span>

	 <?php $this->loadView('bulk/part-progressbar', false,  ['part' => 'selection']); ?>

      <div class='load wrapper' >
         <div class='loading'>
             <span><img src="<?php echo esc_url(\wpSPUI()->plugin_url('res/img/bulk/loading-hourglass.svg')); ?>" /></span>
             <span>
             <p><?php esc_html_e('Please wait, ShortPixel is checking the images to be processed...','shortpixel-upscale-image'); ?><br>
               <span class="number" data-stats-total="total">x</span> <?php esc_html_e('items found', 'shortpixel-upscale-image'); ?></p>
           </span>
         </div>


				 <div class='loading skip'>
					<nav>
					 <span><p><button class='button' data-action="SkipPreparing"><?php esc_html_e( 'Start now', 'shortpixel-upscale-image' ); ?></button></p>

					 </span>
					 <span>
	 						 <p><?php esc_html_e( 'Clicking this button will start upscaling the items added to the queue. The remaining items can be processed in a new bulk. After completion, you can start bulk and the system will continue with the unprocessed images.', 'shortpixel-upscale-image' ); ?></p>
						</span>
					</nav>
				</div>

        <div class='loading overlimit'>
              <p><?php esc_html_e( 'ShortPixel has detected that there are no more resources available during preparation. The plugin will try to complete the process, but may be slower. Increase memory, disable heavy plugins or reduce the number of prepared items per load.', 'shortpixel-upscale-image' ); ?></p>

        </div>
      </div>

       <div class="interface wrapper">

	   <h3 class="heading">
        <?php esc_html_e('ShortPixel Bulk Upscale - Select Images', 'shortpixel-upscale-image'); ?>
      </h3>

      <p class='description'><?php esc_html_e('Select the type of images that ShortPixel should upscale for you.','shortpixel-upscale-image'); ?></p>
				 <div class="option-block">

					<!-- <h2><?php esc_html_e('Optimize:','shortpixel-upscale-image'); ?> </h2> -->
				<!--	 <p><?php
            // translators: 1: Opening bold tag. 2: Closing bold tag. 3: Line break tag.
            printf( wp_kses_post( __( 'ShortPixel has %1$sestimated%2$s the number of images that can still be optimized. %3$sAfter you select the options, the plugin will calculate exactly how many images to optimize.', 'shortpixel-upscale-image' ) ), '<b>', '</b>', '<br />' );
            ?></p>

					 <?php if ($approx->media->isLimited): ?>
						 <h4 class='count_limited'><?php esc_html_e('ShortPixel has detected a high number of images. This estimates are limited for performance reasons. On the next step an accurate count will be produced', 'shortpixel-upscale-image'); ?></h4>
					 <?php endif; ?>
				 -->

	         <div class="media-library optiongroup">
	            <div class='switch_button'>
	              <label>
	                <input type="checkbox" class="switch" id="media_checkbox" checked>
	                <div class="the_switch">&nbsp; </div>
	              </label>
	            </div>


	            <h4><label for="media_checkbox"><?php esc_html_e('Media Library','shortpixel-upscale-image'); ?></label></h4>
	            <div class='option'>
	              <label><?php esc_html_e('Images (estimate)', 'shortpixel-upscale-image'); ?></label>
	              <span class="number" ><?php echo esc_html($spui_approx->media->items) ?></span>
	            </div>

							<?php if (\wpSPUI()->settings()->processThumbnails == 1): ?>
		            <div class='option'>
		              <label><?php esc_html_e('Thumbnails (estimate)','shortpixel-upscale-image'); ?></label> <span class="number" ><?php echo esc_html($spui_approx->media->thumbs) ?> </span>
		            </div>
							<?php endif; ?>
	         </div>


					<?php if (! \wpSPUI()->settings()->processThumbnails): ?>
					<div class='thumbnails optiongroup'>
						<div class='switch_button'>
							<label>
								<input type="checkbox" class="switch" id="thumbnails_checkbox" <?php checked(\wpSPUI()->settings()->processThumbnails); ?>>
								<div class="the_switch">&nbsp; </div>
							</label>
						</div>
							<h4><label for="thumbnails_checkbox"><?php esc_html_e('Process Image Thumbnails','shortpixel-upscale-image'); ?></label></h4>
							<div class='option'>
								<label><?php esc_html_e('Thumbnails (estimate)','shortpixel-upscale-image'); ?></label>
								<span class="number" ><?php echo esc_html($spui_approx->media->thumbs) ?> </span>
							</div>

						<p><?php esc_html_e('It is recommended to process the WordPress thumbnails. These are the small images that are most often used in posts and pages.This option changes the global ShortPixel settings of your site.','shortpixel-upscale-image'); ?></p>

					</div>
				<?php endif; ?>

				<?php
				$spui_optimize_ai_controller = OptimizeAiController::getInstance(); 
				if (true === $spui_optimize_ai_controller->isAiEnabled()):  ?>
			 <div class='ai-images optiongroup'>
				<div class='switch_button'>
				<label>
		               <input type="checkbox" class="switch" id="autoai_checkbox" name="autoai_checkbox"
		                <?php checked(\wpSPUI()->settings()->autoAIBulk); ?>  />
		               <div class="the_switch">&nbsp; </div>
	             </label>
				 <h4><label for="autoai_checkbox">
					<?php
          /* translators: 1: Opening AI settings link. 2: Closing AI settings link. */
          printf( wp_kses_post( __( 'Use ShortPixel AI to generate image SEO data for all Media Library images, according to the %1$ssettings%2$s', 'shortpixel-upscale-image' ) ), '<a href="options-general.php?page=shortpixel-upscale-settings&part=ai">', '</a>' );
          ?>
              				<span class='new'><?php esc_html_e( 'New!', 'shortpixel-upscale-image' ); ?></span>
				 </label></h4>

				</div>	

				<div class='switch_button indent'>
				<label>
		               <input type="checkbox" class="switch" id="aipreserve_checkbox" name="aipreserve_checkbox"
		                <?php checked(\wpSPUI()->settings()->aiPreserve); ?>  />
		               <div class="the_switch">&nbsp; </div>
	             </label>
				 <h4><label for="aipreserve_checkbox">
					<?php esc_html_e( 'Prevent overriding any of the existing data with the one generated by AI', 'shortpixel-upscale-image' ); ?>
              				<span class='new'><?php esc_html_e( 'New!', 'shortpixel-upscale-image' ); ?></span>
				 </label></h4>

				</div>	

			 </div>

			<?php endif ?>
			
	         <div class="custom-images optiongroup"  data-check-visibility data-control="data-check-custom-hascustom" >
	           <div class='switch_button'>
	             <label>
	               <input type="checkbox" class="switch" id="custom_checkbox" checked>
	               <div class="the_switch">&nbsp; </div>
	             </label>
	           </div>
	           <h4><label for="custom_checkbox"><?php esc_html_e('Custom Media images','shortpixel-upscale-image') ?></label></h4>
	            <div class='option'>
	              <label><?php esc_html_e('Images (estimate)','shortpixel-upscale-image'); ?></label>
	               <span class="number" ><?php echo esc_html($spui_approx->custom->images) ?></span>
	            </div>
	         </div>

<!--
			<div class='maximum-items'> 
			<div class='switch_button'>
			<br>
				<div class='switch_button'>
	             <label>
	               <input type="checkbox" class="switch" id="limit_items" name='limit_items' >
	               <div class="the_switch">&nbsp; </div>
				   <?php
           // translators: %s: HTML input for the limit count.
           printf( wp_kses_post( __( 'Limit Items to %s and then start', 'shortpixel-upscale-image' ) ), 
				'<input type="text" name="limit_numitems" value="1000">'); ?>
				</div>	
				</label>
	           </div>

			</div>
			-->			

				</div> <!-- // option top block -->

				<div class='wrap-collap'>

				<input type="checkbox" id="advanced-settings" class='collap-checkbox'>       

				<label for="advanced-settings" class='advanced-label'>     
					<span class='collap-arrow'><?php echo wp_kses_post( UIHelper::getIcon('res/images/icon/chevron.svg') ); ?></span> 
					<span class='title'><?php esc_html_e('Advanced Settings', 'shortpixel-upscale-image'); ?></span>
					<hr>
				</label>

				<div class='collap-content'>

				<div class="option-block selection-settings">
					 <h2><?php esc_html_e('Options','shortpixel-upscale-image') ?>: </h2>
						 <p><?php esc_html_e('Enable these options if you also want to create WebP/AVIF files. These options change the global ShortPixel settings of your site.','shortpixel-upscale-image'); ?></p>
		         <div class='optiongroup'  >
		           <div class='switch_button'>

		             <label>
		               <input type="checkbox" class="switch" id="webp_checkbox" name="webp_checkbox"
		                <?php checked(\wpSPUI()->settings()->createWebp); ?>  />
		               <div class="the_switch">&nbsp; </div>
		             </label>

		           </div>
			   <h4><label for="webp_checkbox">
					 <?php printf(esc_html__('Also create WebP versions of the images' ,'shortpixel-upscale-image') ); ?>
				 </label></h4>
				<div class="option"><?php esc_html_e('The total number of WebP images will be calculated in the next step.','shortpixel-upscale-image'); ?></div>
		       </div>


					 <?php
					 $spui_avif_enabled = $this->access()->isFeatureAvailable('avif');
					 $spui_create_avif_checked = (\wpSPUI()->settings()->createAvif == 1 && $spui_avif_enabled === true) ? true : false;
					 $spui_disabled = ($spui_avif_enabled === false) ? 'disabled' : '';
					 ?>


		       <div class='optiongroup'>
		         <div class='switch_button'>

		           <label>
		             <input type="checkbox" class="switch" id="avif_checkbox" name="avif_checkbox" <?php echo esc_attr( $spui_disabled ); ?>
		              <?php checked($spui_create_avif_checked); ?>  />
		             <div class="the_switch">&nbsp; </div>
		           </label>

		         </div>
		         <h4><label for="avif_checkbox"><?php esc_html_e('Also create AVIF versions of the images','shortpixel-upscale-image'); ?></label></h4>
				<?php if ($spui_avif_enabled == true): ?>
				<div class="option"><?php esc_html_e('The total number of AVIF images will be calculated in the next step.','shortpixel-upscale-image'); ?></div>
		     </div>
			<?php else : ?>
				<div class="option warning"><?php
          /* translators: 1: Opening AVIF license documentation link. 2: Closing link. */
          printf( wp_kses_post( __( 'The creation of AVIF files is not possible with this license type. %1$s Read more %2$s ', 'shortpixel-upscale-image' ) ), '<a href="https://shortpixel.com/knowledge-base/article/how-does-the-unlimited-plan-work/" target="_blank">', '</a>' );
          ?>
				</div>
			<?php endif;  ?>

        <div class='optiongroup'>
          <div class='switch_button'>

            <label>
              <input type="checkbox" class="switch" id="background_checkbox" name="background_checkbox"
               <?php checked(\wpSPUI()->settings()->doBackgroundProcess); ?>  data-action="ChangeBackgroundProcessSettingEvent" data-event="change"/>
              <div class="the_switch">&nbsp; </div>
            </label>

          </div>
          <h4><label for="background_checkbox">

            <?php esc_html_e( 'Background Mode' ,'shortpixel-upscale-image' ); ?>
          </label></h4>
            <?php $link = 'https://shortpixel.com/knowledge-base/article/background-processing-using-cron-jobs-in-shortpixel-image-optimizer/'; ?>
         <div class="option"><?php
         /* translators: 1: Opening background mode documentation link wrapped in strong tag. 2: Closing link and strong tags. */
         printf( wp_kses_post( __( 'Utilize this feature to upscale images without the need to keep a browser window open. Please be aware that on websites with low traffic or shared hosting, this method of upscaling might be considerably slower. If you observe a significant increase in server resource usage or processing time, consider switching to browser-based processing. %1$sRead more%2$s.', 'shortpixel-upscale-image' ) ), '<strong><a href="' . esc_attr( $link ) . '" target="_blank">', '</a></strong>' );
         ?>
         </div>
         <div class='option warning
         <?php echo (\wpSPUI()->settings()->doBackgroundProcess) ? '' : 'hidden' ?>'>
         <p><?php esc_html_e( 'I understand that background upscaling may pause if there are no visitors on the website.', 'shortpixel-upscale-image' ); ?></p></div>

       </div>

	  <!-- <h2><?php esc_html_e('Limit bulk', 'shortpixel-upscale-image'); ?></h2> -->

<div class='bulk-date-picker optiongroup'>
	<?php
  /* translators: 1: Opening h4 tag. 2: Closing h4 tag. 3: Start date picker HTML. 4: End date picker HTML. */
  printf( wp_kses_post( __( '%1$sOptional: Upscale items between %2$s %3$s and %4$s ', 'shortpixel-upscale-image' ) ), 
	'<h4>', 
	'</h4>',
	'<span class="date-picker-container">
		
	<label><input type="text" name="start-date" id="bulk-start-date" value="" placeholder="' . esc_attr__( 'Start date' ,'shortpixel-upscale-image' ) . '" /></label></span>', 
	'<span class="date-picker-container">
	
	<label><input type="text" name="end-date" id="bulk-end-date" value="" placeholder="' . esc_attr__( 'End date' ,'shortpixel-upscale-image' ) . '" /></label></span>'
	); ?>
</div>
		</div> <!-- option block -->



				</div> <!-- COLLAP CONTENT --> 
				</div> <!--- WRAP COLLAP --> 

				


 	 	 <div class="option-block all-round">
       <div class='optiongroup' data-check-visibility="false" data-control="data-check-approx-total">
          <h3><?php esc_html_e('No images found', 'shortpixel-upscale-image'); ?></h3>
          <p><?php esc_html_e('ShortPixel Bulk couldn\'t find any images ready for upscaling.','shortpixel-upscale-image'); ?></p>
       </div>

       <h4 class='approx'><?php esc_html_e('An estimate of images not yet upscaled in this installation', 'shortpixel-upscale-image'); ?> :
			<span data-check-approx-total><?php echo esc_html($spui_approx->total->images) ?></span> </h4>

       <div><p><?php
       /* translators: 1: Opening bold tag. 2: Closing bold tag. */
       printf( wp_kses_post( __( 'In the next step, the plugin will calculate the total number of images to be upscaled, and your bulk process will be prepared. The processing %1$s will not start yet %2$s, but a summary of the images to be upscaled will be displayed.', 'shortpixel-upscale-image' ) ), '<b>', '</b>' );
       ?></p></div>
		 </div>

      <nav>
        <button class="button" type="button" data-action="FinishBulk">
					<span class='dashicons dashicons-arrow-left'></span>
					<p><?php esc_html_e('Back', 'shortpixel-upscale-image'); ?></p>
				</button>

        <button class="button-primary button" type="button" data-action="CreateBulk" data-panel="summary" data-control="data-check-approx-total" data-check-presentation="disable">
					<span class='dashicons dashicons-arrow-right'></span>
					<p><?php esc_html_e('Calculate', 'shortpixel-upscale-image'); ?></p>
				</button>
      </nav>

    </div> <!-- interface wrapper -->
  </div><!-- container -->
</section>
