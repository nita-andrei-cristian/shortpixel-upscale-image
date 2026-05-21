<?php
namespace SPUI;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

use SPUI\ShortPixelLogger\ShortPixelLogger as Log;
use SPUI\Notices\NoticeController as Notices;
use SPUI\Controller\QueueController as QueueController;
use SPUI\Controller\QuotaController as QuotaController;
use SPUI\Controller\AjaxController as AjaxController;
use SPUI\Controller\AdminController as AdminController;
use SPUI\Controller\ImageEditorController as ImageEditorController;
use SPUI\Controller\ApiKeyController as ApiKeyController;
use SPUI\Controller\FileSystemController;
use SPUI\Controller\Optimizer\OptimizeAiController;
use SPUI\Controller\OtherMediaController as OtherMediaController;
use SPUI\NextGenController as NextGenController;

use SPUI\Controller\Queue\MediaLibraryQueue as MediaLibraryQueue;
use SPUI\Controller\Queue\CustomQueue as CustomQueue;

use SPUI\Helper\InstallHelper as InstallHelper;
use SPUI\Helper\UiHelper as UiHelper;

use SPUI\Model\AccessModel as AccessModel;
use SPUI\Model\SettingsModel as SettingsModel;

/** Plugin class
 * This class is meant for: WP Hooks, init of runtime and Controller Routing.
 */
class ShortPixelPlugin {

	private static $instance;
	protected static $modelsLoaded = array(); // don't require twice, limit amount of require looksups..

	protected $is_noheaders = false;

	protected $plugin_path;
	protected $plugin_url;

	protected $shortPixel; // shortpixel megaclass

	protected $admin_pages = array();  // admin page hooks.

	public function __construct() {
		// $this->initHooks();
		add_action( 'plugins_loaded', [$this, 'lowInit'], 5 ); // early as possible init.
		
	}

	/** LowInit after all Plugins are loaded. Core WP function can still be missing. This should mostly add hooks */
	public function lowInit() {

		$this->plugin_path = plugin_dir_path( SPUI_PLUGIN_FILE );
		$this->plugin_url  = plugin_dir_url( SPUI_PLUGIN_FILE );

		// phpcs:ignore WordPress.Security.NonceVerification.Recommended  -- This is not a form
		if ( isset( $_REQUEST['noheader'] ) ) {
			$this->is_noheaders = true;
		}

		/*
		Filter to prevent SPUI from starting. This can be used by third-parties to prevent init when needed for a particular situation.
		* Hook into plugins_loaded with priority lower than 5 */
		$init = apply_filters( 'spui/plugin/init', true );

		if (false === $init ) {
			return;
		}


		$front        = new Controller\FrontController(); // init front checkers
		$admin        = Controller\AdminController::getInstance();
		$adminNotices = Controller\AdminNoticesController::getInstance(); // Hook in the admin notices.

//		$this->initHooks();
		$this->ajaxHooks();

		if ( defined( 'WP_CLI' ) && \WP_CLI ) {
			WPCliController::getInstance();
		}

		add_action ('init', [$this, 'init']);
		add_action('init', [$this, 'initHooks']);
		add_action( 'admin_init', [ $this, 'admin_init' ] );
	}

	public function init()
	{
		Controller\CronController::getInstance();  // cron jobs - must be init to function!

		$access = AccessModel::getInstance();

		$isAdminUser = $access->userIsAllowed('is_admin_user');
	
		if ( $isAdminUser ) {
			// toolbar notifications

			// deactivate conflicting plugins if found
			add_action( 'admin_post_spui_deactivate_conflict_plugin', array( '\SPUI\Helper\InstallHelper', 'deactivateConflictingPlugin' ) );

			// only if the key is not yet valid or the user hasn't bought any credits.
			// @todo This should not be done here.
			$settings     = $this->settings();
			$stats        = $settings->currentStats;
			$totalCredits = isset( $stats['APICallsQuotaNumeric'] ) ? $stats['APICallsQuotaNumeric'] + $stats['APICallsQuotaOneTimeNumeric'] : 0;
			$keyControl = ApiKeyController::getInstance();


			if ( true || false === $keyControl->keyIsVerified() || $totalCredits < 4000 ) {
				// Feedback modal removed in SPUI slim build.
			}
		}
		
	}


	/** Mainline Admin Init. Tasks that can be loaded later should go here */
	public function admin_init() {
			// This runs activation thing. Should be -after- init
			$this->check_plugin_version();


			$notices             = Notices::getInstance(); // This hooks the ajax listener
			$quotaController = QuotaController::getInstance();
			$quotaController->getQuota();

			/* load_plugin_textdomain( 'shortpixel-upscale-image', false, plugin_basename( dirname( SPUI_PLUGIN_FILE ) ) . '/lang' ); */
	}

	/** Function to get plugin settings
     *
     * @return SettingsModel The settings model object.
     */
	public function settings() {
			return SettingsModel::getInstance();
	}

