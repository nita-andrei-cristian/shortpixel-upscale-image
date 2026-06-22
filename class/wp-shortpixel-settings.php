<?php
use SPUI\ShortPixelLogger\ShortPixelLogger as Log;

use SPUI\Model\SettingsModel as SettingsModel;

if ( ! defined( 'ABSPATH' ) ) {
 exit; // Exit if accessed directly.
}


/** Settings Model **/
// SPUI: renamed from the global WPShortPixelSettings so SPUI can stay co-active with
// SPIO, which declares its own global WPShortPixelSettings. Mirrors SPAATG_Settings.
class SPUI_Settings extends \SPUI\Model {
    private $_apiKey = '';
    private $_compressionType = 1;
    private $_keepExif = 0;
    private $_processThumbnails = 1;
    private $_CMYKtoRGBconversion = 1;
    private $_backupImages = 1;
    private $_verifiedKey = false;

    private $_resizeImages = false;
    private $_resizeWidth = 0;
    private $_resizeHeight = 0;

    private static $_optionsMap = array(
        //This one is accessed also directly via get_option
    //    'frontBootstrap' => array('key' => 'spui-short-pixel-front-bootstrap', 'default' => null, 'group' => 'options'), //set to 1 when need the plugin active for logged in user in the front-end
      //  'lastBackAction' => array('key' => 'spui-short-pixel-last-back-action', 'default' => null, 'group' => 'state'), //when less than 10 min. passed from this timestamp, the front-bootstrap is ineffective.

        //optimization options
        'apiKey' => array('key' => 'spui-short-pixel-apiKey', 'default' => '', 'group' => 'options'),
        'verifiedKey' => array('key' => 'spui-short-pixel-verifiedKey', 'default' => false, 'group' => 'options'),
        'compressionType' => array('key' => 'spui-short-pixel-compression', 'default' => 1, 'group' => 'options'),
        'processThumbnails' => array('key' => 'spui-short-process_thumbnails', 'default' => 1, 'group' => 'options'),
				'useSmartcrop' => array('key' => 'spui-usesmartcrop', 'default' => 0, 'group' => 'options'),
        'keepExif' => array('key' => 'spui-short-pixel-keep-exif', 'default' => 0, 'group' => 'options'),
        'CMYKtoRGBconversion' => array('key' => 'spui-short-pixel_cmyk2rgb', 'default' => 1, 'group' => 'options'),
        'createWebp' => array('key' => 'spui-short-create-webp', 'default' => null, 'group' => 'options'),
        'createAvif' => array('key' => 'spui-short-create-avif', 'default' => null, 'group' => 'options'),
        'deliverWebp' => array('key' => 'spui-short-pixel-create-webp-markup', 'default' => 0, 'group' => 'options'),
        'optimizeRetina' => array('key' => 'spui-short-pixel-optimize-retina', 'default' => 1, 'group' => 'options'),
        'optimizeUnlisted' => array('key' => 'spui-short-pixel-optimize-unlisted', 'default' => 0, 'group' => 'options'),
        'backupImages' => array('key' => 'spui-short-backup_images', 'default' => 1, 'group' => 'options'),
        'resizeImages' => array('key' => 'spui-short-pixel-resize-images', 'default' => false, 'group' => 'options'),
        'resizeType' => array('key' => 'spui-short-pixel-resize-type', 'default' => null, 'group' => 'options'),
        'resizeWidth' => array('key' => 'spui-short-pixel-resize-width', 'default' => 0, 'group' => 'options'),
        'resizeHeight' => array('key' => 'spui-short-pixel-resize-height', 'default' => 0, 'group' => 'options'),
        'siteAuthUser' => array('key' => 'spui-short-pixel-site-auth-user', 'default' => null, 'group' => 'options'),
        'siteAuthPass' => array('key' => 'spui-short-pixel-site-auth-pass', 'default' => null, 'group' => 'options'),
        'autoMediaLibrary' => array('key' => 'spui-short-pixel-auto-media-library', 'default' => 1, 'group' => 'options'),
        'doBackgroundProcess' => array('key' => 'spui-short-pixel-backgroundprocess', 'default' => 0, 'group' => 'options'),
        'optimizePdfs' => array('key' => 'spui-short-pixel-optimize-pdfs', 'default' => 1, 'group' => 'options'),
        'excludePatterns' => array('key' => 'spui-short-pixel-exclude-patterns', 'default' => array(), 'group' => 'options'),
        'png2jpg' => array('key' => 'spui-short-pixel-png2jpg', 'default' => 0, 'group' => 'options'),
        'excludeSizes' => array('key' => 'spui-short-pixel-excludeSizes', 'default' => array(), 'group' => 'options'),
				'currentVersion' => array('key' => 'spui-short-pixel-currentVersion', 'default' => null, 'group' => 'options'),
				'showCustomMedia' => array('key' => 'spui-short-pixel-show-custom-media', 'default' => 1, 'group' => 'options'),

        //CloudFlare
        /*'cloudflareEmail'   => array( 'key' => 'spui-short-pixel-cloudflareAPIEmail', 'default' => '', 'group' => 'options'),
        'cloudflareAuthKey' => array( 'key' => 'spui-short-pixel-cloudflareAuthKey', 'default' => '', 'group' => 'options'), */
        'cloudflareZoneID'  => array( 'key' => 'spui-short-pixel-cloudflareAPIZoneID', 'default' => '', 'group' => 'options'),
        'cloudflareToken'   => array( 'key' => 'spui-short-pixel-cloudflareToken', 'default' => '', 'group' => 'options'),

        //optimize other images than the ones in Media Library
        'includeNextGen' => array('key' => 'spui-short-pixel-include-next-gen', 'default' => null, 'group' => 'options'),
        'hasCustomFolders' => array('key' => 'spui-short-pixel-has-custom-folders', 'default' => false, 'group' => 'options'),
        //'customBulkPaused' => array('key' => 'spui-short-pixel-custom-bulk-paused', 'default' => false, 'group' => 'options'),

        //uninstall
  //      'removeSettingsOnDeletePlugin' => array('key' => 'spui-short-pixel-remove-settings-on-delete-plugin', 'default' => false, 'group' => 'options'),

        //stats, notices, etc.
				// @todo Most of this can go. See state machine comment.
        'currentStats' => array('key' => 'spui-short-pixel-current-total-files', 'default' => null, 'group' => 'state'),
      //  'fileCount' => array('key' => 'spui-short-pixel-fileCount', 'default' => 0, 'group' => 'state'),
        'thumbsCount' => array('key' => 'spui-short-pixel-thumbnail-count', 'default' => 0, 'group' => 'state'),
        //'under5Percent' => array('key' => 'spui-short-pixel-files-under-5-percent', 'default' => 0, 'group' => 'state'),
    //    'savedSpace' => array('key' => 'spui-short-pixel-savedSpace', 'default' => 0, 'group' => 'state'),
       // 'apiRetries' => array('key' => 'spui-short-pixel-api-retries', 'default' => 0, 'group' => 'state'),
      //  'totalOptimized' => array('key' => 'spui-short-pixel-total-optimized', 'default' => 0, 'group' => 'state'),
      //  'totalOriginal' => array('key' => 'spui-short-pixel-total-original', 'default' => 0, 'group' => 'state'),
        'quotaExceeded' => array('key' => 'spui-short-pixel-quota-exceeded', 'default' => 0, 'group' => 'state'),
        'httpProto' => array('key' => 'spui-short-pixel-protocol', 'default' => 'https', 'group' => 'state'),
        'downloadProto' => array('key' => 'spui-short-pixel-download-protocol', 'default' => null, 'group' => 'state'),

				'downloadArchive' => array('key' => 'spui-short-pixel-download-archive', 'default' => -1, 'group' => 'state'),

        'activationDate' => array('key' => 'spui-short-pixel-activation-date', 'default' => null, 'group' => 'state'),
        'mediaLibraryViewMode' => array('key' => 'spui-short-pixel-view-mode', 'default' => false, 'group' => 'state'),
        'redirectedSettings' => array('key' => 'spui-short-pixel-redirected-settings', 'default' => null, 'group' => 'state'),
      //  'convertedPng2Jpg' => array('key' => 'spui-short-pixel-converted-png2jpg', 'default' => array(), 'group' => 'state'),
				'unlistedCounter' => array('key' => 'spui-short-pixel-unlisted-counter', 'default' => 0, 'group' => 'state'),
    );



