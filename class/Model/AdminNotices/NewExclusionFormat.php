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
				/* translators: 1: Opening exclusions settings link. 2: Closing exclusions settings link. */
				$notice_text = __( 'Since version 5.5.0, Shortpixel Image Upscale also checks thumbnails for exclusions. This can change which images are upscaled and which are excluded. Please check your exclusion rules in the %1$sShortpixel Image Upscale%2$s page.', 'shortpixel-upscale-image' );
				$message = '<p>' . sprintf(
					$notice_text,
					'<a href="options-general.php?page=shortpixel-upscale-settings&part=exclusions">',
					'</a>'
				) . '</p>';

		return $message;
	}
}
