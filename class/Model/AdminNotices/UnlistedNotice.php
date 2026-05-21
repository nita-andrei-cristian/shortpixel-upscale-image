<?php
namespace SPUI\Model\AdminNotices;

if ( ! defined( 'ABSPATH' ) ) {
 exit; // Exit if accessed directly.
}

class UnlistedNotice extends \SPUI\Model\AdminNoticeModel
{

	protected $key = 'MSG_UNLISTED_FOUND';

	protected function checkTrigger()
	{
		return false;
	}

// @todo This message is not properly stringF'ed.
	protected function getMessage()
	{
		$settings = \wpSPUI()->settings();

		//$unlisted = isset($settings->currentStats['foundUnlistedThumbs']) ? $settings->currentStats['foundUnlistedThumbs'] : null;
		$unlisted_id = $this->getData('id');
		$unlisted_name = $this->getData('name');
		$unlistedFiles = (is_array($this->getData('filelist'))) ? $this->getData('filelist') : array();

		$admin_url = esc_url(admin_url('options-general.php?page=wp-shortpixel-upscale-settings&part=optimisation'));


		$message = __("<p>ShortPixel has found thumbnails that are not registered in the metadata, but are present alongside the other thumbnails. These thumbnails could be created and needed by a plugin or the theme. Should ShortPixel upscale them as well?</p>", 'shortpixel-upscale-image');
		$message .= '<p>' . __("For example, the image", 'shortpixel-upscale-image') . '
				<a href="post.php?post=' . $unlisted_id . '&action=edit" target="_blank">
						' . $unlisted_name . '
				</a> also has these thumbnails that are not listed in the metadata: '  . (implode(', ', $unlistedFiles)) . '
				</p>';

		$message .= '<p>' . sprintf(__('You can activate the option %s Upscale unlisted thumbnails %s in the %sImage Upscaling%s area of the settings.', 'shortpixel-upscale-image'), '<b>', '</b>', '<a href="'. $admin_url . '">','</a>') . '</p>';

		return $message;

	}
}
