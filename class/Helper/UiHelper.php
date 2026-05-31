<?php
namespace ShortPixel\Helper;

if ( ! defined( 'ABSPATH' ) ) {
 exit; // Exit if accessed directly.
}

use ShortPixel\Model\Image\ImageModel as ImageModel;
use ShortPixel\Controller\ApiKeyController as ApiKeyController;
use ShortPixel\Controller\QuotaController as QuotaController;
use ShortPixel\Controller\QueueController as QueueController;
use ShortPixel\ShortPixelLogger\ShortPixelLogger as Log;


use ShortPixel\Model\AccessModel as AccessModel;
use ShortPixel\Model\AiDataModel;

class UiHelper
{

	private static $outputMode = 'admin';

	private static $knowledge_url = 'https://shortpixel.com/knowledge-base/article/common-shortpixel-bulk-processing-errors/'; // the URL of all knowledge.

	public static function setOutputHandler($name)
	{
		 	self::$outputMode = $name;
	}

  public static function renderBurgerList($actions, $imageObj)
  {
    $output = "";
    $id = $imageObj->get('id');
    $primary = isset($actions['optimizethumbs']) ? 'button-primary' : '';

    $output .= "<div class='sp-column-actions '>
                    <div class='sp-dropdown'>
                        <button onclick='ShortPixel.openImageMenu(event);' class='sp-dropbtn button dashicons dashicons-menu $primary' title='ShortPixel Actions'></button>";
    $output .= "<div id='sp-dd-$id' class='sp-dropdown-content'>";

    foreach($actions as $actionName => $actionData)
    {
        $link = ($actionData['type'] == 'js') ? 'javascript:' . $actionData['function'] : $actionData['function'];
        $output .= "<a href='" . $link . "' class='" . esc_attr($actionName) . "' >" . esc_html($actionData['text']) . "</a>";
    }

    $output .= "</div> <!--sp-dropdown-content--> </div> <!--sp-dropdown--> </div> <!--sp-column-actions--> ";
    return $output;
  }

  // SPUI: success text shown in the media column when the image has already been upscaled.
  public static function renderSuccessText($imageObj)
  {
    $iconUrl = esc_url( plugins_url( 'res/images/icon/shortpixel.svg', SHORTPIXEL_PLUGIN_FILE ) );
    return '<img class="spui-action-icon spui-caption-icon" src="' . $iconUrl . '" alt=""> '
         . esc_html__( 'This image has been upscaled by ShortPixel.', 'shortpixel-upscale-image' );
  }

  public static function compressionTypeToText($type)
  {

     switch($type)
     {
        case ImageModel::COMPRESSION_LOSSLESS:
           $text = __('Lossless', 'shortpixel-image-optimiser');
        break;
        case ImageModel::COMPRESSION_LOSSY:
            $text = __('Lossy', 'shortpixel-image-optimiser');
        break;
        case ImageModel::COMPRESSION_GLOSSY:
            $text = __('Glossy', 'shortpixel-image-optimiser');
        break;
        default:
            $text = __('No compression', 'shortpixel-image-optimiser');
        break; 
     }


      return $text;
  }

  // SPUI: no burger menu — all actions handled by the single Upscale button.
  public static function getListActions($mediaItem, $aiDataModel = null)
  {
    return [];
  }

