<?php
namespace SPUI\Model\AdminNotices;

if ( ! defined( 'ABSPATH' ) ) {
 exit; // Exit if accessed directly.
}

use \SPUI\Controller\CacheController as CacheController;
use SPUI\ShortPixelLogger\ShortPixelLogger as Log;


class AvifNotice extends \SPUI\Model\AdminNoticeModel
{
	protected $key = 'MSG_AVIF_ERROR';
	protected $errorLevel = 'error';

	protected $error_message;
	protected $error_detail;


	protected function checkTrigger()
	{
		// No Automatic Trigger.
		// Disabled the notification and this check mechanism
		//$this->check(); // @todo Hacky solution to have this retry functionality available. @todo Fix into better structure with auto-check.
		return false;
	}

	public function check()
	{
		$cache = new CacheController();
		if (apply_filters('spui/avifcheck/override', false) === true)
		{ return; }


		if ($cache->getItem('avif_server_check')->exists() === false)
		{
			 $url = \wpSPUI()->plugin_url('res/img/test.avif');
			 $headers = get_headers($url);
			 $is_error = true;

			 $this->addData('headers', $headers);
			 // Defaults.
			 $this->error_message = __('AVIF server test failed. Your server may not be configured to display AVIF files correctly. Serving AVIF might cause your images not to load. Check your images, disable the AVIF option, or update your web server configuration.', 'shortpixel-upscale-image');
			 /* translators: %s: URL checked during the AVIF server test. */
			 $this->error_detail = sprintf( __( 'The request did not return valid HTTP headers. Check if the plugin is allowed to access %s', 'shortpixel-upscale-image' ), $url );

			 $response = $headers[0];

			 if (is_array($headers) )
			 {
					foreach($headers as $index => $header)
					{
							if ( strpos(strtolower($header), 'content-type') !== false )
							{
								// This is another header that can interrupt.
								if (strpos(strtolower($header), 'x-content-type-options') === false)
								{
									$contentType = $header;
								}
							}
					}

 					 // http not ok, redirect etc. Shouldn't happen.
					 if (is_null($response) || strpos($response, '200') === false)
					 {
						 /* translators: 1: Opening checked URL link. 2: URL that could not be retrieved. 3: Closing link. 4: Line break tag. */
						 $this->error_detail = sprintf(__('AVIF check could not be completed because the plugin could not retrieve %1$s %2$s %3$s. %4$s Please check the security/firewall settings and try again', 'shortpixel-upscale-image'), '<a href="' . $url . '">', $url, '</a>', '<br>');
					 }
					 elseif(is_null($contentType) || strpos($contentType, 'avif') === false)
					 {
						 /* translators: 1: Opening AVIF content-type documentation link. 2: Closing link. */
						 $this->error_detail = sprintf(__('The required Content-type header for AVIF files was not found. Please check this with your hosting and/or CDN provider. For more details on how to fix this issue, %1$s see this article %2$s', 'shortpixel-upscale-image'), '<a href="https://shortpixel.com/blog/avif-mime-type-delivery-apache-nginx/" target="_blank"> ', '</a>');
					 }
					 else
					 {
							$is_error = false;
					 }
			 }

			 if ($is_error)
			 {
				   if (is_null($this->notice) || $this->notice->isDismissed() === false)
					 {
						  $this->addManual();
					 }

			 }
			 else
			 {
				 		$this->reset();

						 $item = $cache->getItem('avif_server_check');
						 $item->setValue(time());
						 $item->setExpires(MONTH_IN_SECONDS);
						 $cache->storeItemObject($item );
			 }
		}

	}

	protected function getMessage()
	{
			$headers = $this->getData('headers');


			$message = '<h4>' . $this->error_message . '</h4><p>' . $this->error_detail . '</p><p class="small">' . __('Returned headers for:<br>', 'shortpixel-upscale-image') . esc_html( wp_json_encode( $headers ) ) .  '</p>';

      $message .= '<div>
        <button class="button button-primary notice-dismiss-action" data-dismisstype="remove" type="button" id="shortpixel-upgrade-advice" style="margin-right:10px;"><strong>' .  __('Dismiss and try again on next page load', 'shortpixel-upscale-image') . '</strong></button>
        </div>';

			return $message;
	}
}