    // This  array --  field_name -> (s)anitize mask
    protected $model = array(
        'apiKey' => array('s' => 'string'), // string
    //    'verifiedKey' => array('s' => 'string'), // string
        'compressionType' => array('s' => 'int'), // int
        'resizeWidth' => array('s' => 'int'), // int
        'resizeHeight' => array('s' => 'int'), // int
        'processThumbnails' => array('s' => 'boolean'), // checkbox
				'useSmartcrop' => array('s' => 'boolean'),
        'backupImages' => array('s' => 'boolean'), // checkbox
        'keepExif' => array('s' => 'int'), // checkbox
        'resizeImages' => array('s' => 'boolean'),
        'resizeType' => array('s' => 'string'),
        'includeNextGen' => array('s' => 'boolean'), // checkbox
        'png2jpg' => array('s' => 'int'), // checkbox
        'CMYKtoRGBconversion' => array('s' => 'boolean'), //checkbox
        'createWebp' => array('s' => 'boolean'), // checkbox
        'createAvif' => array('s' => 'boolean'),  // checkbox
        'deliverWebp' => array('s' => 'int'), // checkbox
        'optimizeRetina' => array('s' => 'boolean'), // checkbox
        'optimizeUnlisted' => array('s' => 'boolean'), // $checkbox
        'optimizePdfs' => array('s' => 'boolean'), //checkbox
        'excludePatterns' => array('s' => 'exception'), //  - processed, multi-layer, so skip
        'siteAuthUser' => array('s' => 'string'), // string
        'siteAuthPass' => array('s' => 'string'), // string
      //  'frontBootstrap' => array('s' =>'boolean'), // checkbox
        'autoMediaLibrary' => array('s' => 'boolean'), // checkbox
        'doBackgroundProcess' => array('s' => 'boolean'), // checkbox
        'excludeSizes' => array('s' => 'array'), // Array
      //  'cloudflareEmail' => array('s' => 'string'), // string
      //  'cloudflareAuthKey' => array('s' => 'string'), // string
        'cloudflareZoneID' => array('s' => 'string'), // string
        'cloudflareToken' => array('s' => 'string'),

				'showCustomMedia' => array('s' => 'boolean'),
        'currentStats' => array('s' => 'array')
    );

