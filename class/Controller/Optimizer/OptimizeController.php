<?php

namespace SPUI\Controller\Optimizer;

use SPUI\Controller\Api\RequestManager;

if (!defined('ABSPATH')) {
  exit; // Exit if accessed directly.
}

use SPUI\ShortPixelLogger\ShortPixelLogger as Log;
use SPUI\Controller\ResponseController as ResponseController;

use SPUI\Model\Queue\QueueItem as QueueItem;

use SPUI\Helper\DownloadHelper as DownloadHelper;
use SPUI\Model\Image\ImageModel as ImageModel;
use SPUI\Helper\UiHelper as UiHelper;


use SPUI\Controller\Api\ApiController as ApiController;
use SPUI\Controller\ApiKeyController as ApiKeyController;

use SPUI\Model\Converter\Converter as Converter;

use SPUI\Controller\AjaxController as AjaxController;
use SPUI\Controller\Queue\QueueItems;
use SPUI\Controller\QuotaController as QuotaController;
use SPUI\Controller\StatsController as StatsController;

class OptimizeController extends OptimizerBase
{

  public function __construct()
  {
    parent::__construct();
    $this->api = ApiController::getInstance();
    $this->apiName = 'optimize';
  }

  public function enQueueItem(QueueItem $qItem, $args = [])
  {
    $queue = $this->getCurrentQueue($qItem);

    // SPUI: route scale_image action to its own setup method instead of optimize
    if (isset($args['action']) && 'scale_image' === $args['action'])
    {
      $qItem->newScaleImageAction($args);
    }
    else
    {
      $qItem->newOptimizeAction($args);
    }

    $status = $queue->addQueueItem($qItem);
    return $status;
  }


  /** Item check before the enqueueing happens 
   * 
   * @param QueueItem  
   * @return boolean 
   */
  public function checkItem(QueueItem $qItem)
  {
    /*  $defaults = array(
          'forceExclusion' => false,
          'action' => 'optimize',
      );
      $args = wp_parse_args($args, $defaults); */

    //$fs = \wpSPUI()->filesystem();

    // $json = $this->getJsonResponse();
    $bool = $this->checkImageModel($qItem);

    if (false === $bool) {
      return false;
    }

    $imageModel = $qItem->imageModel;

    // Manual Optimization order should always go trough
    if ($imageModel->isOptimizePrevented() !== false) {
      $imageModel->resetPrevent();
    }

    $is_processable = $imageModel->isProcessable();
    if (false === $is_processable && 'scale_image' === $qItem->data()->action && $imageModel->isOptimized()) {
      $is_processable = true;
    }

    // Allow processable to be overridden when using the manual optimize button
    if (false === $is_processable && true === $imageModel->isUserExcluded() && true === $qItem->data()->forceExclusion) {
      $imageModel->cancelUserExclusions();
      $is_processable = true;
    }

    // If is not processable and not user excluded (user via this way can force an optimize if needed) then don't do it!
    if (false === $is_processable) {
      $qItem->addResult([
        'message' => $imageModel->getProcessableReason(),
        'is_error' => true,
        'is_done' => true,
        'fileStatus' => ImageModel::FILE_STATUS_ERROR,
      ]);
    } else {
      return true;
    }

    return false;
  }

  public function sendToProcessing(QueueItem $qItem)
  {
    $action = $qItem->data()->action;

    if ('optimize' === $action || 'convert_api' ===  $action) {
      $is_processable = $qItem->imageModel->isProcessable();

      // Allow processable to be overridden when using the manual optimize button - ignore when this happens already to be in queue.

      if (false === $is_processable) {
        // @todo This should be checked.
        if (! is_null($qItem->data()->forceExclusion) && true == $qItem->data()->forceExclusion) {
          $qItem->imageModel->cancelUserExclusions();
        }
      }

      $this->api->processMediaItem($qItem, $qItem->imageModel);
    }

    if ('remove_background' === $action || 'scale_image' === $action ) {
      $this->api->processActionItem($qItem);
    }
  }

