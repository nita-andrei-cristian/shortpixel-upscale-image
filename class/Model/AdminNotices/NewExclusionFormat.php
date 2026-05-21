<?php
namespace SPUI\Model\AdminNotices;

if ( ! defined( 'ABSPATH' ) ) {
 exit; // Exit if accessed directly.
}

class NewExclusionFormat extends \SPUI\Model\AdminNoticeModel
{

  protected $key = 'MSG_EXCLUSION_WARNING';


	protected function checkTrigger()
	{
      $patterns = \wpSPUI()->settings()->excludePatterns;

      if (! is_array($patterns))
      {
         return false; 
      }

      foreach($patterns as $index => $pattern)
      {
        if (! isset($pattern['apply']))
        {
           return true;
        }
      }
      return false;
	}

	protected function getMessage()
	{
		$message = "<p>" . __('Since version 5.5.0, ShortPixel Image Optimiser also checks thumbnails for exclusions. This can change which images are upscaled and which are excluded. Please check your exclusion rules in the '
						. '<a href="options-general.php?page=wp-shortpixel-upscale-settings&part=exclusions">ShortPixel Upscaler Settings</a> page.','shortpixel-upscale-image') . "
		</p>";

		return $message;
	}
}