	/** Function to get all enviromental variables
     *
     * @return EnvironmentModel
     */
	public function env() {
		return Model\EnvironmentModel::getInstance();
	}

	/** Get the SPUI FileSystemController
	 * 
	 * @return FileSystemController 
	 */
	public function fileSystem() {
		return new Controller\FileSystemController();
	}

	/** Create instance. This should not be needed to call anywhere else than main plugin file
     * This should not be called *after* plugins_loaded action
     **/
	public static function getInstance() {
		if ( is_null( self::$instance ) ) {
			self::$instance = new ShortPixelPlugin();
		}
		return self::$instance;

	}

	/** Hooks for all WordPress related hooks
     * For now hooks in the lowInit, asap.
     */
	public function initHooks() {

		add_action( 'admin_menu', array( $this, 'admin_pages' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'admin_scripts' ) ); // admin scripts
		add_action( 'admin_enqueue_scripts', array( $this, 'admin_styles' ) ); // admin styles
		add_action( 'admin_enqueue_scripts', array( $this, 'load_admin_scripts' ), 90 ); // loader via route.
		add_action( 'enqueue_block_assets', array($this, 'load_admin_scripts'), 90);
		// defer notices a little to allow other hooks ( notable adminnotices )

		$queueController = new QueueController();
		add_action( 'shortpixel-thumbnails-regenerated', array( $queueController, 'thumbnailsChangedHookLegacy' ), 10, 4 );
		add_action( 'rta/image/thumbnails_regenerated', array( $queueController, 'thumbnailsChangedHook' ), 10, 2 );
		add_action( 'rta/image/thumbnails_removed', array( $queueController, 'thumbnailsChangedHook' ), 10, 2 );
		add_action('rta/image/scaled_image_regenerated', array($queueController, 'scaledImageChangedHook'), 10, 2);


		// Media Library - Actions to route screen
		add_action( 'load-upload.php', array( $this, 'route' ) );
		add_action( 'load-post.php', array( $this, 'route' ) );

		$admin = AdminController::getInstance();
		$imageEditor = ImageEditorController::getInstance();

		// Handle for EMR
		add_action( 'wp_handle_replace', array( $admin, 'handleReplaceHook' ) );

		// Action / hook for who wants to use CRON. Please refer to manual / support to prevent loss of credits.
		add_action( 'spui/hook/processqueue', array( $admin, 'processQueueHook' ) );
		add_action( 'spui/hook/scancustomfolders', array($admin, 'scanCustomFoldersHook'));

		// Action for media library gallery view
		//add_filter('attachment_fields_to_edit', array($admin, 'editAttachmentScreen'), 10, 2);
		add_action('print_media_templates', array($admin, 'printComparer'));

		// Placeholder function for heic and such, return placeholder URL in image to help w/ database replacements after conversion.
		add_filter('wp_get_attachment_url', array($admin, 'checkPlaceHolder'), 10, 2);

		add_filter('rest_post_dispatch', [$admin, 'checkRestMedia'],10, 3);

		/** When automagically process images when uploaded is on */
		if ( $this->env()->is_autoprocess ) {
			// compat filter to shortcircuit this in cases.  (see external - visualcomposer)
			if ( apply_filters( 'spui/init/automedialibrary', true ) ) {

      			add_action( 'shortpixel-thumbnails-before-regenerate', array( $admin, 'preventImageHook' ), 10, 1 );

				add_action( 'enable-media-replace-upload-done', array( $admin, 'handleReplaceEnqueue' ), 10, 3 );

				add_filter( 'wp_generate_attachment_metadata', array( $admin, 'handleImageUploadHook' ), 5, 2 );
				add_action('add_attachment', array($admin, 'addAttachmentHook'));

				// @integration MediaPress
				add_filter( 'mpp_generate_metadata', array( $admin, 'handleImageUploadHook' ), 10, 2 );
			}
		}

		$optimizeAiController = OptimizeAiController::getInstance(); 
		if (true === $optimizeAiController->isAutoAiEnabled())
		{

			// Run one hit earlier than optimization, to do this action first if needed.
			add_filter( 'wp_generate_attachment_metadata', array( $admin, 'handleAiImageUploadHook' ), 4, 2 );
			add_filter( 'mpp_generate_metadata', array( $admin, 'handleAiImageUploadHook' ), 9, 2 );
		}


		$this->env()->setDefaultViewModeList();// set default mode as list. only @ first run

		add_filter( 'plugin_action_links_' . plugin_basename( SPUI_PLUGIN_FILE ), array( $admin, 'generatePluginLinks' ) );// for plugin settings page

		// for cleaning up the WebP images when an attachment is deleted . Loading this early because it's possible other plugins delete files in the uploads, but we need those to remove backups.
		add_action( 'delete_attachment', array( $admin, 'onDeleteAttachment' ), 5 );
		add_action( 'mime_types', array( $admin, 'addMimes' ) );

		// integration with WP/LR Sync plugin
		//add_action( 'wplr_update_media', array( AjaxController::getInstance(), 'onWpLrUpdateMedia' ), 10, 2 );
		add_action( 'wplr_sync_media', array( AjaxController::getInstance(), 'onWpLrSyncMedia' ), 10, 2 );

		add_action( 'admin_bar_menu', array( $admin, 'toolbar_spui_processing' ), 999 );

		// Image Editor Actions
		add_filter('load_image_to_edit_path', array($imageEditor, 'getImageForEditor'), 10, 3);
		add_filter('wp_save_image_editor_file', array($imageEditor, 'saveImageFile'), 10, 5);  // hook when saving
	//	add_action('update_post_meta', array($imageEditor, 'checkUpdateMeta'), 10, 4 );


		if (is_admin())
		{
			  add_filter('pre_get_posts', array($admin, 'filter_listener'));
		}

		if ($this->env()->is_multisite)
		{
			 add_action('network_admin_menu', [$this, 'admin_network_pages']) ;
		}

	}

