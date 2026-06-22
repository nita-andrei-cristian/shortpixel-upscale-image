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
		$message = '<p><strong>' .  __('ShortPixel has found items in the media library with an outdated upscale format!', 'shortpixel-upscale-image') . '</strong></p>';

		// translators: 1: Opening emphasis or spacing markup. 2: Closing markup. 3: Opening documentation link.
		$message .= '<p>' . __('Prior to version 5.0, a different format was used to store ShortPixel upscale information. ShortPixel automatically migrates the media library items to the new format when they are opened. %1$s Please check if your images contain the upscale information after migration. %2$s Read more %3$s', 'shortpixel-upscale-image') . '</p>';

		$message .=  '<p>' . __('It is recommended to migrate all items to the modern format by clicking the button below.', 'shortpixel-upscale-image') . '</p>';
		$message .= '<p><a href="%s" class="button button-primary">%s</a></p>';

		$read_link = esc_url('https://shortpixel.com/knowledge-base/article/spio-5-tells-me-to-convert-legacy-data-what-is-this/');
		$action_link = esc_url(admin_url('upload.php?page=wp-short-pixel-bulk&panel=bulk-migrate'));
		$action_name = __('Migrate upscale data', 'shortpixel-upscale-image');

		$message = sprintf($message, '<br>', '<a href="' . $read_link . '" target="_blank">', '</a>', $action_link, $action_name);

		return $message;
	}
}