      public static function resetOptions() {
        foreach(self::$_optionsMap as $key => $val) {
            delete_option($val['key']);
        }
        delete_option("spui-short-pixel-bulk-previous-percent");
    }

    public static function onActivate() {
        /*if(!self::getOpt('spui-short-pixel-verifiedKey', false)) {
            update_option('spui-short-pixel-activation-notice', true, 'no');
        } */
        update_option( 'spui-short-pixel-activation-date', time(), 'no');

        delete_option( 'spui-short-pixel-current-total-files');
				//delete_option('spui-short-pixel-remove-settings-on-delete-plugin');

        /*
				if (isset(self::$_optionsMap['removeSettingsOnDeletePlugin']) && isset(self::$_optionsMap['removeSettingsOnDeletePlugin']['key']))
				{
        	delete_option(self::$_optionsMap['removeSettingsOnDeletePlugin']['key']);
				} */

        $settingsModel = SettingsModel::getInstance();
				$updated = false;

				foreach(self::$_optionsMap as $option_name => $data)
				{
					 $value = self::getOpt($data['key'], $data['default']);
					 $bool = $settingsModel->setIfEmpty($option_name, $value);

					 // Remove setting if set, or if it doesn't exist in model anymore
					 if (true === $bool || false === $settingsModel->exists($option_name))
					 {
            //  Log::AddTrace('Would delete non-existing? setting ' . $option_name);
					//	  delete_option($data['key']);
					 		$updated = true;
					 }
				}


    }

