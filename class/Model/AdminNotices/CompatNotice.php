<?php
namespace SPUI\Model\AdminNotices;

if ( ! defined( 'ABSPATH' ) ) {
 exit; // Exit if accessed directly.
}

class CompatNotice extends \SPUI\Model\AdminNoticeModel
{
	protected $key = 'MSG_COMPAT';
	protected $errorLevel = 'warning';

	protected function checkTrigger()
	{
			$conflictPlugins = $this->getConflictingPlugins();

			if (count($conflictPlugins) > 0)
			{
				$this->addData('conflicts', $conflictPlugins);
				return true;
			}
			else {
				return false;
			}
	}

	protected function getMessage()
	{
		$conflicts = $this->getData('conflicts');
		if (! is_array($conflicts))
			$conflicts = array();

		$message = __("The following plugins are not compatible with ShortPixel and may cause unexpected results: ",'shortpixel-upscale-image');
		$message .= '<ul class="sp-conflict-plugins">';
		foreach($conflicts as $plugin) {
				//ShortPixelVDD($plugin);
				$action = $plugin['action'];
				$link = ( $action == 'Deactivate' )
						? wp_nonce_url( admin_url( 'admin-post.php?action=spui_deactivate_conflict_plugin&plugin=' . urlencode( $plugin['path'] ) ), 'sp_deactivate_plugin_nonce' )
						: $plugin['href'];
				$message .= '<li class="sp-conflict-plugins-list"><strong>' . $plugin['name'] . '</strong>';
				$message .= '<a href="' . $link . '" class="button button-primary">' . $action . '</a>';

				if($plugin['details']) $message .= '<br>';
				if($plugin['details']) $message .= '<span>' . $plugin['details'] . '</span>';
		}
		$message .= "</ul>";

		return $message;
	}

  protected function checkReset()
  {
      $conflictPlugins = $this->getConflictingPlugins();
      if (count ($conflictPlugins) === 0)
      {
         return true;
      }
      return false;
  }