	protected function ajaxHooks() {

		// Ajax hooks. Should always be prepended with ajax_ and *must* check on nonce in function
		add_action( 'wp_ajax_spui_image_processing', array( AjaxController::getInstance(), 'ajax_processQueue' ) );

		// Custom Media

		//add_action( 'wp_ajax_spui_get_backup_size', array( AjaxController::getInstance(), 'ajax_getBackupFolderSize' ) );

		add_action( 'wp_ajax_spui_propose_upgrade', array( AjaxController::getInstance(), 'ajax_proposeQuotaUpgrade' ) );
		add_action( 'wp_ajax_spui_check_quota', array( AjaxController::getInstance(), 'ajax_checkquota' ) );


		add_action( 'wp_ajax_spui_ajaxRequest', array( AjaxController::getInstance(), 'ajaxRequest' ) );
		add_action( 'wp_ajax_spui_settingsRequest', array( AjaxController::getInstance(), 'settingsRequest'));

	}

	/** Hook in our admin pages */
	public function admin_pages() {
		$admin_pages = array();
		// settings page
		$admin_pages[] = add_options_page( __( 'ShortPixel Upscaler Settings', 'shortpixel-upscale-image' ), 'ShortPixel Upscaler', 'manage_options', 'wp-shortpixel-upscale-settings', array( $this, 'route' ) );

		$otherMediaController = OtherMediaController::getInstance();
		if ( $otherMediaController->showMenuItem() ) {
			/*translators: title and menu name for the Other media page*/
			$admin_pages[] = add_media_page( __( 'Custom Media Upscaled by ShortPixel', 'shortpixel-upscale-image' ), __( 'Custom Media', 'shortpixel-upscale-image' ), 'edit_others_posts', 'wp-shortpixel-upscale-custom', array( $this, 'route' ) );
		}
		/*translators: title and menu name for the Bulk Processing page*/
		$admin_pages[] = add_media_page( __( 'ShortPixel Bulk Upscale', 'shortpixel-upscale-image' ), __( 'Bulk Upscale', 'shortpixel-upscale-image' ), 'edit_others_posts', 'wp-shortpixel-upscale-bulk', array( $this, 'route' ) );

		$this->admin_pages = $admin_pages;
	}

	public function admin_network_pages()
	{
	//	  	add_menu_page(__('Shortpixel MU', 'shortpixel-upscale-image'), __('Shortpixel Upscaler', 'shortpixel-upscale-image'), 'manage_sites', 'spui-network-settings', [$this, 'route'], $this->plugin_url('res/img/shortpixel.png') );
	}

