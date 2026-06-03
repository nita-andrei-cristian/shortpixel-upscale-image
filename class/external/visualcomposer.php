<?php

if ( ! defined( 'ABSPATH' ) ) {
 exit; // Exit if accessed directly.
}

// Visual Composer and compat class.
// SPUI: renamed from the global visualComp so SPUI stays co-active with SPIO, which
// declares its own global visualComp.
class SPUI_visualComp
{

  public function __construct()
  {
     add_filter('spui/init/automedialibrary', array($this, 'check_vcinline'));
  }

  // autolibrary should not do things when VC is being inline somewhere.
  public function check_vcinline($bool)
  {
      if ( function_exists( 'vc_action' ) && vc_action() == 'vc_inline' )
        return false;
      else
        return $bool;
  }

} // Class

$vc = new SPUI_visualComp();