	protected function getConflictingPlugins() {
			$settings = \wpSPUI()->settings();

			$conflictPlugins = array(
					'WP Smush - Image Upscaling'
							=> array(
											'action'=>'Deactivate',
											'data'=>'wp-smushit/wp-smush.php',
											'page'=>'wp-smush-bulk'
							),
					'Imagify Image Upscaler'
							=> array(
											'action'=>'Deactivate',
											'data'=>'imagify/imagify.php',
											'page'=>'imagify'
							),
					'Compress JPEG & PNG images (TinyPNG)'
							=> array(
											'action'=>'Deactivate',
											'data'=>'tiny-compress-images/tiny-compress-images.php',
											'page'=>'tinify'
							),
					'Kraken.io Image Upscaler'
							=> array(
											'action'=>'Deactivate',
											'data'=>'kraken-image-upscaler/kraken.php',
											'page'=>'wp-krakenio'
							),
					'Optimus - WordPress Image Upscaler'
							=> array(
											'action'=>'Deactivate',
											'data'=>'optimus/optimus.php',
											'page'=>'optimus'
							),
					'Phoenix Media Rename' => array(
											'action' => 'Deactivate',
											'data' => 'phoenix-media-rename/phoenix-media-rename.php',
					),
					'EWWW Image Upscaler'
							=> array(
											'action'=>'Deactivate',
											'data'=>'ewww-image-upscaler/ewww-image-upscaler.php',
											'page'=>'ewww-image-upscaler%2F'
							),
					'EWWW Image Upscaler Cloud'
							=> array(
											'action'=>'Deactivate',
											'data'=>'ewww-image-upscaler-cloud/ewww-image-upscaler-cloud.php',
											'page'=>'ewww-image-upscaler-cloud%2F'
							),
					'ImageRecycle pdf & image compression'
							=> array(
											'action'=>'Deactivate',
											'data'=>'imagerecycle-pdf-image-compression/wp-image-recycle.php',
											'page'=>'option-image-recycle'
							),
					'CheetahO Image Upscaler'
							=> array(
											'action'=>'Deactivate',
											'data'=>'cheetaho-image-upscaler/cheetaho.php',
											'page'=>'cheetaho'
							),
					'Zara 4 Image Compression'
							=> array(
											'action'=>'Deactivate',
											'data'=>'zara-4/zara-4.php',
											'page'=>'zara-4'
							),
					'CW Image Upscaler'
							=> array(
											'action'=>'Deactivate',
											'data'=>'cw-image-upscaler/cw-image-upscaler.php',
											'page'=>'cw-image-upscaler'
							),
					'Simple Image Sizes'
							=> array(
											'action'=>'Deactivate',
											'data'=>'simple-image-sizes/simple_image_sizes.php'
							),
					'Regenerate Thumbnails and Delete Unused'
						=> array(
										'action' => 'Deactivate',
										'data' => 'regenerate-thumbnails-and-delete-unused/regenerate_wpregenerate.php',
						),
            'Resmushit' => [
                    'action' => 'Deactivate',
                    'data' => 'resmushit-image-upscaler/resmushit.php',
            ],
						'Swift Performance'
							=> array(
											'action' => 'Deactivate',
											'data' => 'swift-performance/performance.php',
						),
            'Swift AI'
              => array(
                      'action' => 'Deactivate',
                      'data' => 'swift-ai/main.php',
            ),
						'Swift Performance Lite'
								=> array(
												'action' => 'Deactivate',
												'data' => 'swift-performance-lite/performance.php',
						),
						 //DEACTIVATED TEMPORARILY - it seems that the customers get scared.
					/* 'Jetpack by WordPress.com - The Speed up image load times Option'
							=> array(
											'action'=>'Change Setting',
											'data'=>'jetpack/jetpack.php',
											'href'=>'admin.php?page=jetpack#/settings'
							)
					*/
			);
			if($settings->processThumbnails) {
					$details = __('Details: recreating image files may require re-upscaling of the resulting thumbnails, even if they were previously upscaled. Please use <a href="https://wordpress.org/plugins/regenerate-thumbnails-advanced/" target="_blank">reGenerate Thumbnails Advanced</a> instead.','shortpixel-upscale-image');

					$conflictPlugins = array_merge($conflictPlugins, array(
							'Regenerate Thumbnails'
									=> array(
													'action'=>'Deactivate',
													'data'=>'regenerate-thumbnails/regenerate-thumbnails.php',
													'page'=>'regenerate-thumbnails',
													'details' => $details
									),
							'Force Regenerate Thumbnails'
									=> array(
													'action'=>'Deactivate',
													'data'=>'force-regenerate-thumbnails/force-regenerate-thumbnails.php',
													'page'=>'force-regenerate-thumbnails',
													'details' => $details
									)
					));
			}


			$found = array();
			foreach($conflictPlugins as $name => $path) {
					$action = ( isset($path['action']) ) ? $path['action'] : null;
					$data = ( isset($path['data']) ) ? $path['data'] : null;
					$href = ( isset($path['href']) ) ? $path['href'] : null;
					$page = ( isset($path['page']) ) ? $path['page'] : null;
					$details = ( isset($path['details']) ) ? $path['details'] : null;
					if(is_plugin_active($data)) {

              // Local checks for things. If too much this needs some other impl.
							if( $data == 'jetpack/jetpack.php' ){
									$jetPackPhoton = get_option('jetpack_active_modules') ? in_array('photon', get_option('jetpack_active_modules')) : false;
									if( !$jetPackPhoton ){ continue; }
							}

              if ($data == 'swift-performance/performance.php' || $data == 'swift-ai/main.php')
              {
                  if (false === $this->checkSwiftActive())
                  {
                     continue;
                  }
              }

							$found[] = array( 'name' => $name, 'action'=> $action, 'path' => $data, 'href' => $href , 'page' => $page, 'details' => $details);
					}
			}
			return $found;
	}

  private function checkSwiftActive()
  {
     if ( function_exists('swift3_check_option') && true == swift3_check_option('upscale-images', 'on'))
     {
        return true;
     }
     return false;
  }

}