	/** All scripts should be registed, not enqueued here (unless global wp-admin is needed )
     *
     * Not all those registered must be enqueued however.
     */
	public function admin_scripts( $hook_suffix ) {

		$settings       = \wpSPUI()->settings();
		$env = \wpSPUI()->env();
		$ajaxController = AjaxController::getInstance();

		$secretKey = $ajaxController->getProcessorKey();

		$keyControl = \SPUI\Controller\ApiKeyController::getInstance();
		$apikey     = $keyControl->getKeyForDisplay();

		$is_bulk_page = \wpSPUI()->env()->is_bulk_page;

		$queueController = new QueueController(['is_bulk' =>  $is_bulk_page ]);
		$quotaController = QuotaController::getInstance();

		$OptimizeAiController = OptimizeAiController::getInstance(); 

		wp_register_script('spui-folderbrowser', plugins_url('/res/js/shortpixel-folderbrowser.js', SPUI_PLUGIN_FILE), array(), SPUI_IMAGE_OPTIMISER_VERSION, true );

	 wp_localize_script('spui-folderbrowser', 'spui_folderbrowser', array(
		 		'strings' => array(
						'loading' => __('Loading', 'shortpixel-upscale-image'),
						'empty_result' => __('No Directories found that can be added to Custom Folders', 'shortpixel-upscale-image'),
				),
				'icons' => array(
						'folder_closed' => plugins_url('res/img/filebrowser/folder-closed.svg', SPUI_PLUGIN_FILE),
						'folder_open' => plugins_url('res/img/filebrowser/folder-closed.svg', SPUI_PLUGIN_FILE),
				),
	 ));

		wp_register_script( 'spui-knob', plugins_url( '/res/js/jquery.knob.min.js', SPUI_PLUGIN_FILE ), array(), SPUI_IMAGE_OPTIMISER_VERSION, true );

		wp_register_script( 'spui-debug', plugins_url( '/res/js/debug.js', SPUI_PLUGIN_FILE ), array( 'jquery', 'jquery-ui-draggable' ), SPUI_IMAGE_OPTIMISER_VERSION, true );

		wp_register_script( 'spui-tooltip', plugins_url( '/res/js/shortpixel-tooltip.js', SPUI_PLUGIN_FILE ), array( 'jquery' ), SPUI_IMAGE_OPTIMISER_VERSION, true );

		$tooltip_localize = array(
			'processing' => __('Processing... ','shortpixel-upscale-image'),
			'pause' =>  __('Click to pause', 'shortpixel-upscale-image'),
			'resume' => __('Click to resume', 'shortpixel-upscale-image'),
			'item' => __('item in queue', 'shortpixel-upscale-image'),
			'items' => __('items in queue', 'shortpixel-upscale-image'),
		);

		wp_localize_script( 'spui-tooltip', 'spui_tooltipStrings', $tooltip_localize);

		wp_register_script( 'spui-settings', plugins_url( 'res/js/shortpixel-settings.js', SPUI_PLUGIN_FILE ), array('spui-shiftselect', 'spui-inline-help'), SPUI_IMAGE_OPTIMISER_VERSION, true );

		wp_register_script('spui-shiftselect', plugins_url('res/js/shift-select.js', SPUI_PLUGIN_FILE), array(), SPUI_IMAGE_OPTIMISER_VERSION, true);

		wp_localize_script('spui-settings', 'settings_strings', UiHelper::getSettingsStrings(false));
		wp_localize_script(
			'spui-settings',
			'SPUISettingsData',
			array(
				'ajaxUrl' => admin_url( 'admin-ajax.php' ),
				'nonceSettingsRequest' => wp_create_nonce( 'settings_request' ),
			)
		);

		wp_register_script( 'spui-onboarding', plugins_url( 'res/js/shortpixel-onboarding.js', SPUI_PLUGIN_FILE ), array('spui-settings'), SPUI_IMAGE_OPTIMISER_VERSION, true );
		wp_localize_script(
			'spui-onboarding',
			'SPUIOnboardingData',
			array(
				'ajaxUrl' => admin_url( 'admin-ajax.php' ),
				'nonceSettingsRequest' => wp_create_nonce( 'settings_request' ),
			)
		);

		wp_register_script('spui-media', plugins_url('res/js/shortpixel-media.js',  SPUI_PLUGIN_FILE), array('jquery'), SPUI_IMAGE_OPTIMISER_VERSION, true);

		wp_register_script('spui-inline-help', plugins_url('res/js/shortpixel-inline-help.js',  SPUI_PLUGIN_FILE), [], SPUI_IMAGE_OPTIMISER_VERSION, true);

		// This filter is from ListMediaViewController for the media library grid display, executive script in shortpixel-media.js.

		$filters = array('optimized' => array(
					'all' => __('Any Upscale State', 'shortpixel-upscale-image'),
					'optimized' => __('Upscaled', 'shortpixel-upscale-image'),
					'unoptimized' => __('Not Upscaled', 'shortpixel-upscale-image'),
					'prevented' => __('Upscaling Error', 'shortpixer-image-optimiser'),
		));

		$editor_localize = ImageEditorController::localizeScript();
		$editor_localize['mediafilters'] = $filters;
		wp_localize_script('spui-media', 'spui_media', $editor_localize);

		wp_register_script( 'spui-processor', plugins_url( '/res/js/shortpixel-processor.js', SPUI_PLUGIN_FILE ), array( 'jquery', 'spui-tooltip' ), SPUI_IMAGE_OPTIMISER_VERSION, true );

		 // How often JS processor asks for next tick on server. Low for fastestness and high loads, high number for surviving servers.
		$interval = apply_filters( 'spui/processor/interval', 3000 );

		// If the queue is empty how often to check if something new appeared from somewhere. Excluding the manual items added by current processor user.
		$deferInterval = apply_filters( 'spui/process/deferInterval', 60000 );

		wp_localize_script(
            'spui-processor',
            'SPUIProcessorData',
            array(
				'bulkSecret'        => $secretKey,
				'isBulkPage'        => (bool) $is_bulk_page,
				'workerURL'         => plugins_url( 'res/js/shortpixel-worker.js', SPUI_PLUGIN_FILE ),
				'nonce_process'     => wp_create_nonce( 'processing' ),
				'nonce_exit'        => wp_create_nonce( 'exit_process' ),
				'nonce_ajaxrequest' => wp_create_nonce( 'ajax_request' ),
				'nonce_settingsrequest' => wp_create_nonce('settings_request'),
				'startData'         => ( \wpSPUI()->env()->is_screen_to_use ) ? $queueController->getStartupData() : false,
				'interval'          => $interval,
				'deferInterval'     => $deferInterval,
				'debugIsActive' 		=> (\wpSPUI()->env()->is_debug) ? 'true' : 'false',
				'autoMediaLibrary'  => ($settings->autoMediaLibrary) ? 'true' : 'false',
				'disable_processor' => apply_filters('spui/processorjs/disable', false),
            )
        );

		//https://github.com/thedatepicker/thedatepicker
		wp_register_script('spui-datepicker', plugins_url('res/js/the-datepicker.min.js', SPUI_PLUGIN_FILE),  ['wp-components', 'wp-i18n', 'wp-element', 'wp-hooks'], SPUI_IMAGE_OPTIMISER_VERSION, true);
		

		/*** SCREENS */
		wp_register_script('spui-screen-base', plugins_url( '/res/js/screens/screen-base.js', SPUI_PLUGIN_FILE ), array( 'jquery', 'spui-processor' ), SPUI_IMAGE_OPTIMISER_VERSION, true );

		wp_register_script('spui-screen-item-base', plugins_url( '/res/js/screens/screen-item-base.js', SPUI_PLUGIN_FILE ), array( 'jquery', 'spui-processor', 'spui-screen-base'), SPUI_IMAGE_OPTIMISER_VERSION, true );

		wp_register_script( 'spui-screen-media', plugins_url( '/res/js/screens/screen-media.js', SPUI_PLUGIN_FILE ), array( 'jquery', 'spui-processor', 'spui-screen-base', 'spui-screen-item-base' ), SPUI_IMAGE_OPTIMISER_VERSION, true );

		wp_register_script( 'spui-screen-custom', plugins_url( '/res/js/screens/screen-custom.js', SPUI_PLUGIN_FILE ), array( 'jquery', 'spui-processor', 'spui-screen-base', 'spui-screen-item-base' ), SPUI_IMAGE_OPTIMISER_VERSION, true );

		wp_register_script( 'spui-screen-nolist', plugins_url( '/res/js/screens/screen-nolist.js', SPUI_PLUGIN_FILE ), array( 'jquery', 'spui-processor', 'spui-screen-base' ), SPUI_IMAGE_OPTIMISER_VERSION, true );

	  $screen_localize = array(  // Item Base
			'startAction' => __('Processing... ','shortpixel-upscale-image'),
			'startActionAI' => __('Generating image SEO data', 'shortpixel-upscale-image'),
			'fatalError' => __('ShortPixel encountered a fatal error when upscaling images. Please check the issue below. If this is caused by a bug please contact our support', 'shortpixel-upscale-image'),
			'fatalErrorStop' => __('ShortPixel has encounted multiple errors and has now stopped processing', 'shortpixel-upscale-image'),
			'fatalErrorStopText' => __('No items are being processed. To try again after solving the issues, please reload the page ', 'shortpixel-upscale-image'),
			'fatalError500' => __('A fatal error HTTP 500 has occurred. On the bulk screen, this may be caused by the script running out of memory. Check your error log, increase memory or disable heavy plugins.'),

		);
	

	 $screen_localize_custom = array( // Custom Screen
			'stopActionMessage' => __('Folder scan has stopped', 'shortpixel-upscale-image'),
		);

	 $screen_localize_media = [
			'hide_ai' => ! $OptimizeAiController->isAiEnabled(),  // turn around negative setting
			'hide_spui_in_popups' => apply_filters('spui/js/media/hide_in_popups', false),
			'modalcss' => plugins_url('res/css/shortpixel-media-modal.css', SPUI_PLUGIN_FILE),
			'remove_background_title' => __('AI Background Removal', 'shortpixel-upscale-image'),
			'scale_title' => __('AI Image Upscale', 'shortpixel-upscale-image'),
			'optimize_max_width' => 1200, // Scale X and max width pin Pixels.
			'popup_load_preview' => true, // Upon opening, load Preview or not.
			'too_big_for_scale_title'  => __('Image too big for scaling', 'shortpixel-upscale-image'),
			'wp_screen_id' => $env->screen_id,
	 ];

		wp_localize_script('spui-screen-media', 'spui_mediascreen_settings', $screen_localize_media);

		wp_localize_script( 'spui-screen-base', 'spui_screenStrings', array_merge($screen_localize, $screen_localize_custom));

		wp_register_script( 'spui-screen-bulk', plugins_url( '/res/js/screens/screen-bulk.js', SPUI_PLUGIN_FILE ), array( 'jquery', 'spui-processor', 'spui-screen-base'), SPUI_IMAGE_OPTIMISER_VERSION, true );

		// phpcs:ignore WordPress.Security.NonceVerification.Recommended  -- This is not a form
		$panel = isset( $_GET['panel'] ) ? sanitize_text_field( wp_unslash($_GET['panel']) ) : false;

		$bulkLocalize = [
			'endBulk'   => __( 'This will stop the bulk processing and take you back to the start. Are you sure you want to do this?', 'shortpixel-upscale-image' ),
			'reloadURL' => admin_url( 'upload.php?page=wp-shortpixel-upscale-bulk'),
		];
		if ( $panel ) {
			$bulkLocalize['panel'] = $panel;
        }

		// screen translations. Can all be loaded on the same var, since only one screen can be active.
		wp_localize_script( 'spui-screen-bulk', 'shortPixelScreen', $bulkLocalize );

		wp_register_script( 'spui', plugins_url( '/res/js/shortpixel.js', SPUI_PLUGIN_FILE ), array( 'jquery', 'spui-knob' ), SPUI_IMAGE_OPTIMISER_VERSION, true );

		// Using an Array within another Array to protect the primitive values from being cast to strings
		$SPUIConstants = array(
			array(
				'WP_PLUGIN_URL'     => plugins_url( '', SPUI_PLUGIN_FILE ),
				'WP_ADMIN_URL'      => admin_url(),
				'API_IS_ACTIVE'     => $keyControl->keyIsVerified(),
				'AJAX_URL'          => admin_url( 'admin-ajax.php' ),
				'BULK_SECRET'       => $secretKey,
				'nonce_ajaxrequest' => wp_create_nonce( 'ajax_request' ),
				'HAS_QUOTA'         => ( $quotaController->hasQuota() ) ? 1 : 0,

			),
		);

		if ( Log::isManualDebug() ) {
			Log::addInfo( 'Ajax Manual Debug Mode' );
			$logLevel                           = Log::getLogLevel();
			$SPUIConstants[0]['AJAX_URL'] = admin_url( 'admin-ajax.php?SPUI_DEBUG=' . $logLevel );
		}

		$jsTranslation = array(
			'optimizeWithSP'              => __( 'ShortPixel Upscaler', 'shortpixel-upscale-image' ),
			'optimize'              => __( 'Upscale', 'shortpixel-upscale-image' ),
			'redoLossy'                   => __( 'Re-upscale Lossy', 'shortpixel-upscale-image' ),
			'redoGlossy'                  => __( 'Re-upscale Glossy', 'shortpixel-upscale-image' ),
			'redoLossless'                => __( 'Re-upscale Lossless', 'shortpixel-upscale-image' ),
			'redoSmartcrop'               => __( 'Re-upscale with SmartCrop', 'shortpixel-upscale-image'),
			'redoSmartcropless'           => __( 'Re-upscale without SmartCrop', 'shortpixel-upscale-image'),
			'restoreOriginal'             => __( 'Restore Originals', 'shortpixel-upscale-image' ),
			'generateAI' 				  => __( 'Generate image SEO data', 'shortpixel-upscale-image'),
			'markCompleted' 			  => __('Mark as completed' ,'shortpixel-upscale-image'),
			'areYouSureStopUpscaling'    => __( 'Are you sure you want to stop upscaling the folder {0}?', 'shortpixel-upscale-image' ),
			'pleaseDoNotSetLesserSize'    => __( "Please do not set a {0} less than the {1} of the largest thumbnail which is {2}, to be able to still regenerate all your thumbnails in case you'll ever need this.", 'shortpixel-upscale-image' ),
			'pleaseDoNotSetLesser1024'    => __( "Please do not set a {0} less than 1024, to be able to still regenerate all your thumbnails in case you'll ever need this.", 'shortpixel-upscale-image' ),
			'confirmBulkRestore'          => __( 'Are you sure you want to restore from backup all the images in your Media Library upscaled with ShortPixel?', 'shortpixel-upscale-image' ),
			'confirmBulkCleanup'          => __( "Are you sure you want to cleanup the ShortPixel metadata info for the images in your Media Library upscaled with ShortPixel? This will make ShortPixel 'forget' that it upscaled them and will upscale them again if you re-run the Bulk Upscaling process.", 'shortpixel-upscale-image' ),
			'alertDeliverWebPAltered'     => __( "Warning: Using this method alters the structure of the rendered HTML code (IMG tags get included in PICTURE tags), which, in some rare \ncases, can lead to CSS/JS inconsistencies.\n\nPlease test this functionality thoroughly after activating!\n\nIf you notice any issue, just deactivate it and the HTML will will revert to the previous state.", 'shortpixel-upscale-image' ),
			'alertDeliverWebPUnaltered'   => __( 'This option will serve both WebP and the original image using the same URL, based on the web browser capabilities, please make sure you\'re serving the images from your server and not using a CDN which caches the images.', 'shortpixel-upscale-image' ),
			'originalImage'               => __( 'Original image', 'shortpixel-upscale-image' ),
			'optimizedImage'              => __( 'Upscaled image', 'shortpixel-upscale-image' ),
			'loading'                     => __( 'Loading...', 'shortpixel-upscale-image' ),

		);

		wp_localize_script( 'spui', '_spTr', $jsTranslation );
		wp_localize_script( 'spui', 'SPUIConstants', $SPUIConstants );

	}

