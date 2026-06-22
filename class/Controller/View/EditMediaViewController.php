<?php
namespace SPUI\Controller\View;

use SPUI\Controller\Front\CDNController;
use SPUI\Controller\Optimizer\OptimizeAiController;
use SPUI\Controller\QueueController;

if ( ! defined( 'ABSPATH' ) ) {
 exit; // Exit if accessed directly.
}

use SPUI\ShortPixelLogger\ShortPixelLogger as Log;

use SPUI\Helper\UiHelper as UiHelper;

use SPUI\Controller\Queue\QueueItems as QueueItems;
use SPUI\Model\AiDataModel;
use SPUI\Model\File\FileModel as FileModel;


// Future contoller for the edit media metabox view.
class EditMediaViewController extends \SPUI\ViewController
{
      protected $template = 'view-edit-media';
  //    protected $model = 'image';

      protected $post_id;
      protected $legacyViewObj;

      protected $imageModel;
      protected $hooked;

			protected static $instance;

      protected function loadHooks()
      {
            add_action( 'add_meta_boxes_attachment', array( $this, 'addMetaBox') );
          //  add_action( 'attachment_fields_to_edit', [ $this, 'addAIAlter'], 10, 2);
            $this->hooked = true;
      }

      public function load()
      {
        if (! $this->hooked)
          $this->loadHooks();

					$fs = \wpSPUI()->filesystem();
					$fs->startTrustedMode();

      }

      public function addMetaBox()
      {
          add_meta_box(
              'shortpixel_info_box',          // this is HTML id of the box on edit screen
              __('ShortPixel Info', 'shortpixel-upscale-image'),    // title of the box
              array( $this, 'doMetaBox'),   // function to be called to display the info
              null,//,        // on which edit screen the box should appear
              'side'//'normal',      // part of page where the box should appear
              //'default'      // priority of the box
          );
      }

      /** Wordpress Filter to ( temp ) add a alt button for AI to the interface.
       * 
       * @param array $fields 
       * @param object $post 
       * @return array 
       */
      public function addAIAlter($fields, $post)
      { 
          $post_id = intval($post->ID);
          $fields['aibutton'] = [
              'label' => __('ShortPixel AI Data', 'shortpixel-upscale-image'), 
              'input' => 'html', 
              'html' => "<a href='javascript:window.SPUIProcessor.screen.RequestAlt($post_id)' class='button button-secondary'>" . __('Generate', 'shortpixel-upscale-image') . "</a>
                 <div class='shortpixel-alt-messagebox' id='shortpixel-ai-messagebox-$post_id'>&nbsp;</div>
               ",
          ];
         
          return $fields;
      }

      public function dometaBox($post)
      {
          UiHelper::setOutputHandler('edit-media');

          $this->post_id = $post->ID;
					$this->view->debugInfo = array();
					$this->view->id = $this->post_id;
					$this->view->list_actions = '';

          $fs = \wpSPUI()->filesystem();
          $this->imageModel = $fs->getMediaImage($this->post_id);

					// Asking for something non-existing.
					if ($this->imageModel === false)
					{
						$this->view->status_message = __('File Error. This could be not an image or the file is missing', 'shortpixel-upscale-image');

						$this->loadView();
						return false;
					}

          $this->view->status_message = null;

         	$this->view->text = UiHelper::getStatusText($this->imageModel);
          $this->view->list_actions = UiHelper::getListActions($this->imageModel);
          $this->view->image = [ 'width' => $this->imageModel->get('width'), 'height' => $this->imageModel->get('height'), 'extension' => $this->imageModel->getExtension() ];

          if ( count($this->view->list_actions) > 0)
            $this->view->list_actions = UiHelper::renderBurgerList($this->view->list_actions, $this->imageModel);
          else
            $this->view->list_actions = '';

          $this->view->actions = UiHelper::getActions($this->imageModel);
          $this->view->stats = $this->getStatistics();

          if (! $this->userIsAllowed)
          {
            $this->view->actions = array();
            $this->view->list_actions = '';
          }

          if(true === \wpSPUI()->env()->is_debug )
          {
            $this->view->debugInfo = $this->getDebugInfo();
          }

          $this->loadView();
      }

      protected function getStatusMessage()
      {
          return UIHelper::renderSuccessText($this->imageModel);
      }