  public function handleAPIResult(QueueItem $qItem)
  {
    $imageModel = $qItem->imageModel;
    $q = $this->getCurrentQueue($qItem);
    $item_id = $qItem->item_id;
    $action = $qItem->data()->action;

    $bool = $this->checkImageModel($qItem);
    if (false === $bool) {
      return false;
    }

    $qItem->addResult(['apiName' => $this->apiName]);

    if (true === $qItem->block()) {

      $qItem->addResult([
        'apiStatus' => RequestManager::STATUS_UNCHANGED,
        'message' => __('Item is waiting (blocked)', 'shortpixel-image-optimiser'),
      ]);
      Log::addWarn('Encountered blocked item, processing success? ', $item_id);
    } else {
      // This used in bulk preview for formatting filename.
      $qItem->addResult(
        ['filename' => $imageModel->getFileName()]
      );

      // Used in WP-CLI
      ResponseController::addData($item_id, 'fileName', $imageModel->getFileName());
    }

    $quotaController = QuotaController::getInstance();

    if (true === $qItem->result()->is_error) {
      Log::addWarn('OptimizeControl - Item has Error', $qItem->result());
      // Check ApiStatus, and see what is what for error
      // https://shortpixel.com/api-docs
      $apistatus = $qItem->result()->apiStatus;

      if ($apistatus == RequestManager::STATUS_ERROR) // File Error - between -100 and -300
      {
        $qItem->addResult(['fileStatus' => ImageModel::FILE_STATUS_ERROR]);
      }
      // Out of Quota (partial / full)
      elseif ($apistatus == RequestManager::STATUS_QUOTA_EXCEEDED) {
        $error_code = AjaxController::NOQUOTA;
        $quotaController->setQuotaExceeded();
      } elseif ($apistatus == RequestManager::STATUS_NO_KEY) {
        $error_code = AjaxController::APIKEY_FAILED;
      } elseif ($apistatus == RequestManager::STATUS_QUEUE_FULL || $apistatus == RequestManager::STATUS_MAINTENANCE) // Full Queue / Maintenance mode
      {
        $error_code = AjaxController::SERVER_ERROR;
      }

      if (isset($error_code)) {
        $qItem->addResult(['error' => $error_code, 'is_error' => true]);
      }

      $response = array(
        'is_error' => true,
        'message' => $qItem->result()->message, // These mostly come from API
      );
      ResponseController::addData($item_id, $response);

      if ($qItem->result()->is_done) {
        $q->itemFailed($qItem, true);
        $this->HandleItemError($qItem);

        ResponseController::addData($qItem->item_id, 'is_done', true);
      }
    }

    // easier to reads than a elseif structure. 
    if (false === $qItem->result()->is_error) {

      if ('optimize' === $action || 'convert_api' === $action)
      {
        $this->handleOptimizeAction($qItem);        
      }
      elseif ('remove_background' === $action || 'scale_image' === $action)
      {
        $this->handleAction($qItem);
        // SPUI: finishItemProcess must be called when done, otherwise ShortQ keeps the item
        // in 'in_process' state indefinitely and the queue never advances.
        if (true === $qItem->result()->is_done)
        {
          $qItem->addResult(['fileStatus' => ImageModel::FILE_STATUS_SUCCESS]);
          $this->finishItemProcess($qItem);
        }
        elseif (false === $qItem->result()->is_done && false === $qItem->result()->is_error)
        {
          // Still waiting for API — persist tries count so ShortQ tracks the item correctly.
          // Only update if item is actually in queue DB (not editor preview flow).
          if (is_object($qItem->getQueueItem()))
          {
            $q->updateItem($qItem);
          }
        }
      }
    }

    // Cleaning up the debugger.
    $debugItem = clone $qItem;

    Log::addDebug('Optimizecontrol - QueueItem has a result ', $debugItem->result());


    ResponseController::addData($item_id, [
      'is_error' => $qItem->result()->is_error,
      'is_done' => $qItem->result()->is_done,
      'apiStatus' => $qItem->result()->apiStatus,
      'tries' => $qItem->data()->tries,
    ]);

    if (property_exists($qItem->result(), 'fileStatus')) {
      ResponseController::addData($item_id, 'fileStatus', $qItem->result()->fileStatus);
    }

    // For now here, see how that goes
    $responseMessage = ResponseController::formatQItem($qItem);
    if ($responseMessage !== false && strlen($responseMessage) > 0) {
      $qItem->addResult([
        'message' => $responseMessage,
      ]);
    }

    if ($qItem->result()->is_error) {
      $qItem->addResult([
        'kblink' => UiHelper::getKBSearchLink($qItem->result()->message)
      ]);
    }
  }

