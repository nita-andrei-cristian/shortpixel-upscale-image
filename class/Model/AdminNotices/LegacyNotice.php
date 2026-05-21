<?php
namespace SPUI\Model\AdminNotices;

if ( ! defined( 'ABSPATH' ) ) {
 exit; // Exit if accessed directly.
}

class LegacyNotice extends \SPUI\Model\AdminNoticeModel
{
	protected $key = 'MSG_CONVERT_LEGACY';

	protected function checkTrigger()
	{
		 return false;
	}

	protected function getMessage()
	{
		$message = '<p><strong>' .  __('ShortPixel has found items in the media library with an outdated upscaling data format!', 'shortpixel-upscale-image') . '</strong></p>';

		$message .= '<p>' . __('Prior to version 5.0, a different format was used to store ShortPixel upscaling information. ShortPixel automatically migrates the media library items to the new format when they are opened. %s Please check if your images contain the upscaling information after migration. %s Read more %s', 'shortpixel-upscale-image') . '</p>';

		$message .=  '<p>' . __('It is recommended to migrate all items to the modern format by clicking the button below.', 'shortpixel-image-optimser') . '</p>';
		$message .= '<p><a href="%s" class="button button-primary">%s</a></p>';

		$read_link = esc_url('https://shortpixel.com/knowledge-base/article/spui-5-tells-me-to-convert-legacy-data-what-is-this/');
		$action_link = esc_url(admin_url('upload.php?page=wp-shortpixel-upscale-bulk&panel=bulk-migrate'));
		$action_name = __('Migrate upscaling data', 'shortpixel-upscale-image');

		$message = sprintf($message, '<br>', '<a href="' . $read_link . '" target="_blank">', '</a>', $action_link, $action_name);

		return $message;
	}
}