      protected function getStatistics()
      {
        $stats = [];
        $imageObj = $this->imageModel;
        $did_keepExif = $imageObj->getMeta('did_keepExif');

				$did_convert = $imageObj->getMeta()->convertMeta()->isConverted();
        $resize = $imageObj->getMeta('resize');

				// Not optimized, not data.
				if (! $imageObj->isOptimized())
					return array();


        $exifData = UIHelper::getExifDisplayValues($did_keepExif);

        if (is_array($exifData) && isset($exifData['line']))
        {
           $stats[] = [$exifData['line'], ''];
        }


        if (true === $did_convert )
        {
					$ext = $imageObj->getMeta()->convertMeta()->getFileFormat();
					/* translators: %s is the original file format extension. */
          $stats[] = array(  sprintf(__('Converted from %s','shortpixel-upscale-image'), $ext), '');
        }
				elseif (false !== $imageObj->getMeta()->convertMeta()->didTry()) {
					$ext = $imageObj->getMeta()->convertMeta()->getFileFormat();
					$error = $imageObj->getMeta()->convertMeta()->getError(); // error code.
					$stats[] = array(UiHelper::getConvertErrorReason($error), '');
				}

        if ($resize == true)
        {
            $from = $imageObj->getMeta('originalWidth') . 'x' . $imageObj->getMeta('originalHeight');
            $to  = $imageObj->getMeta('resizeWidth') . 'x' . $imageObj->getMeta('resizeHeight');
						$type = ($imageObj->getMeta('resizeType') !== null) ? '(' . $imageObj->getMeta('resizeType') . ')' : '';
						/* translators: 1: resize type, 2: original dimensions, 3: resized dimensions. */
            $stats[] = array(sprintf(__('Resized %1$s %2$s to %3$s', 'shortpixel-upscale-image'), $type, $from, $to), '');
        }

        $tsOptimized = $imageObj->getMeta('tsOptimized');
        if ($tsOptimized !== null)
          $stats[] = array(__("Upscaled on :", 'shortpixel-upscale-image') . "<br /> ", UiHelper::formatTS($tsOptimized) );

				if ($imageObj->isOptimized())
				{
					/* translators: 1: opening paragraph with icon HTML, 2: opening knowledge-base link, 3: closing knowledge-base link. */
					$stats[] = array( sprintf(__('%1$s %2$s Read more about theses stats %3$s ', 'shortpixel-upscale-image'), '
					<p><img alt=' . esc_html('Info Icon', 'shortpixel-upscale-image')  . ' src=' . esc_url( wpSPUI()->plugin_url('res/img/info-icon.png' )) . ' style="margin-bottom: -4px;"/>', '<a href="https://shortpixel.com/knowledge-base/article/the-stats-from-the-shortpixel-column-in-the-media-library-explained/" target="_blank">', '</a></p>'), '');
				}

        return $stats;
      }

      protected function getDebugInfo()
      {
          if(! \wpSPUI()->env()->is_debug )
          {
            return [];
          }

          $meta = \wp_get_attachment_metadata($this->post_id);

          $fs = \wpSPUI()->filesystem();

					$imageObj = $this->imageModel;

					if ($imageObj->isProcessable())
					{
						 $optimizeData = $imageObj->getOptimizeData();
						 $urls = $optimizeData['urls'];
					}

          $optimizeAiController = OptimizeAiController::getInstance();


					$thumbnails = $imageObj->get('thumbnails');
					$processable = ($imageObj->isProcessable()) ? '<span class="green">Yes</span>' : '<span class="red">No</span> (' . $imageObj->getReason('processable') . ')';
          $optimized = ($imageObj->isOptimized()) ? '<span class="green">Yes</span>' : '<span class="red">No</span>';

					$anyFileType = ($imageObj->isProcessableAnyFileType()) ? '<span class="green">Yes</span>' : '<span class="red">No</span>';
					$restorable = ($imageObj->isRestorable()) ? '<span class="green">Yes</span>' : '<span class="red">No</span> (' . $imageObj->getReason('restorable') . ')';

					$hasrecord = ($imageObj->hasDBRecord()) ? '<span class="green">Yes</span>' : '<span class="red">No</span> ';

          $debugInfo = array();
          $debugInfo[] = array(__('URL (get attachment URL)', 'shortpixel-upscale-image'), wp_get_attachment_url($this->post_id));
          $debugInfo[] = array(__('File (get attached)', 'shortpixel-upscale-image'), get_attached_file($this->post_id));

					if ($imageObj->is_virtual())
					{
            $virtual = $imageObj->get('virtual_status');
            if($virtual == FileModel::$VIRTUAL_REMOTE)
              $vtext = 'Remote';
            elseif($virtual == FileModel::$VIRTUAL_STATELESS)
              $vtext = 'Stateless';
            else
              $vtext = 'Not set';

						$debugInfo[] = array(__('Is Virtual: ', 'shortpixel-upscale-image') . $vtext, $imageObj->getFullPath() );
					}

          $debugInfo[] = array(__('Size and Mime (ImageObj)', 'shortpixel-upscale-image'), $imageObj->get('width') . 'x' . $imageObj->get('height'). ' (' . $imageObj->get('mime') . ')');
          $debugInfo[] = array(__('Status (ShortPixel)', 'shortpixel-upscale-image'), $imageObj->getMeta('status') . ' '   );

						$debugInfo[] = array(__('Processable', 'shortpixel-upscale-image'), $processable);
          $debugInfo[] = array(__('Upscaled', 'shortpixel-upscale-image'), $optimized);
						$debugInfo[] = array(__('Avif/Webp needed', 'shortpixel-upscale-image'), $anyFileType);
						$debugInfo[] = array(__('Restorable', 'shortpixel-upscale-image'), $restorable);
						$debugInfo[] = array(__('Record', 'shortpixel-upscale-image'), $hasrecord);

					if ($imageObj->getMeta()->convertMeta()->didTry())
					{
						 $debugInfo[] = array(__('Converted', 'shortpixel-upscale-image'), ($imageObj->getMeta()->convertMeta()->isConverted() ?'<span class="green">Yes</span>' : '<span class="red">No</span> '));
						 $debugInfo[] = array(__('Checksum', 'shortpixel-upscale-image'), $imageObj->getMeta()->convertMeta()->didTry());
						 $debugInfo[] = array(__('Error', 'shortpixel-upscale-image'), $imageObj->getMeta()->convertMeta()->getError());
					}

          $debugInfo[] = array(__('WPML Duplicates', 'shortpixel-upscale-image'), json_encode($imageObj->getWPMLDuplicates()) );

					if ($imageObj->getParent() !== false)
					{
						 $debugInfo[] = array(__('WPML duplicate - Parent: ', 'shortpixel-upscale-image'), $imageObj->getParent());
					}

					if (isset($urls))
					{
						 $debugInfo[] = array(__('Upscale URLs', 'shortpixel-upscale-image'),  $urls);
					}

          $item = QueueItems::getImageItem($imageObj);

          if ($imageObj->isProcessable())
					{
             $item->setDebug();
             $item->newOptimizeAction();

             $counts = $item->data()->counts;

						 $returnEnqueue = $item->returnEnqueue();

						 $debugInfo[] = array(__('Image to Queue', 'shortpixel-upscale-image'), $returnEnqueue );
             $debugInfo[] = [__('Counts', 'shortpixel-upscale-image'), $counts];

					}

          if ( $optimizeAiController->isAIEnabled())
          {
            $aiDataModel = AiDataModel::getModelByAttachment($this->post_id);

            $aiProcessable = ($aiDataModel->isProcessable()) ? '<span class="green">Yes</span>' : '<span class="red">No</span> ';

            $debugInfo[] = ['AI - is Processable', $aiProcessable]; 

            if (true === $aiDataModel->isProcessable())
            {
              $debugInfo[] = ['Ai - Paramlist ', $aiDataModel->getOptimizeData() ];            
            }
            else
            {
               $debugInfo[] = ['Ai - Reason', $aiDataModel->getProcessableReason()];
            }
            if (true === $aiDataModel->isSomeThingGenerated())
            {
              $debugInfo[] = ['Ai -Generated ', $aiDataModel->getGeneratedData()];
            }

          }

          $debugInfo['imagemetadata'] = array(__('ImageModel Metadata (ShortPixel)', 'shortpixel-upscale-image'), $imageObj);
					$debugInfo[] = array('', '<hr>');

          $debugInfo['wpmetadata'] = array(__('WordPress Get Attachment Metadata', 'shortpixel-upscale-image'), $meta );
					$debugInfo[] = array('', '<hr>');

						if ($imageObj->hasBackup())
            	$backupFile = $imageObj->getBackupFile();
						else {
							 $backupFile = $fs->getFile($fs->getBackupDirectory($imageObj) . $imageObj->getBackupFileName());
						}

            $debugInfo[] = array(__('Backup Folder', 'shortpixel-upscale-image'), (string) $backupFile->getFileDir() );
						if ($imageObj->hasBackup())
								$backupText = __('Backup File :', 'shortpixel-upscale-image');
							else {
								$backupText = __('Target Backup File after upscaling (no backup) ', 'shortpixel-upscale-image');
							}
            $debugInfo[] = array( $backupText, (string) $backupFile . '(' . UiHelper::formatBytes($backupFile->getFileSize()) . ')' );

            $debugInfo[] =  array(__("No Main File Backup Available", 'shortpixel-upscale-image'), '');

					if ($imageObj->getMeta()->convertMeta()->isConverted())
					{
							$convertedBackup = ($imageObj->hasBackup(array('forceConverted' => true))) ? '<span class="green">Yes</span>' : '<span class="red">No</span>';
							$backup = $imageObj->getBackupFile(array('forceConverted' => true));
						 $debugInfo[] = array('Has converted backup', $convertedBackup);
						 if (is_object($backup))
						 	$debugInfo[] = array('Backup: ', $backup->getFullPath() );
				}

          if ($or = $imageObj->hasOriginal())
          {
             $original = $imageObj->getOriginalFile();
             $debugInfo[] = array(__('Has Original File: ', 'shortpixel-upscale-image'), $original->getFullPath()  . '(' . UiHelper::formatBytes($original->getFileSize()) . ')');
             $orbackup = $original->getBackupFile();

             $processable = ($original->isProcessable()) ? '<span class="green">Yes</span>' : '<span class="red">No</span> (' . $original->getReason('processable') . ')';
             
             $restorable = ($original->isRestorable()) ? '<span class="green">Yes</span>' : '<span class="red">No</span> (' . 		$original->getReason('restorable') . ')';

             $debugInfo[] = ['Original Processable:', $processable];
             $debugInfo[] = ['Original Restorable:', $restorable];


             if ($orbackup)
              $debugInfo[] = array(__('Has Backup Original Image', 'shortpixel-upscale-image'), $orbackup->getFullPath() . '(' . UiHelper::formatBytes($orbackup->getFileSize()) . ')');
						$debugInfo[] = array('', '<hr>');

          }


          if (! isset($meta['sizes']) )
          {
             $debugInfo[] = array('',  __('Thumbnails were not generated', 'shortpixel-upscale-image'));
          }
          else
          {
            foreach($thumbnails as $thumbObj)
            {
							$size = $thumbObj->get('size');

              $display_size = ucfirst(str_replace("_", " ", $size));

              if ($thumbObj === false)
              {
                $debugInfo[] =  array(__('Thumbnail not found / loaded: ', 'shortpixel-upscale-image'), $size );
                continue;
              }

              $url = $thumbObj->getURL(); 
              $filename = $thumbObj->getFullPath();
              $fileDir = $thumbObj->getFileDir();

							$backupFile = $thumbObj->getBackupFile();
							if ($thumbObj->hasBackup())
							{
								$backup = $backupFile->getFullPath();
									$backupText = __('Backup File :', 'shortpixel-upscale-image');
								}
								else {
									$backupFile = $fs->getFile($fs->getBackupDirectory($thumbObj) . $thumbObj->getBackupFileName());
									$backup = $backupFile->getFullPath();
									$backupText = __('Target Backup File after upscaling (no backup) ', 'shortpixel-upscale-image');
								}

              $width = $thumbObj->get('width');
              $height = $thumbObj->get('height');

					$processable = ($thumbObj->isProcessable()) ? '<span class="green">Yes</span>' : '<span class="red">No</span> (' . $thumbObj->getReason('processable') . ')';
					$restorable = ($thumbObj->isRestorable()) ? '<span class="green">Yes</span>' : '<span class="red">No</span> (' . 		$thumbObj->getReason('restorable') . ')';
					$hasrecord = ($thumbObj->hasDBRecord()) ? '<span class="green">Yes</span>' : '<span class="red">No</span> ';

					$dbid = $thumbObj->getMeta('databaseID');

              $debugInfo[] = array('', "<div class='$size previewwrapper'><img src='" . $url . "'><p class='label'>
							<b>URL:</b> $url ( $display_size - $width X $height ) <br><b>FileName:</b>  $filename <br>
              <b>FileDir:</b> $fileDir <br> <b> $backupText </b> $backup </p>
							<p><b>Processable: </b> $processable <br> <b>Restorable:</b>  $restorable <br> <b>Record:</b> $hasrecord ($dbid) </p>
							<hr></div>");
            }
          }
          return $debugInfo;
      }



} // controller .