	public function admin_styles() {

		wp_register_style( 'spui-folderbrowser', plugins_url( '/res/css/shortpixel-folderbrowser.css', SPUI_PLUGIN_FILE ),[], SPUI_IMAGE_OPTIMISER_VERSION );

		//wp_register_style( 'shortpixel', plugins_url( '/res/css/short-pixel.css', SPUI_PLUGIN_FILE ), array(), SPUI_IMAGE_OPTIMISER_VERSION );

		// notices. additional styles for SPUI.
		wp_register_style( 'spui-notices', plugins_url( '/res/css/shortpixel-notices.css', SPUI_PLUGIN_FILE ), array( 'spui-admin' ), SPUI_IMAGE_OPTIMISER_VERSION );

		wp_register_style('spui-notices-module', plugins_url('/build/shortpixel/notices/src/css/notices.css', SPUI_PLUGIN_FILE), array(), SPUI_IMAGE_OPTIMISER_VERSION);

		// other media screen
		wp_register_style( 'spui-othermedia', plugins_url( '/res/css/shortpixel-othermedia.css', SPUI_PLUGIN_FILE ), array(), SPUI_IMAGE_OPTIMISER_VERSION );

		// load everywhere, because we are inconsistent.
		wp_register_style( 'spui-toolbar', plugins_url( '/res/css/shortpixel-toolbar.css', SPUI_PLUGIN_FILE ), array( 'dashicons' ), SPUI_IMAGE_OPTIMISER_VERSION );

		// @todo Might need to be removed later on
		wp_register_style( 'spui-admin', plugins_url( '/res/css/shortpixel-admin.css', SPUI_PLUGIN_FILE ), array(), SPUI_IMAGE_OPTIMISER_VERSION );

		wp_register_style( 'spui-bulk', plugins_url( '/res/css/shortpixel-bulk.css', SPUI_PLUGIN_FILE ), array(), SPUI_IMAGE_OPTIMISER_VERSION );

		wp_register_style( 'spui-nextgen', plugins_url( '/res/css/shortpixel-nextgen.css', SPUI_PLUGIN_FILE ), array(), SPUI_IMAGE_OPTIMISER_VERSION );

		wp_register_style( 'spui-settings', plugins_url( '/res/css/shortpixel-settings.css', SPUI_PLUGIN_FILE ), array(), SPUI_IMAGE_OPTIMISER_VERSION );

		wp_register_style('spui-datepicker', plugins_url('res/css/the-datepicker.css', SPUI_PLUGIN_FILE), [], SPUI_IMAGE_OPTIMISER_VERSION );
	}