  protected function handleOptimizeAction($qItem)
  {
    $imageModel = $qItem->imageModel;
    $item_id = $qItem->item_id;
    $fs = \wpSPUI()->filesystem(); 
    $q = $this->getCurrentQueue($qItem);
    $statsController = StatsController::getInstance();

    if (true === $qItem->result()->is_done) {
      if ($qItem->result()->apiStatus == RequestManager::STATUS_SUCCESS) // Is done and with success
      {

        // Set the metadata decided on APItime.
        if (false === is_null($qItem->data()->compressionType)) {
          $imageModel->setMeta('compressionType', $qItem->data()->compressionType);
        } else {
          Log::addWarn('Compression Type not set on handleSuccess!');
        }

        if (is_array($qItem->result()->files) && count($qItem->result()->files) > 0) {
          $status = $this->handleOptimizedItem($qItem, $imageModel);
          $qItem->addResult(['improvements' => $imageModel->getImprovements()]);

          if (RequestManager::STATUS_SUCCESS == $status) {
            $qItem->addResult([
              'apiStatus' => RequestManager::STATUS_SUCCESS,
              'fileStatus' => ImageModel::FILE_STATUS_SUCCESS
            ]);

            do_action('shortpixel_image_optimised', $item_id);
            do_action('spui/image/optimised', $imageModel);
          } elseif (RequestManager::STATUS_CONVERTED == $status) {
            $qItem->addResult([
              'apiStatus' => RequestManager::STATUS_CONVERTED,
              'fileStatus' => ImageModel::FILE_STATUS_SUCCESS
            ]);

            $imageModel = $fs->getMediaImage($qItem->item_id);

            if (! is_null($qItem->data()->compressionTypeRequested)) {
              $qItem->setData('compressionType', $qItem->data()->compressionTypeRequested);
            }
            // Keep compressiontype from object, set in queue, imageModelToQueue
            //$imageModel->setMeta('compressionType', $qItem->data()->compressionType);
          } else {

            $qItem->addResult([
              'apiStatus' => RequestManager::STATUS_ERROR,
              'fileStatus' => ImageModel::FILE_STATUS_ERROR,
              'is_error' => true,
            ]);
          }

          $this->addPreview($qItem);

          // Dump Stats, Dump Quota. Refresh
          $statsController->reset();

          $this->deleteTempFiles($qItem);
        }
        // This was not a request process, just handle it and mark it as done.
        elseif ($qItem->result()->apiStatus == RequestManager::STATUS_NOT_API) {
          // Nothing here.
        } else {
          Log::addWarn('Api returns Success, but result has no files', $qItem->result());
          $message = sprintf(__('Image API returned succes, but without images', 'shortpixel-image-optimiser'), $item_id);
          ResponseController::addData($item_id, 'message', $message);
          $qItem->addResult(['is_error' => true, 'apiStatus' => RequestManager::STATUS_FAIL]);
        }
      }  // Is Done / Handle Success

      // This is_error can happen not from api, but from handleOptimized
      if ($qItem->result()->is_error) {
        Log::addDebug('Item failed, has error on done ', $qItem->result());
        $q->itemFailed($qItem, true);
        $this->HandleItemError($qItem);
      } else {

        // *** RESEND TO PROCESS MORE *** 
        // If this keeps giving issues, probably some trigger is needed and move to QueueController instead.
        if ($imageModel->isProcessable() && $qItem->result()->apiStatus !== RequestManager::STATUS_NOT_API) {
          Log::addDebug('Item with ID' . $item_id . ' still has processables (with dump)', $imageModel->getOptimizeUrls());

          $api = $this->api;

          $optimize_args = [];
          if (! is_null($qItem->data()->compressionType)) {
            $optimize_args['compressionType'] = $qItem->data()->compressionType;
          }
          if (! is_null($qItem->data()->smartcrop)) {
            $optimize_args['smartcrop'] = $qItem->data()->smartcrop;
          }

          // It can happen that only webp /avifs are left for this image. This can't influence the API cache, so dump is not needed. Just don't send empty URLs for processing here.
          $api->dumpMediaItem($qItem);

          // Fetch a new qItem, because of all the left-over-data . Left the old one alone for reporting
          $new_qItem = QueueItems::getImageItem($imageModel);

          $this->enQueueItem($new_qItem, $optimize_args); // requeue for further processing.

        } elseif (RequestManager::STATUS_CONVERTED !== $qItem->result()->apiStatus) {
          $this->finishItemProcess($qItem);
        }
      }
    } else { // Not is_done
      if ($qItem->result()->apiStatus == ApiController::STATUS_UNCHANGED || $qItem->result()->apiStatus === Apicontroller::STATUS_PARTIAL_SUCCESS) {
        $qItem->addResult(['fileStatus' => ImageModel::FILE_STATUS_PENDING]);
        $retry_limit = $q->getShortQ()->getOption('retry_limit');

        if ($qItem->result()->apiStatus === ApiController::STATUS_PARTIAL_SUCCESS) {
          if (property_exists($qItem->result(), 'files') && count($qItem->result()->files) > 0) {
            $this->handleOptimizedItem($qItem, $imageModel);
          } else {
            Log::addWarn('Status is partial success, but no files followed. ', $qItem->result());
          }

          // Let frontend follow unchanged / waiting procedure.
          $qItem->addResult(['apiStatus' => ApiController::STATUS_UNCHANGED]);
        }

        if ($retry_limit == $qItem->data()->tries || $retry_limit == ($qItem->data()->tries - 1)) {
          $message = __('Retry Limit reached. Image might be too large, limit too low or network issues.  ', 'shortpixel-image-optimiser');

          ResponseController::addData($item_id, 'message', $message);
          ResponseController::addData($item_id, 'is_error', true);
          ResponseController::addData($item_id, 'is_done', true);

          $qItem->addResult([
            'apiStatus' => ApiController::ERR_TIMEOUT,
            'message' => $message,
            'is_error' => true,
            'is_done' => true,
          ]);

          $this->HandleItemError($qItem);
        }
      }
    }
  }

