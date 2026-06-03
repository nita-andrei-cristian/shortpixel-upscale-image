<?php

namespace SPUI\External\Offload;

use SPUI\Model\File\FileModel as FileModel;

if (! defined('ABSPATH')) {
	exit; // Exit if accessed directly.
}

use SPUI\ShortPixelLogger\ShortPixelLogger as Log;
use SPUI\Notices\NoticeController as Notice;

class InfiniteUploads
{
	
		public function __construct()
		{
		//	add_filter('spui/image/urltopath', array($this, 'checkIfOffloaded'), 10, 3);
		//	add_filter('spui/file/virtual/translate', array($this, 'getLocalPathByURL'));
		}


		/** Checks if image is offloaded. True / False  */
		public function checkIfOffloaded($boolean, $url, $fullpath)
		{
			 
		}

		public function getLocalPathByURL()
		{

		}
	



}