	/** Load Style via Route, on demand */
	public function load_style( $name ) {
		if ( $this->is_noheaders ) {  // fail silently, if this is a no-headers request.
			return;
		}

		if ( wp_style_is( $name, 'registered' ) ) {
			wp_enqueue_style( $name );
		} else {
			Log::addWarn( "Style $name was asked for, but not registered", $_SERVER['REQUEST_URI'] );
		}
	}

	/** Load Style via Route, on demand */
	public function load_script( $script ) {
		if ( $this->is_noheaders ) {  // fail silently, if this is a no-headers request.
			return;
		}

		if ( ! is_array( $script ) ) {
			$script = array( $script );
		}

		foreach ( $script as $index => $name ) {
			if ( wp_script_is( $name, 'registered' ) ) {
				wp_enqueue_script( $name );
			} else {
				Log::addWarn( "Script $name was asked for, but not registered", $_SERVER['REQUEST_URI']  );
			}
		}
	}

	/** This is separated from route to load in head, preventing unstyled content all the time */
	 public function load_admin_scripts( $hook_suffix ) {
		global $plugin_page;
		$screen_id = $this->env()->screen_id;

		$load_processor = array( 'spui', 'spui-processor' );  // a whole suit needed for processing, not more. Always needs a screen as well!
		$load_bulk      = array();  // the whole suit needed for bulking.
		if ( \wpSPUI()->env()->is_screen_to_use ) {
			$this->load_script( $load_processor );
			$this->load_style( 'spui-toolbar' );
			$this->load_style('spui-notices');
			$this->load_style('spui-notices-module');
		}

		if ( $plugin_page == 'wp-shortpixel-upscale-settings' || $plugin_page == 'spui-network-settings' ) {

			$this->load_script( 'spui-screen-nolist' ); // screen
			$this->load_script( 'spui-settings' );
			$this->load_script( 'spui-onboarding' );

			$this->load_style( 'spui-admin' );

			$this->load_style( 'spui-settings' );

		} elseif ( $plugin_page == 'wp-shortpixel-upscale-bulk' ) {
			$this->load_script( 'spui-screen-bulk' );
			$this->load_script('spui-datepicker');

			$this->load_style('spui-datepicker');
			$this->load_style( 'spui-admin' );
			$this->load_style( 'spui-bulk' );
		} elseif ( $screen_id == 'upload' || $screen_id == 'attachment' ) {

			$this->load_script( 'spui-screen-media' ); // screen
			$this->load_script( 'spui-media' );

			$this->load_style( 'spui-admin' );
			$this->load_style( 'spui-notices-module');
		//	$this->load_style( 'spui' );

			if ( $this->env()->is_debug ) {
				$this->load_script( 'spui-debug' );
			}

		} elseif ( $plugin_page == 'wp-shortpixel-upscale-custom' ) { // custom media
		//	$this->load_style( 'spui' );

			$this->load_script( 'spui-folderbrowser' );

			$this->load_style( 'spui-admin' );
			$this->load_style( 'spui-folderbrowser' );
			$this->load_style( 'spui-othermedia' );
			$this->load_script( 'spui-screen-custom' ); // screen

		} elseif ( NextGenController::getInstance()->isNextGenScreen() ) {

			$this->load_script( 'spui-screen-custom' ); // screen
			$this->load_style( 'spui-admin' );

		//	$this->load_style( 'spui' );
			$this->load_style( 'spui-nextgen' );
		}
		elseif (true === $this->env()->is_gutenberg_editor || true === $this->env()->is_classic_editor)
		{
			$this->load_script( $load_processor );
			$this->load_script( 'spui-screen-media' ); // screen
			$this->load_script( 'spui-media' );

			$this->load_style( 'spui-admin' );
		}
		elseif (true === \wpSPUI()->env()->is_screen_to_use  )
		{
			// If our screen, but we don't have a specific handler for it, do the no-list screen.
			$this->load_script( 'spui-screen-nolist' ); // screen
		}

	}