  // SPUI: single "Upscale Now" button — disabled when in queue, not processable, or no quota.
  public static function getActions($mediaItem)
  {
    $actions         = [];
    $id              = $mediaItem->get('id');
    $keyControl      = ApiKeyController::getInstance();
    $quotaControl    = QuotaController::getInstance();
    $queueController = new QueueController();
    $access          = AccessModel::getInstance();

    if (! $keyControl->keyIsVerified())          { return []; }
    if (! $access->imageIsEditable($mediaItem))  { return []; }
    if ($id === 0)                               { return []; }

    // Always show the Upscale Now button; set disabled + title to communicate state.
    $action = self::getAction('optimize', $id);

    if (! $quotaControl->hasQuota())
    {
      $action['disabled'] = true;
      $action['title']    = __('No upscale quota available', 'shortpixel-upscale-image');
    }
    elseif ($queueController->isItemInQueue($mediaItem))
    {
      $action['disabled'] = true;
      $action['title']    = __('Upscaling in progress…', 'shortpixel-upscale-image');
    }
    elseif (! $mediaItem->isProcessable())
    {
      $action['disabled'] = true;
      $action['title']    = $mediaItem->getReason('processable');
    }

    $actions['optimize'] = $action;
    return $actions;
  }

  // SPUI: status text shown above/below the action button in the media column.
  public static function getStatusText($mediaItem)
  {
    $keyControl   = ApiKeyController::getInstance();
    $quotaControl = QuotaController::getInstance();
    $text         = '';

    if (! $keyControl->keyIsVerified())
    {
      $text = __('Invalid API Key. <a href="options-general.php?page=wp-shortpixel-settings">Check your Settings</a>', 'shortpixel-image-optimiser');
    }
    elseif ($mediaItem->get('id') === 0)
    {
      if ($mediaItem->isProcessable(true) === false)
      {
        $text  = __('Not Processable: ', 'shortpixel_image_optimiser');
        $text .= $mediaItem->getProcessableReason();
      }
    }
    elseif (! $mediaItem->exists())
    {
      $text = __('File does not exist.', 'shortpixel-image-optimiser');
    }
    elseif ($mediaItem->getMeta('status') < 0)
    {
      $text = $mediaItem->getMeta('errorMessage');
    }
    elseif ($mediaItem->isOptimized() || get_post_meta($mediaItem->get('id'), '_spui_scaled', true))
    {
      // Success state: icon + confirmation text.
      $iconUrl = esc_url( plugins_url( 'res/images/icon/shortpixel.svg', SHORTPIXEL_PLUGIN_FILE ) );
      $text    = '<img class="spui-action-icon spui-caption-icon" src="' . $iconUrl . '" alt=""> '
               . esc_html__( 'This image has been upscaled by ShortPixel.', 'shortpixel-upscale-image' );
    }
    elseif (! $quotaControl->hasQuota())
    {
      $text = sprintf(
        /* translators: %s: URL to ShortPixel pricing page */
        __( 'No upscale quota available. <a href="%s" target="_blank">Extend your quota</a>.', 'shortpixel-upscale-image' ),
        esc_url( 'https://shortpixel.com/login/' . $keyControl->getKeyForDisplay() )
      );
    }
    elseif (! $mediaItem->isProcessable())
    {
      $reason = $mediaItem->getReason('processable');
      if ($reason)
      {
        $text = esc_html($reason);
      }
    }

    return $text;
  }

  public static function getExifDisplayValues($exif)
  {
      if (! is_numeric($exif) || $exif < 0 ||$exif > 7 )
      {
         return false;
      }

      $removed = ($exif % 2 == 0) ? true : false;
      $seo = ($exif >= 2 && $exif <= 5) ? true : false;
      $ai = ($exif >= 2 & $exif <= 3) ? true : false;

      // 0 and 1 options are classic, without ai settings
      if ($exif <= 1)
      {
        $ai = $seo = null;
      }


      $status = [
        'removed' => $removed,
        'ai' => $seo,
        'seo' => $ai,
      ];


      $mainline =  sprintf(__('Exif: %s'), ($removed) ? __('Removed', 'shortpixel-image-optimiser') : __('Kept', 'shortpixel-image-optimiser'));

      if (! is_null($ai))
      {
         $mainline .=  sprintf(__(', AI %s '), ($ai) ? __('Allowed', 'shortpixel-image-optimiser') : __('Denied', 'shortpixel-image-optimiser'));
         $mainline .=  sprintf(__(', SEO %s'), ($seo) ? __('Allowed', 'shortpixel-image-optimiser') : __('Denied', 'shortpixel-image-optimiser'));
      }

      $status['line']  = $mainline;

      return $status;

  }