  protected function handleAction($qItem) {

    $item_id = $qItem->item_id; 
    $imageModel = $qItem->imageModel;

    $_pl_raw = $qItem->data()->paramlist;
    $_pl_arr = is_object($_pl_raw) ? (array) $_pl_raw : (is_array($_pl_raw) ? $_pl_raw : []);
    $is_preview = isset($_pl_arr['preview_only']) ? $_pl_arr['preview_only'] : false; 
    $apiStatus = $qItem->result()->apiStatus; 

    // @todo When opening all from gutenberg et al, should send the original page / post id and add it to media item.
    $attached_post_id = 0; 

    if (RequestManager::STATUS_SUCCESS === $apiStatus)
    {
        if (false === $is_preview)
        {
          $paramlist = $qItem->data()->paramlist;
           if (is_object($paramlist)) { $paramlist = (array) $paramlist; }
           if (! is_array($paramlist)) { $paramlist = []; }

           // Handle image here / copy etc.
           $downloadHelper = DownloadHelper::getInstance();
           $url = $qItem->result()->optimized;
           $tmpFile = $downloadHelper->downloadFile($url);
           $newPostTitle = $paramlist['newPostTitle'] ?? '';

           if (isset($paramlist['attached_post_id']))
           {
              $attached_post_id = $paramlist['attached_post_id'];
           }

           $fileArray = [];

           $fileArray['name'] = $paramlist['newFileName'] ?? ''; 
           $fileArray['tmp_name']= $tmpFile->getFullPath(); 
           $fileArray['type'] = $tmpFile->getMime();  // @todo 
           $fileArray['size'] = $tmpFile->getFileSize(); 

           $new_attach_id = media_handle_sideload($fileArray, $attached_post_id, $newPostTitle);

           // SPUI: mark both the source and the generated attachment as scaled.
           if ( ! is_wp_error($new_attach_id) ) {
             update_post_meta($item_id, '_spui_scaled', (int) $new_attach_id);
             update_post_meta($new_attach_id, '_spui_scaled', (int) $item_id);

             // SPUI: carry over the descriptive + SEO metadata from the source
             // attachment. media_handle_sideload() only sets a title, so without this
             // the upscaled copy would lose its caption, description, alt text and any
             // SEO plugin data.
             $this->copyAttachmentMetadata($item_id, $new_attach_id);
           }

           $qItem->addResult(['new_attach_id' => $new_attach_id] );

           $tmpFile->delete();
        }
    }

  }

