<?php
/**
 * Plugin Name: ShortPixel Image Upscaler
 * Plugin URI: https://shortpixel.com/
 * Description: ShortPixel upscales images automatically, while guarding the quality of your images. Check your <a href="/wp-admin/options-general.php?page=wp-shortpixel-upscale-settings" target="_blank">Settings &gt; ShortPixel Upscaler</a> page on how to start upscaling your image library.
 * Version: 6.4.4
 * Author: ShortPixel - Convert WebP/AVIF & Upscale Images
 * Author URI: https://shortpixel.com
 * GitHub Plugin URI: https://github.com/short-pixel-upscaler/shortpixel-upscale-image
 * Text Domain: shortpixel-upscale-image
 * Domain Path: /lang
 */


 if ( ! defined( 'ABSPATH' ) ) {
 	exit('No Direct Access'); // Exit if accessed directly.
 }

// Preventing double load crash.
if (function_exists('wpSPUI'))
{
    add_action('admin_notices', function () {
      echo '<div class="error"><h4>';
      printf(esc_html__('ShortPixel plugin already loaded. You might have two versions active. Not loaded: %s', 'shortpixel-upscale-image'), __FILE__);
      echo '</h4></div>';
    });
    return;
}

if (! defined('SPUI_RESET_ON_ACTIVATE'))
  define('SPUI_RESET_ON_ACTIVATE', false);

//define('SPUI_DEBUG', true);
//define('SPUI_DEBUG_TARGET', true);

define('SPUI_PLUGIN_FILE', __FILE__);
define('SPUI_PLUGIN_DIR', __DIR__);

define('SPUI_IMAGE_OPTIMISER_VERSION', "6.4.4");

define('SPUI_BACKUP', 'ShortpixelBackups');
define('SPUI_MAX_FAIL_RETRIES', 3);

if(!defined('SPUI_USE_DOUBLE_WEBP_EXTENSION')) { //can be defined in wp-config.php
    define('SPUI_USE_DOUBLE_WEBP_EXTENSION', false);
}

if(!defined('SPUI_USE_DOUBLE_AVIF_EXTENSION')) { //can be defined in wp-config.php
    define('SPUI_USE_DOUBLE_AVIF_EXTENSION', false);
}

define('SPUI_API', 'api.shortpixel.com');

$max_exec = intval(ini_get('max_execution_time'));
if ($max_exec === 0) // max execution time of zero means infinite. Quantify.
  $max_exec = 60;
elseif($max_exec < 0) // some hosts like to set negative figures on this. Ignore that.
  $max_exec = 30;
define('SPUI_MAX_EXECUTION_TIME', $max_exec);

// ** Load the modules */
require_once(SPUI_PLUGIN_DIR . '/build/shortpixel/autoload.php');

$sp__uploads = wp_get_upload_dir();

define('SPUI_UPLOADS_BASE', (file_exists($sp__uploads['basedir']) ? '' : ABSPATH) . $sp__uploads['basedir'] );
define('SPUI_UPLOADS_URL', is_main_site() ? $sp__uploads['baseurl'] : dirname(dirname($sp__uploads['baseurl'])));
define('SPUI_UPLOADS_NAME', basename(is_main_site() ? SPUI_UPLOADS_BASE : dirname(dirname(SPUI_UPLOADS_BASE))));
$sp__backupBase = is_main_site() ? SPUI_UPLOADS_BASE : dirname(dirname(SPUI_UPLOADS_BASE));
define('SPUI_BACKUP_FOLDER', $sp__backupBase . '/' . SPUI_BACKUP);



//define('SPUI_SILENT_MODE', true); // no global notifications. Can lead to data damage. After setting, reactivate plugin.
//define('SPUI_TRUSTED_MODE', false); // doesn't do any file checks on the view-side of things.
// define('SPUI_SKIP_FEEDBACK', true);

// Starting logging services, early as possible.
if (! defined('SPUI_DEBUG'))
{
    define('SPUI_DEBUG', false);
}


if (false === defined( 'WP_CLI' ) || false === WP_CLI)
{
	$log = \SPUI\ShortPixelLogger\ShortPixelLogger::getInstance();
	if (\SPUI\ShortPixelLogger\ShortPixelLogger::debugIsActive() )
	{
  	$log->setLogPath(SPUI_BACKUP_FOLDER . "/shortpixel_log");
	}
}

/* Function to reach core function of ShortPixel
* Use to get plugin url, plugin path, or certain core controllers
*/

if (! function_exists("wpSPUI"))	{
  function wpSPUI()
  {
     return \SPUI\ShortPixelPlugin::getInstance();
  }
}
// Start runtime here
require_once(SPUI_PLUGIN_DIR . '/shortpixel-plugin.php'); // loads runtime and needed classes.

// PSR-4 package loader.
$loader = new SPUI\Build\PackageLoader();
$loader->setComposerFile(SPUI_PLUGIN_DIR . '/class/plugin.json');
$loader->load(SPUI_PLUGIN_DIR);

wpSPUI(); // let's go!

// Activation / Deactivation services
register_activation_hook( __FILE__, array('\SPUI\Helper\InstallHelper','activatePlugin') );
register_deactivation_hook( __FILE__,  array('\SPUI\Helper\InstallHelper','deactivatePlugin') );
register_uninstall_hook(__FILE__,  array('\SPUI\Helper\InstallHelper','uninstallPlugin') );