    public static function onDeactivate() {

        delete_option('spui-short-pixel-activation-notice');
				delete_option('spui-short-pixel-bulk-last-status'); // legacy shizzle
				delete_option('spui-short-pixel-current-total-files');
				delete_option('spui-short-pixel-remove-settings-on-delete-plugin');

				// Bulk State machine legacy
				$bulkLegacyOptions = array(
						'spui-short-pixel-bulk-type',
						'spui-short-pixel-bulk-last-status',
						'spui-short-pixel-query-id-start',
						'spui-short-pixel-query-id-stop',
						'spui-short-pixel-bulk-count',
						'spui-short-pixel-bulk-previous-percent',
						'spui-short-pixel-bulk-processed-items',
						'spui-short-pixel-bulk-done-count',
						'spui-short-pixel-last-bulk-start-time',
						'spui-short-pixel-last-bulk-success-time',
						'spui-short-pixel-bulk-running-time',
						'spui-short-pixel-cancel-pointer',
						'spui-short-pixel-skip-to-custom',
						'spui-short-pixel-bulk-ever-ran',
						'spui-short-pixel-flag-id',
						'spui-short-pixel-failed-imgs',
						'bulkProcessingStatus',
						'spui-short-pixel-prioritySkip',
				);

				$removedStats = array(
						'spui-short-pixel-helpscout-optin',
						'spui-short-pixel-activation-notice',
						'spui-short-pixel-dismissed-notices',
						'spui-short-pixel-media-alert',
				);

				$removedOptions = array(
						'spui-short-pixel-remove-settings-on-delete-plugin',
						'spui-short-pixel-custom-bulk-paused',
						'spui-short-pixel-last-back-action',
						'spui-short-pixel-front-bootstrap',
				);

        // Settings completely removed during the settings redo
        $settingsRevamp = [
          'spui-short-pixel-cloudflareAPIEmail',
          'spui-short-pixel-cloudflareAuthKey',
          'spui-short-pixel-front-bootstrap',
					'spui-short-pixel-api-retries',
					'spui-short-pixel-total-optimized',
					'spui-short-pixel-total-original',
					'spui-short-pixel-download-archive',
					'spui-short-pixel-converted-png2jpg',
          'spui-short-pixel-savedSpace',
          'spui-short-pixel-fileCount',
          'spui-short-pixel-files-under-5-percent',
        ];

				$toRemove = array_merge($bulkLegacyOptions, $removedStats, $removedOptions, $settingsRevamp);

				foreach($toRemove as $option)
				{
					 delete_option($option);
				}
    }

    public function __get($name)
    {
        if (array_key_exists($name, self::$_optionsMap)) {
            return $this->getOpt(self::$_optionsMap[$name]['key'], self::$_optionsMap[$name]['default']);
        }
        return null;
    }

    public function __set($name, $value) {
        if (array_key_exists($name, self::$_optionsMap)) {
            if($value !== null) {
                $this->setOpt(self::$_optionsMap[$name]['key'], $value);
            } else {
                delete_option(self::$_optionsMap[$name]['key']);
            }
        }
    }

		// Remove option. Only deletes with defined key!
		public function deleteOption($key)
		{
			  if(isset(self::$_optionsMap[$key]) && isset(self::$_optionsMap[$key]['key']))
				{
						$deleteKey = self::$_optionsMap[$key]['key'];
						delete_option($deleteKey);
				}
		}

    public static function getOpt($key, $default = null) {

				// This function required the internal Key. If this not given, but settings key, overwrite.
        if(isset(self::$_optionsMap[$key]['key'])) { //first try our name
						$default = self::$_optionsMap[$key]['default']; // first do default do to overwrite.
						$key = self::$_optionsMap[$key]['key'];
        }

        $opt = get_option($key, $default);
				return $opt;
    }

    public function setOpt($key, $val) {
        $autoload = true;
        $ret = update_option($key, $val, $autoload);

        //hack for the situation when the option would just not update....
        if($ret === false && !is_array($val) && $val != get_option($key)) {
            delete_option($key);
            $alloptions = wp_load_alloptions();
            if ( isset( $alloptions[$key] ) ) {
                wp_cache_delete( 'alloptions', 'options' );
            } else {
                wp_cache_delete( $key, 'options' );
            }
            delete_option($key);
            add_option($key, $val, '', $autoload);

            // still not? try the DB way...
            if($ret === false && $val != get_option($key)) {
                global $wpdb;
                $rows = $wpdb->get_results(
                    $wpdb->prepare(
                        "SELECT * FROM {$wpdb->options} WHERE option_name = %s",
                        $key
                    )
                );
                if(count($rows) === 0) {
                    $wpdb->insert(
                        $wpdb->options,
                        array(
                            'option_name'  => $key,
                            'option_value' => ( is_array( $val ) ? maybe_serialize( $val ) : $val ),
                            'autoload'     => $autoload ? 'yes' : 'no',
                        ),
                        array(
                            '%s',
                            is_numeric( $val ) ? '%d' : '%s',
                            '%s',
                        )
                    );
                } else { //update
                    $wpdb->update(
                        $wpdb->options,
                        array(
                            'option_value' => ( is_array( $val ) ? maybe_serialize( $val ) : $val ),
                        ),
                        array(
                            'option_name' => $key,
                        ),
                        array(
                            is_numeric( $val ) ? '%d' : '%s',
                        ),
                        array( '%s' )
                    );
                }

                if($val != get_option($key)) {
                    //tough luck, gonna use the bomb...
                    wp_cache_flush();
                    delete_option($key);
                    add_option($key, $val, '', $autoload);
                }
            }
        }
    }

} // class