  // Defines all possible actions in the Ui
  public static function getAction($name, $id, $args = array())
  {
     $action = array('function' => '', 'type' => '', 'text' => '', 'display' => '');
     $keyControl = ApiKeyController::getInstance();

		 $compressionType = isset($args['compressionType']) ? $args['compressionType'] : null;

    switch($name)
    {
      case 'optimize':
         // SPUI: opens the editor preview popup (scale action).
         $action['function'] = 'window.ShortPixelProcessor.screen.OpenEditorById(' . $id . ', \'scale\', \'edit\')';
         $action['type']  = 'js';
         $action['text'] = __('Upscale Now', 'shortpixel-upscale-image');
         $action['display'] = 'button';
         $action['is-optimizable'] = true;
      break;
      case 'forceOptimize':
        $action['function'] = 'window.ShortPixelProcessor.screen.Optimize(' . $id . ', true)';
        $action['type']  = 'js';
        // SPUI: renamed from "Override exclusions and optimize now"
        $action['text'] = __('Override exclusions and upscale now', 'shortpixel-image-optimiser');
        $action['display'] = 'button';
        $action['is-optimizable'] = true;
      break;
			case 'cancelOptimize':
				 $action['function'] = 'window.ShortPixelProcessor.screen.CancelOptimizeItem(' . $id . ')';
				 $action['type']  = 'js';
				 $action['text'] = __('Cancel item upscaling', 'shortpixel-image-optimiser');
				 $action['display'] = 'button';
			break;
      case 'markCompleted':
          $action['function'] = 'window.ShortPixelProcessor.screen.MarkCompleted(' . $id . ')';
          $action['type']  = 'js';
          $action['text'] = __('Mark as Completed', 'shortpixel-image-optimiser');
          $action['display'] = 'button-secondary';
          $action['layout'] = 'paragraph';
          $action['title'] = __('This will cause the plugin to skip this image for upscaling', 'shortpixel-image-optimiser');
      break;
      case 'unMarkCompleted':
          $action['function'] = 'window.ShortPixelProcessor.screen.UnMarkCompleted(' . $id . ')';
          $action['type']  = 'js';
          $action['text'] = __('Click to unmark this item as done', 'shortpixel-image-optimiser');
          $action['display'] = 'js';
      break;
      case 'optimizethumbs':
          if (! is_null($compressionType))
          {
            $action['function'] = 'window.ShortPixelProcessor.screen.Optimize(' . $id . ', null, ' . $compressionType . ');';
          }
          else
          {
            $action['function'] = 'window.ShortPixelProcessor.screen.Optimize(' . $id . ');';
          }

          $action['type'] = 'js';
          $action['text']  = '';
          $action['display'] = 'inline';
          $action['is-optimizable'] = true;
      break;
      case 'retry':
         $action['function'] = 'window.ShortPixelProcessor.screen.Optimize(' . $id . ');';
         $action['type']  = 'js';
         $action['text'] = __('Retry', 'shortpixel-image-optimiser') ;
         $action['display'] = 'button';
         $action['is-optimizable'] = true;
     break;
		 case 'redo_legacy':
		 			$action['function'] = 'window.ShortPixelProcessor.screen.RedoLegacy(' . $id . ');';
		 			$action['type']  = 'js';
		 			$action['text'] = __('Redo Conversion', 'shortpixel-image-optimiser') ;
		 			$action['display'] = 'button';
		 break;

     case 'restore':
         $action['function'] = 'window.ShortPixelProcessor.screen.RestoreItem(' . $id . ');';
         $action['type'] = 'js';
         $action['text'] = __('Restore backup','shortpixel-image-optimiser');
         $action['display'] = 'inline';
     break;

     case 'compare':
        $action['function'] = 'ShortPixel.loadComparer(' . $id . ')';
        $action['type'] = 'js';
        $action['text'] = __('Compare', 'shortpixel-image-optimiser');
        $action['display'] = 'inline';
     break;
     case 'compare-custom':
        $action['function'] = 'ShortPixel.loadComparer(' . $id . ',"custom")';
        $action['type'] = 'js';
        $action['text'] = __('Compare', 'shortpixel-image-optimiser');
        $action['display'] = 'inline';
     break;
     case 're-upscale':
        // SPUI: Re-upscale button shown after a successful upscale.
        $action['function'] = 'window.ShortPixelProcessor.screen.OpenEditorById(' . $id . ', \'scale\', \'edit\')';
        $action['type']     = 'js';
        $action['text']     = __('Re-upscale', 'shortpixel-upscale-image');
        $action['display']  = 'button';
        $action['is-optimizable'] = true;
     break;
     case 're-upscale-glossy':
        $action['function'] = 'window.ShortPixelProcessor.screen.ReOptimize(' . $id . ',' . ImageModel::COMPRESSION_GLOSSY . ')';
        $action['type'] = 'js';
        $action['text'] = __('Re-upscale Glossy','shortpixel-image-optimiser');
        $action['display'] = 'inline';
     break;
     case 're-upscale-lossy':
        $action['function'] = 'window.ShortPixelProcessor.screen.ReOptimize(' . $id . ',' . ImageModel::COMPRESSION_LOSSY . ')';
        $action['type'] = 'js';
        $action['text'] = __('Re-upscale Lossy','shortpixel-image-optimiser');
        $action['display'] = 'inline';
     break;
     case 're-upscale-lossless':
        $action['function'] = 'window.ShortPixelProcessor.screen.ReOptimize(' . $id . ',' . ImageModel::COMPRESSION_LOSSLESS . ')';
        $action['type'] = 'js';
        $action['text'] = __('Re-upscale Lossless','shortpixel-image-optimiser');
        $action['display'] = 'inline';
     break;
		 case 're-upscale-smartcrop':
        $action['function'] = 'window.ShortPixelProcessor.screen.ReOptimize(' . $id . ',' . $compressionType . ',' . ImageModel::ACTION_SMARTCROP . ')';
        $action['type'] = 'js';
        $action['text'] = __('Re-upscale with SmartCrop','shortpixel-image-optimiser');
        $action['display'] = 'inline';
     break;
		 case 're-upscale-smartcropless':
        $action['function'] = 'window.ShortPixelProcessor.screen.ReOptimize(' . $id . ',' . $compressionType . ',' . ImageModel::ACTION_SMARTCROPLESS . ')';
        $action['type'] = 'js';
        $action['text'] = __('Re-upscale without SmartCrop','shortpixel-image-optimiser');
        $action['display'] = 'inline';
     break;
     case 'shortpixel-generateai': 
      $action['function'] = 'window.ShortPixelProcessor.screen.RequestAlt(' . $id . ')';
      $action['type'] = 'js';
      $action['text'] = __('Generate image SEO data','shortpixel-image-optimiser');     
      $action['ai-action'] = true;
      break; 
     case 'extendquota':
        $action['function'] = 'https://shortpixel.com/login/'. $keyControl->getKeyForDisplay();
        $action['type'] = 'button';
        $action['text'] = __('Extend Quota','shortpixel-image-optimiser');
        $action['display'] = 'button';
     break;
     case 'checkquota':
        $action['function'] = 'ShortPixel.checkQuota()';
        $action['type'] = 'js';
        $action['display'] = 'button';
        $action['text'] = __('Check&nbsp;&nbsp;Quota','shortpixel-image-optimiser');
     break;
   }

   return $action;
  }