	/** Route, based on the page slug
     *
     * Principially all page controller should be routed from here.
     */
	public function route() {
		global $plugin_page;

		$default_action = 'load'; // generic action on controller.
		// phpcs:ignore WordPress.Security.NonceVerification.Recommended  -- This is not a form
		$action         = isset( $_REQUEST['sp-action'] ) ? sanitize_text_field( wp_unslash($_REQUEST['sp-action']) ) : $default_action;
		// phpcs:ignore WordPress.Security.NonceVerification.Recommended  -- This is not a form
		$template_part  = isset( $_GET['part'] ) ? sanitize_text_field( wp_unslash($_GET['part']) ) : false;

		$controller = false;

		$url       = menu_page_url( $plugin_page, false );
		$screen_id = \wpSPUI()->env()->screen_id;

        switch ( $plugin_page ) {
			case 'wp-shortpixel-upscale-settings': // settings
						$controller = 'SPUI\Controller\View\SettingsViewController';
						wp_enqueue_media();
			break;
			case 'spui-network-settings':
						$controller = 'SPUI\Controller\View\MultiSiteViewController';
			break;
          case 'wp-shortpixel-upscale-custom': // other media
						if ('folders'  === $template_part )
						{
							$controller = 'SPUI\Controller\View\OtherMediaFolderViewController';
						}
						elseif('scan' === $template_part)
						{
							$controller = 'SPUI\Controller\View\OtherMediaScanViewController';
						}
						else {
							$controller = 'SPUI\Controller\View\OtherMediaViewController';
						}

        	break;
			case 'wp-shortpixel-upscale-bulk':
						$controller = '\SPUI\Controller\View\BulkViewController';
           break;
           case null:
            default:
                switch ( $screen_id ) {
					case 'upload':
                  $controller = '\SPUI\Controller\View\ListMediaViewController';
                        break;
					case 'attachment': // edit-media
                   $controller = '\SPUI\Controller\View\EditMediaViewController';
                     break;
                }
                break;

		}
		if ( $controller !== false ) {
			$c = $controller::getInstance();
			$c->setControllerURL( $url );
			if ( method_exists( $c, $action ) ) {
				$c->$action();
			} else {
				Log::addWarn( "Attempted Action $action on $controller does not exist!" );
				$c->$default_action();
			}
		}
	}


	// Get the plugin URL, based on real URL.
	public function plugin_url( $urlpath = '' ) {
		$url = trailingslashit( $this->plugin_url );
		if ( strlen( $urlpath ) > 0 ) {
			$url .= $urlpath;
		}
		return $url;
	}

	// Get the plugin path.
	public function plugin_path( $path = '' ) {
		$plugin_path = trailingslashit( $this->plugin_path );
		if ( strlen( $path ) > 0 ) {
			$plugin_path .= $path;
		}

		return $plugin_path;
	}

	/** Returns defined admin page hooks. Internal use - check states via environmentmodel
     *
     * @returns Array
     */
	public function get_admin_pages() {
		return $this->admin_pages;
	}

	protected function check_plugin_version() {
      $version     = SPUI_IMAGE_OPTIMISER_VERSION;
			$db_version = $this->settings()->currentVersion;

		if ( $version !== $db_version ) {
			InstallHelper::activatePlugin();
			$this->settings()->currentVersion = $version;

		}
	}




} // class plugin
