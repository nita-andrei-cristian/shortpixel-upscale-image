<?php
namespace SPUI;

if ( ! defined( 'ABSPATH' ) ) {
 exit; // Exit if accessed directly.
}

use SPUI\ShortPixelLogger\ShortPixelLogger as Log;

class Spai
{
		public function __construct()
		{
			 add_action('plugins_loaded', array($this, 'addHooks'));

		}

		public function addHooks()
		{
			  if (\wpSPUI()->env()->plugin_active('spai'))
				{
					 // Prevent SPAI doing its stuff to our JSON returns.
					 $hook_upon = array('spui_image_processing', 'spui_ajaxRequest');
					 if (wp_doing_ajax() &&
					 			// phpcs:ignore WordPress.Security.NonceVerification.Recommended  -- This is not a form
					 		 isset($_REQUEST['action']) &&
							 // phpcs:ignore WordPress.Security.NonceVerification.Recommended  -- This is not a form
							 in_array($_REQUEST['action'], $hook_upon)			 )
					 {
						 	$this->preventCache();
					 }
				}
		}

		public function preventCache()
		{
			  if (! defined('DONOTCDN'))
				{
					 define('DONOTCDN', true);
				}
		}
}

$s = new Spai();