	public static function getConvertErrorReason($error)
	{
		switch($error)
		{
			case -1: //ERROR_LIBRARY:
				$reason = __('PNG Library is not present or not working', 'shortpixel-image-optimiser');
			break;
			case -2: //ERROR_PATHFAIL:
				$reason = __('Could not create path', 'shortpixel-image-optimiser');
			break;
			case -3: //ERROR_RESULTLARGER:
				$reason  = __('Result file is larger','shortpixel-image-optimiser');
			break;
			case -4: // ERROR_WRITEERROR
				$reason = __('Could not write result file', 'shortpixel-image-optimiser');
			break;
			case -5: // ERROR_BACKUPERROR
				$reason = __('Could not create backup', 'shortpixel-image-optimiser');
			break;
			case -6:  // ERROR_TRANSPARENT
				$reason = __('Image is transparent', 'shortpixel-image-optimiser');
			break;
			default:
				$reason = sprintf(__('Unknown error %s', 'shortpixel-image-optimiser'), $error);
			break;
		}


		$message = sprintf(__('Not converted: %s ', 'shortpixel-image-optimiser'), $reason);
		return $message;
	}

	public static function getKBSearchLink($subject)
	{
			return esc_url(self::$knowledge_url); // . sanitize_text_field($subject)); //the KB search doesn't work anymore
	}