  /**
   * SPUI: Copy the descriptive and SEO metadata from a source attachment onto the
   * freshly created (upscaled) attachment.
   *
   * media_handle_sideload() creates a brand new attachment that only inherits a
   * title, so without this the upscaled image starts blank — losing its caption,
   * description, alt text and any SEO plugin data. Core WP fields are copied
   * explicitly; meta keys are cloned by known SEO prefix (plus the WP alt-text meta)
   * so new keys from those plugins keep working without enumerating each one.
   *
   * @param int $source_id The original attachment ID.
   * @param int $target_id The newly created upscaled attachment ID.
   */
  protected function copyAttachmentMetadata($source_id, $target_id)
  {
    $source_id = (int) $source_id;
    $target_id = (int) $target_id;

    if ($source_id <= 0 || $target_id <= 0 || $source_id === $target_id) {
      return;
    }

    $source = get_post($source_id);
    if (null === $source) {
      Log::addWarn('SPUI: cannot copy metadata, source attachment not found: ' . $source_id);
      return;
    }

    // Core descriptive fields: description (post_content) and caption (post_excerpt).
    $update = array(
      'ID'           => $target_id,
      'post_content' => $source->post_content, // Description
      'post_excerpt' => $source->post_excerpt, // Caption
    );

    // Only fill the title from the source when the sideload left it empty or fell
    // back to the file name, so an explicitly requested newPostTitle is preserved.
    $target = get_post($target_id);
    if (null !== $target && '' === trim((string) $target->post_title) && '' !== trim((string) $source->post_title)) {
      $update['post_title'] = $source->post_title;
    }

    wp_update_post($update);

    // Alt text + every known SEO plugin meta key (Yoast, Rank Math, SEOPress,
    // All in One SEO). Copying by prefix avoids enumerating individual keys.
    $seo_prefixes = array('_yoast_wpseo_', 'rank_math_', '_seopress_', '_aioseo_');

    foreach (array_keys(get_post_meta($source_id)) as $key) {
      $copy = ('_wp_attachment_image_alt' === $key);

      if (! $copy) {
        foreach ($seo_prefixes as $prefix) {
          if (0 === strpos($key, $prefix)) {
            $copy = true;
            break;
          }
        }
      }

      if (! $copy) {
        continue;
      }

      // get_post_meta() returns unserialized, unslashed values; the metadata API
      // expects slashed input, so re-slash before writing.
      $values = get_post_meta($source_id, $key);
      delete_post_meta($target_id, $key);
      foreach ($values as $value) {
        add_post_meta($target_id, $key, wp_slash($value));
      }
    }
  }

  protected function HandleItemError(QueueItem $qItem)
  {
    return;
  }

