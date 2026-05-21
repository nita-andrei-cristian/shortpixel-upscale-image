<?php
namespace SPUI;

if ( ! defined( 'ABSPATH' ) ) {
 exit; // Exit if accessed directly.
}

use SPUI\ShortPixelLogger\ShortPixelLogger as Log;

// Image gallery plugins that require a few small extra's
class ImageGalleries
{
  public function __construct()
  {
      add_action('admin_init', array($this, 'addConstants'));
      add_filter('spui/init/optimize_on_screens', array($this, 'add_screen_loads'), 10, 2);
  }

  // This adds constants for mentioned plugins checking for specific suffixes on addUnlistedImages.
	// @integration Envira Gallery
	// @integration Soliloquy
  public function addConstants()
  {
    //if( !defined('SPUI_CUSTOM_THUMB_SUFFIXES')) {


    if (\wpSPUI()->env()->plugin_active('envira') || \wpSPUI()->env()->plugin_active('soliquy') )
		{

						add_filter('spui/image/unlisted_suffixes', array($this, 'envira_suffixes'));
            //define('SPUI_CUSTOM_THUMB_SUFFIXES', '_c,_tl,_tr,_br,_bl');
    //    }

		// not in use?
    //    elseif(defined('SPUI_CUSTOM_THUMB_SUFFIX')) {
    //        define('SPUI_CUSTOM_THUMB_SUFFIXES', SPUI_CUSTOM_THUMB_SUFFIX);
    //    }
    }

  }

  public function add_screen_loads($screens, $screen)
  {

     // Envira Gallery Lite
     $screens[] = 'edit-envira';
     $screens[] = 'envira';

     // Solo Cuy
     $screens[] = 'edit-soliloquy';
     $screens[] = 'soliloquy';
     return $screens;
  }

	public function envira_suffixes($suffixes)
	{

		 $envira_suffixes = array('_c','_tl','_tr','_br','_bl', '-\d+x\d+');
		 $suffixes = array_merge($suffixes, $envira_suffixes);

		 return $suffixes;
	}



} // class
$c = new ImageGalleries();