	// @param MediaLibraryModel Object $imageItem
	// @param String $size  Preferred size
	// @param String Preload The preloader tries to guess what the preview might be for a more smooth process. Ignore optimize / backup
	public static function findBestPreview($imageItem, $size = 800, $preload = false)
	{
		 	$closestObj = $imageItem;

			// set the standard.
			if ($imageItem->getExtension() == 'pdf') // try not to select non-showable extensions.
				$bestdiff = 0;
			else
				$bestdiff = abs($imageItem->get('width') - $size);

			$thumbnails = $imageItem->get('thumbnails');

			if (! is_array($thumbnails))
			{
				 return $closestObj; // nothing more to do.
			}

			foreach($thumbnails as $thumbnail)
			{
				 if (! $preload && (! $thumbnail->isOptimized() || ! $thumbnail->hasBackup()))
				 	continue;

					$diff = abs($thumbnail->get('width') - $size);
					if ($diff < $bestdiff)
					{
						 $closestObj = $thumbnail;
						 $bestdiff = $diff;
					}
			}

			return $closestObj;
	}

  public static function formatTS($ts)
  {
      //$format = get_option('date_format') .' @ ' . date_i18n(get_option('time_format');
			if (function_exists('wp_date'))
			{
      	$date = wp_date(get_option('date_format'), $ts);
				$date .= ' @ ' . wp_date(get_option('time_format'), $ts);
			}
			else
			{
      	$date = date_i18n(get_option('date_format'), $ts);
				$date .= ' @ ' . date_i18n(get_option('time_format'), $ts);

			}
      return $date;
  }

  public static function formatBytes($bytes, $precision = 2) {
      $units = array('B', 'KB', 'MB', 'GB', 'TB');

      $bytes = max($bytes, 0);
      $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
      $pow = min($pow, count($units) - 1);

      $bytes /= pow(1024, $pow);

      return number_format_i18n(round($bytes, $precision), $precision) . ' ' . $units[$pow];
  }

	public static function formatNumber($number, $precision = 2)
	{
			global $wp_locale;
			$decimalpoint = isset($wp_locale->number_format['decimal_point']) ? $wp_locale->number_format['decimal_point'] : '.';
			$number =  number_format_i18n( (float) $number, $precision);

 			$hasDecimal = (strpos($number, $decimalpoint) === false) ? false : true;

			// Don't show trailing zeroes if number is a whole unbroken number. -> string comparison because number_format_i18n returns string.
			if ($decimalpoint !== false && $hasDecimal && substr($number, strpos($number, $decimalpoint) + 1) === '00')
			{
				 $number = substr($number, 0, strpos($number, $decimalpoint));
			}
			// Some locale's have no-breaking-space as thousands separator. This doesn't work well in JS / Cron Shell so replace with space.
			$number = str_replace('&nbsp;', ' ', $number);
		  return $number;
	}

