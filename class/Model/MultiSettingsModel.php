<?php
namespace SPUI\Model;

if ( ! defined( 'ABSPATH' ) ) {
 exit; // Exit if accessed directly.
}

use SPUI\ShortPixelLogger\ShortPixelLogger as Log;


class MultiSettingsModel extends \SPUI\Model\SettingsModel
{

  private static $instance;
  private $option_name = 'spui_wpmu';
  private $updated = false;


  protected $model = [

  ];


  private $settings;


  protected function load()
  {
     $this->settings = get_site_option($this->option_name, array());
     register_shutdown_function(array($this, 'onShutdown'));
  }


} // class
