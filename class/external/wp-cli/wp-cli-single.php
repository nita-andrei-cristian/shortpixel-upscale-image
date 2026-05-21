<?php
namespace SPUI;

if ( ! defined( 'ABSPATH' ) ) {
 exit; // Exit if accessed directly.
}

use SPUI\ShortPixelLogger\ShortPixelLogger as Log;
//use SPUI\Controller\OptimizeController as OptimizeController;
use SPUI\Controller\BulkController as BulkController;

use SPUI\Controller\Queue\Queue as Queue;
use SPUI\Controller\ResponseController as ResponseController;

use SPUI\Model\Queue\QueueItem as QueueItem;
use SPUI\Controller\Queue\QueueItems as QueueItems;

/**
* Actions and operations for the ShortPixel Image Upscaler plugin
*/
class SPUISingle extends SPUICommandBase
{

    /**
   * Restores the optimized item to its original state (if backups are active).
   *
   * ## OPTIONS
   *
   * <id>
   * : Media Library ID or Custom Media ID
	 *
   * [--type=<type>]
   * : media | custom
   * ---
   * default: media
   * options:
   *   - media
   *   - custom
   * ---
   *
   * ## EXAMPLES
   *
   *   wp spui restore 123
   *   wp spui restore 21 --type=custom
   *
   * @when after_wp_load
   */
  public function restore($args, $assoc_args)
  {
      //$controller = new QueueController();
      $fs = \wpSPUI()->filesystem();

      if (! isset($args[0]))
      {
        \WP_CLI::Error(__('Specify an (Media Library) Item ID', 'shortpixel-upscale-image'));
        return;
      }
			if (! is_numeric($args[0]))
			{
				 \WP_CLI::Error(__('Item ID needs to be a number', 'shortpixel-upscale-image'));
				 return;
			}

      $id = intval($args[0]);
			$type = $assoc_args['type'];

      $imageModel = $fs->getImage($id, $type);

      if ($imageModel === false)
			{
				 \WP_CLI::Error(__('No Image returned. Please check if the number and type are correct and the image exists', 'shortpixel-upscale-image'));
				 return;
			}

      $qItem = QueueItems::getImageItem($imageModel);
      $qItem->newRestoreAction();

      $queueController = $this->getQueueController();
      //$optimiser = $qItem->getApiController();
      //$optimiser->restoreItem($qItem);

      $result  = $queueController->addItemToQueue($imageModel, ['action' => 'restore']);

      //$result = $qItem->result();

			$this->showResponses();

	 		if (property_exists($result,'message') && ! is_null($result->message) && strlen($result->message) > 0)
				 $message = $result->message;
			elseif (property_exists($result, 'result') )
      {
        \WP_CLI::Error(sprintf(__("Result result exists, should not be", 'shortpixel-upscale-image'), $result) );
      }
      else {
         $message = __('Operation didn\'t yield any messages');
      }


      if (property_exists($result, 'success') && true === $result->success)
			{
        \WP_CLI::Success($message);
			}
      elseif (true === $result->is_error)
			{
        \WP_CLI::Error(sprintf(__("Restoring Item: %s", 'shortpixel-upscale-image'), $message) );
			}
      else {
        \WP_CLI::Error('Undetermined' . $message);
      }
  }

  	/**
	 * Add an Alt Tag to Item
	 *
	 *  <id>
	 *   : Media Library ID
	 *
	 *
	 */
	public function requestAlt($args, $assoc)
	{
		$queueController = $this->getQueueController();
		$fs = \wpSPUI()->filesystem();

		if (! isset($args[0])) {
			\WP_CLI::Error(__('Specify an Media Library Item ID', 'shortpixel-upscale-image'));
			return;
		}

		$id = intval($args[0]);

		$imageObj = $fs->getMediaImage($id);

		if ($imageObj === false) {
			\WP_CLI::Error(__('Image object not found / non-existing in database by this ID', 'shortpixel-upscale-image'));
		}

		// @todo When completing this script probably as for AddSingleItem with requestAlt as action, then run queue, then remove/update item for getter.

		// @todo Check OptimizeController - sendToProcessing for options / other data.

		$args = [
			'action' => 'requestAlt',

		];
		$result = $queueController->addItemToQueue($imageObj, $args);

		$this->displayResult($result, 'alttext');
	}




} // CLASS