  /**
   * [Handles one optimized image and extra filetypes]
   * @param  [object] $q                         [queue object]
   * @param  [object] $item                      [item QueueItem object. The data item]
   * @param  [object] $mediaObj                  [imageModel of the optimized collection]
   * @param  [array] $successData               [all successdata received so far]
   * @return int           status integer, one of apicontroller status constants
   */
  protected function handleOptimizedItem($qItem, $imageModel)
  {
    $imageArray = $qItem->result()->files;

    $downloadHelper = DownloadHelper::getInstance();
    $converter = Converter::getConverter($imageModel, true);

    $qItem->block(true);

    $q = $this->currentQueue;
    $q->updateItem($qItem);

    $item_id = $qItem->item_id;

    // @todo Here check if these item_files are persistent ( probablY ) and if so add them to data(), not result();
    // @todo This should be a temporary cast. Perhaps best to include this in QueueItem object with extra functions / checks implementable?
    if (! is_null($qItem->data()->files)) {
      $item_files = (array) $qItem->data()->files;
    } else {
      $item_files = [];
    }


    foreach ($imageArray as $imageName => $image) {
      if (!isset($item_files[$imageName])) {
        $item_files[$imageName] = [];
      }

      // @todo Direct call to file_exists, which should be ok, because tmp, but still could be improved.
      if (isset($item_files[$imageName]['image']) && file_exists($item_files[$imageName]['image'])) {
        // All good.
      }
      // If status is success.  When converting (API) allow files that are bigger
      elseif (
        $image['image']['status'] == ApiController::STATUS_SUCCESS ||
        ($image['image']['status'] == ApiController::STATUS_OPTIMIZED_BIGGER && is_object($converter))
      ) {
        $tempFile = $downloadHelper->downloadFile($image['image']['url']);      
        if (is_object($tempFile)) {
          $item_files[$imageName]['image'] = $tempFile->getFullPath();
          $imageArray[$imageName]['image']['file'] = $tempFile->getFullPath();
        }
        else
        {
          
           $imageArray[$imageName]['image']['status'] = RequestManager::STATUS_CONNECTION_ERROR;
        }

      }

      if (!isset($item_files[$imageName]['webp']) && $image['webp']['status'] == ApiController::STATUS_SUCCESS) {
        $tempFile = $downloadHelper->downloadFile($image['webp']['url']);
        if (is_object($tempFile)) {
          $item_files[$imageName]['webp'] = $tempFile->getFullPath();
          $imageArray[$imageName]['webp']['file'] = $tempFile->getFullPath();
        }
      } elseif ($image['webp']['status'] == ApiController::STATUS_OPTIMIZED_BIGGER) {
        $item_files[$imageName]['webp'] = ApiController::STATUS_OPTIMIZED_BIGGER;
      } elseif ($image['webp']['status'] == ApiController::STATUS_NOT_COMPATIBLE) {
        $item_files[$imageName]['webp'] = ApiController::STATUS_NOT_COMPATIBLE;
      }
      //STATUS_NOT_COMPATIBLE

      if (!isset($item_files[$imageName]['avif']) && $image['avif']['status'] == ApiController::STATUS_SUCCESS) {
        $tempFile = $downloadHelper->downloadFile($image['avif']['url']);
        if (is_object($tempFile)) {
          $item_files[$imageName]['avif'] = $tempFile->getFullPath();
          $imageArray[$imageName]['avif']['file'] = $tempFile->getFullPath();
        }
      } elseif ($image['avif']['status'] == ApiController::STATUS_OPTIMIZED_BIGGER) {
        $item_files[$imageName]['avif'] = ApiController::STATUS_OPTIMIZED_BIGGER;
      } elseif ($image['avif']['status'] == ApiController::STATUS_NOT_COMPATIBLE) {
        $item_files[$imageName]['avif'] = ApiController::STATUS_NOT_COMPATIBLE;
      }
    }

    $successData['files'] = $imageArray;
    // Bit strange but imageModel currently expects this.
    $successData['data'] = $qItem->result()->data;
    $qItem->setData('files', $item_files);

    $converter = Converter::getConverter($imageModel, true);

    if (is_object($converter) && $converter->isConverterFor('api')) {
      $optimizedResult = $converter->handleConverted($successData);
      if (true === $optimizedResult) {
        ResponseController::addData($item_id, 'message', __('File Converted', 'shortpixel-image-optimiser'));
        $status = ApiController::STATUS_CONVERTED;
      } else {
        ResponseController::addData($item_id, 'message', __('File conversion failed.', 'shortpixel-image-optimiser'));
        $q->itemFailed($qItem, true);
        Log::addError('File conversion failed with data ', $successData);
        $status = ApiController::STATUS_FAIL;
      }
    } else {
      if (is_object($converter)) {
        $successData = $converter->handleConvertedFilter($successData);
      }

      $optimizedResult = $imageModel->handleOptimized($successData);
      if (true === $optimizedResult)
        $status = ApiController::STATUS_SUCCESS;
      else {
        $status = ApiController::STATUS_FAIL;
      }
    }

    $qItem->block(false);
    $q->updateItem($qItem);

    return $status;
  }

  protected function deleteTempFiles(QueueItem $qItem)
  {
    if (! is_null($qItem->data()->files)) {
      return false;
    }

    $files = $qItem->files;
    $fs = \wpSPUI()->filesystem();

    foreach ($files as $name => $data) {
      foreach ($data as $tmpPath) {
        if (is_numeric($tmpPath)) // Happens when result is bigger status is set.
          continue;

        $tmpFile = $fs->getFile($tmpPath);
        if ($tmpFile->exists())
          $tmpFile->delete();
      }
    }
  }
} // class
