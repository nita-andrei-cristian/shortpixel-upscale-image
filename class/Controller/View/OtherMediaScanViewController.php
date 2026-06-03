<?php
namespace SPUI\Controller\View;

if ( ! defined( 'ABSPATH' ) ) {
 exit; // Exit if accessed directly.
}

use SPUI\ShortPixelLogger\ShortPixelLogger as Log;
use SPUI\Helper\InstallHelper as InstallHelper;
use SPUI\Controller\OtherMediaController as OtherMediaController;


class OtherMediaScanViewController extends \SPUI\ViewController
{

  protected $template = 'view-other-media-scan';

  protected static $instance;

  protected static $allFolders;

  private $controller;

  public function __construct()
  {
    parent::__construct();

    $this->controller = OtherMediaController::getInstance();
  }

  public function load()
  {

      $this->view->title = __('Scan for new files', 'shortpixel-image-optimiser');
      $this->view->pagination = false;

      $this->view->show_search = false;
      $this->view->has_filters = false;

			$this->view->totalFolders = count($this->controller->getActiveDirectoryIDS());

      $this->loadView();
  }
} // class
