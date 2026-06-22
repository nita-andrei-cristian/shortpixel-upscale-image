<?php
         if ( ! defined( 'ABSPATH' ) ) {
             exit;
         }

         require_once  (__DIR__  . "/PackageLoader.php");
         $spui_loader = new SPUI\Build\PackageLoader();
         $spui_loader->load(__DIR__);
         
