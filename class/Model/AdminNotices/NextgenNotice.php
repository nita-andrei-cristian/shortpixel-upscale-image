<?php
namespace SPUI\Model\AdminNotices;

if ( ! defined( 'ABSPATH' ) ) {
 exit; // Exit if accessed directly.
}

use SPUI\Controller\ApiKeyController as ApiKeyController;

class NextgenNotice extends \SPUI\Model\AdminNoticeModel
{
	protected $key = 'MSG_INTEGRATION_NGGALLERY';

	protected function checkTrigger()
	{

		$settings = \wpSPUI()->settings();
		$keyControl = ApiKeyController::getInstance();

		if (false === $keyControl->keyIsVerified())
		{
			return false; // no key, no integrations.
		}

		if (\wpSPUI()->env()->has_nextgen && ! $settings->includeNextGen)
		{
			 return true;
		}

		return false;
	}

	protected function getMessage()
	{
		$url = esc_url(admin_url('options-general.php?page=shortpixel-upscale-settings&part=optimisation'));
		$message = sprintf(__('You seem to be using NextGen Gallery. You can upscale your galleries with ShortPixel, but this is not currently enabled. To enable it, %sgo to settings and enable%s it!', 'shortpixel_image_optimiser'), '<a href="' . $url . '">', '</a>');

		return $message;

	}
}