	public static function formatDate( $date ) {

	if ( '0000-00-00 00:00:00' === $date->format('Y-m-d ') ) {
			$h_time = '';
	} else {
			$time   = $date->format('U'); //get_post_time( 'G', true, $post, false );
			if ( ( abs( $t_diff = time() - $time ) ) < DAY_IN_SECONDS ) {
					if ( $t_diff < 0 ) {
							$h_time = sprintf( __( '%s from now' ), human_time_diff( $time ) );
					} else {
							$h_time = sprintf( __( '%s ago' ), human_time_diff( $time ) );
					}
			} else {
					$h_time = $date->format( 'Y/m/d' );
			}
	}

	return $h_time;
}

	protected static function convertImageTypeName($name, $type)
	{
		if ($type == 'webp')
		{
			$is_double = \wpSPIO()->env()->useDoubleWebpExtension();
		}
		if ($type == 'avif')
		{
			$is_double = \wpSPIO()->env()->useDoubleAvifExtension();
		}

		if ($is_double)
		{
			 return $name . '.' . $type;
		}
		else
		{
			 return substr($name, 0, strrpos($name, '.')) . '.' . $type;
		}

	}

  /* Strings on settings page that need to be available for both JS and PHP  */
  public static function getSettingsStrings($name = false)
  {

      $strings = array(
      );

      $exclusion_types = array(
          'name' => __('Image Name', 'shortpixel-image-optimiser'),
          'path' => __('Image Path', 'shortpixel-image-optimiser'),
          'size' => __('Image Size', 'shortpixel-image-optimiser'),
          'filesize' => __('Image File Size', 'shortpixel-image-optimiser'),
          'date' => __('Date', 'shortpixel-image-optimiser'), 
      );

      $exclusion_apply = array(
           'all' => __('All', 'shortpixel-image-optimiser'),
           'only-thumbs' => __('Only Thumbnails', 'shortpixel-image-optimiser'),
           'only-custom' =>  __('Only Custom Media Images', 'shortpixel-image-optimiser'),
           'selected-thumbs' => __('Selected Images', 'shortpixel-image-optimiser'),
      );

      $dashboard_string = [
            'ok' => __('Everything ok', 'shortpixel-image-optimiser'),
            'warning' => __('Improvement possible', 'shortpixel-image-optimiser'),
            'alert' => __('Action needed', 'shortpixel-image-optimiser'),
      ];

      $ai_string = [
            'imagemodaltitle' => __('Select an image for AI SEO data preview', 'shortpixel-image-optimiser'), 
            'selectimage' => __('Use this image', 'shortpixel-image-optimiser'),
            'preview_requested' => __('Working on your AI SEO data preview. This may take a while ... ', 'shortpixel-image-optimiser'),
      ];

      $strings['exclusion_types'] = $exclusion_types;
      $strings['exclusion_apply'] = $exclusion_apply;
      $strings['dashboard_strings'] = $dashboard_string;
      $strings['ai_strings'] = $ai_string; 

      if ($name !== false && isset($strings[$name]))
      {
         return $strings[$name];
      }

      return $strings;
  }

  public static function getIcon($path, $args = array())
  {
      $defaults = array(

      );

      $icon_url = plugins_url($path, SHORTPIXEL_PLUGIN_FILE);

      $attr = ''; 
      if (isset($args['width']))
      {
         $attr .= ' width="' . $args['width'] . '"'; 

      }

      if (isset($args['height']))
      {
         $attr . ' height="' . $args['height'] . '"';
      }


      $html = sprintf('<img src="%s" class="icon"  %s />', esc_attr($icon_url), $attr);

      return $html;

  }



} // class
