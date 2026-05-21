<?php
namespace SPUI\Model\AdminNotices;

if ( ! defined( 'ABSPATH' ) ) {
 exit; // Exit if accessed directly.
}

use SPUI\ShortPixelLogger\ShortPixelLogger as Log;
use SPUI\Controller\ApiKeyController as ApiKeyController;


class ApiNotice extends \SPUI\Model\AdminNoticeModel
{
	protected $key = 'MSG_NO_APIKEY';

  protected $exclude_screens = ['settings_page_wp-shortpixel-upscale-settings'];

	public function load()
	{
		$activationDate = \wpSPUI()->settings()->activationDate;
		if (! $activationDate)
		{
			 $activationDate = time();
			 \wpSPUI()->settings()->activationDate = $activationDate;
		}

		parent::load();
	}

	protected function checkTrigger()
	{
			$keyControl = ApiKeyController::getInstance();
			if ($keyControl->keyIsVerified())
			{
				return false;
			}

			// If not key is verified.
			return true;
	}

  protected function checkReset()
  {

		$keyControl = ApiKeyController::getInstance();
		if ($keyControl->keyIsVerified())
		{
      return true;
    }
    return false;
  }

	protected function getMessage()
	{
		$message = "<p>" . __('To start the upscaling process, you need to validate your API key on the '
						. '<a href="options-general.php?page=wp-shortpixel-upscale-settings">ShortPixel Upscaler Settings</a> page in your WordPress admin.','shortpixel-upscale-image') . "
		</p>
		<p>" .  __('If you do not have an API key yet, just fill out the form and a key will be created.','shortpixel-upscale-image') . "</p>";

		return $message;
	}
}
