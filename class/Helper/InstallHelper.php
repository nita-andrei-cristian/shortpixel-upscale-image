<?php

namespace SPUI\Helper;

if (! defined('ABSPATH')) {
	exit; // Exit if accessed directly.
}

use SPUI\ShortPixelLogger\ShortPixelLogger as Log;
use SPUI\Controller\QueueController as QueueController;
use SPUI\Controller\CronController as CronController;
use SPUI\Controller\BulkController as BulkController;
use SPUI\Controller\FileSystemController as FileSystemController;
use SPUI\Controller\AdminNoticesController as AdminNoticesController;
use SPUI\Controller\StatsController as StatsController;
use SPUI\Controller\ApiKeyController as ApiKeyController;
use SPUI\Notices\NoticeController as Notices;
use SPUI\Helper\UtilHelper as UtilHelper;


class InstallHelper
{

	public static function activatePlugin()
	{
		self::deactivatePlugin();

		$env = wpSPUI()->env();

		if (\SPUI_Settings::getOpt('deliverWebp') == 3 && ! $env->is_nginx) {
			UtilHelper::alterHtaccess(true, true); //add the htaccess lines. Both are true because even if one option is now off in the past both fileformats could have been generated.
		}

		self::checkTables();

		AdminNoticesController::resetOldNotices();
		\SPUI_Settings::onActivate();

		$queueController = new QueueController();
		$q = $queueController->getQueue('media');
		$q->getShortQ()->install(); // create table.

		$settings = \wpSPUI()->settings();
		$settings->currentVersion = SPUI_IMAGE_OPTIMISER_VERSION;

		wp_cache_flush();
	}

	public static function deactivatePlugin()
	{
		$settings = new \SPUI_Settings(); // \wpSPUI()->settings();
		$settings::onDeactivate();

		$env = wpSPUI()->env();

		if (! $env->is_nginx) {
			UtilHelper::alterHtaccess(false, false);
		}

		// save remove.
		$fs = new FileSystemController();
		$log = $fs->getFile(SPUI_BACKUP_FOLDER . "/shortpixel_log");

		if ($log->exists())
			$log->delete();

			global $wpdb;
			$wpdb->query( "delete from {$wpdb->options} where option_name like '%_transient_shortpixel%' or option_name like '%_transient_timeout_shortpixel%'" ); // remove transients.

		// saved in settings object, reset all stats.
		StatsController::getInstance()->reset();
		CronController::getInstance()->onDeactivate();
	}

	public static function uninstallPlugin()
	{
		QueueController::uninstallPlugin();
		ApiKeyController::uninstallPlugin();

		delete_transient('bulk-secret');
		delete_transient('othermedia_refresh_folder_delay');
		delete_transient('avif_server_check');
		delete_transient('quotaData');
	}

	// Removes everything  of SPIO 5.x .  Not recommended.
	public static function hardUninstall()
	{
		$env = \wpSPUI()->env();
		$settings = new \SPUI_Settings();

		$nonce = (isset($_POST['tools-nonce'])) ? sanitize_key($_POST['tools-nonce']) : null;
		if (! wp_verify_nonce($nonce, 'remove-all')) {
			wp_nonce_ays('');
		}

		self::deactivatePlugin(); // deactivate
		self::uninstallPlugin(); // uninstall

		// Bulk Log
		BulkController::uninstallPlugin();

		$settings::resetOptions();

		// new settings
		delete_option('spui_settings');

		if (! $env->is_nginx) {
			insert_with_markers(get_home_path() . '.htaccess', 'ShortPixelWebp', '');
		}

		self::removeTables();

		// Remove Backups
		$dir = \wpSPUI()->filesystem()->getDirectory(SPUI_BACKUP_FOLDER);
		$dir->recursiveDelete();

		$plugin = basename(SPUI_PLUGIN_DIR) . '/' . basename(SPUI_PLUGIN_FILE);
		deactivate_plugins($plugin);
	}


	public static function deactivateConflictingPlugin()
	{
		if (! isset($_GET['_wpnonce']) || ! wp_verify_nonce(sanitize_key($_GET['_wpnonce']), 'sp_deactivate_plugin_nonce')) {
			wp_nonce_ays('Nononce');
		}

		$referrer_url = wp_get_referer();
		$url = wp_get_referer();
		$plugin = (isset($_GET['plugin'])) ? sanitize_text_field(wp_unslash($_GET['plugin'])) : null; // our target.

		if (! is_null($plugin))
			deactivate_plugins($plugin);

		wp_safe_redirect($url);
		die();
	}

	/**
	 * Check if TableName exists
	 * @param $tableName The Name of the Table without Prefix.
	 */
	public static function checkTableExists($tableName)
	{
		global $wpdb;
		$tableName = $wpdb->prefix . $tableName;
			$result = intval(
				$wpdb->query(
					$wpdb->prepare(
						"SHOW TABLES LIKE %s",
						$tableName
					)
				)
			);

		if ($result == 0)
			return false;
		else {
			return true;
		}
	}


	public static function checkTables()
	{
		global $wpdb;
		require_once(ABSPATH . 'wp-admin/includes/upgrade.php');

		dbDelta(self::getFolderTableSQL());
		dbDelta(self::getMetaTableSQL());
		dbDelta(self::getPostMetaSQL());
		dbDelta(self::getAIPostSQL());

		self::checkIndexes();
	}

	private static function checkIndexes()
	{
		global $wpdb;

		$definitions = array(
			'shortpixel_meta' => array(
				'path' => 'path'
			),
			'shortpixel_folders' => array(
				'path' => 'path'
			),
			'shortpixel_postmeta' => array(
				'attach_id' => 'attach_id',
				'parent' => 'parent',
				'size' => 'size',
				'status' => 'status',
				'compression_type' => 'compression_type'
			)
		);

			foreach ($definitions as $raw_tableName => $indexes) {
				$tableName = esc_sql( $wpdb->prefix . $raw_tableName );
				foreach ($indexes as $indexName => $fieldName) {
					$indexName = preg_replace( '/[^A-Za-z0-9_]/', '', $indexName );
					$fieldName = preg_replace( '/[^A-Za-z0-9_]/', '', $fieldName );
					// Check exists
					// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared, WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Query is prepared here and table name is plugin-owned.
					$res = $wpdb->get_row(
						$wpdb->prepare(
							"SHOW INDEX FROM {$tableName} WHERE Key_name = %s",
							$indexName
						)
					);

					if (is_null($res)) {
						// phpcs:ignore PluginCheck.Security.DirectDB.UnescapedDBParameter, WordPress.DB.PreparedSQL.InterpolatedNotPrepared, WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.DirectDatabaseQuery.SchemaChange -- Schema query uses sanitized identifiers and plugin-owned table names.
						$res = $wpdb->query( "CREATE INDEX {$indexName} ON {$tableName} ( {$fieldName} )" );
					}
				}
			}
	}

	private static function removeTables()
	{
			global $wpdb;
			if (self::checkTableExists('shortpixel_folders') === true) {
				$wpdb->query( 'DROP TABLE ' . esc_sql( $wpdb->prefix . 'shortpixel_folders' ) );
			}
			if (self::checkTableExists('shortpixel_meta') === true) {
				$wpdb->query( 'DROP TABLE ' . esc_sql( $wpdb->prefix . 'shortpixel_meta' ) );
			}
			if (self::checkTableExists('shortpixel_postmeta') === true) {
				$wpdb->query( 'DROP TABLE ' . esc_sql( $wpdb->prefix . 'shortpixel_postmeta' ) );
			}
			if (self::checkTableExists('shortpixel_aipostmeta') === true) {
				$wpdb->query( 'DROP TABLE ' . esc_sql( $wpdb->prefix . 'shortpixel_aipostmeta' ) );
			}
		}

	private static function getFolderTableSQL()
	{
		global $wpdb;
		$charsetCollate = $wpdb->get_charset_collate();
		$prefix = $wpdb->prefix;

		return "CREATE TABLE {$prefix}shortpixel_folders (
          id mediumint(9) NOT NULL AUTO_INCREMENT,
          path varchar(512),
          name varchar(150),
          path_md5 char(32),
          file_count int,
          status SMALLINT NOT NULL DEFAULT 0,
          parent SMALLINT DEFAULT 0,
          ts_checked timestamp,
          ts_updated timestamp,
          ts_created timestamp,
          PRIMARY KEY id (id)
        ) $charsetCollate;";
	}

	private static function getMetaTableSQL()
	{
		global $wpdb;
		$charsetCollate = $wpdb->get_charset_collate();
		$prefix = $wpdb->prefix;

		return "CREATE TABLE {$prefix}shortpixel_meta (
          id mediumint(10) NOT NULL AUTO_INCREMENT,
          folder_id mediumint(9) NOT NULL,
          ext_meta_id int(10),
          path varchar(512),
          name varchar(150),
          path_md5 char(32),
          compressed_size int(10) NOT NULL DEFAULT 0,
          compression_type tinyint,
          keep_exif tinyint DEFAULT 0,
          cmyk2rgb tinyint DEFAULT 0,
          resize tinyint,
          resize_width smallint,
          resize_height smallint,
          backup tinyint DEFAULT 0,
          status SMALLINT NOT NULL DEFAULT 0,
          retries tinyint NOT NULL DEFAULT 0,
          message varchar(255),
          ts_added timestamp,
          ts_optimized timestamp,
					extra_info LONGTEXT,
          PRIMARY KEY sp_id (id)
        ) $charsetCollate;";
	}

	private static function getPostMetaSQL()
	{
		global $wpdb;
		$charsetCollate = $wpdb->get_charset_collate();
		$prefix = $wpdb->prefix;

		$sql = "CREATE TABLE {$prefix}shortpixel_postmeta (
			 id bigint unsigned NOT NULL AUTO_INCREMENT,
			 attach_id bigint unsigned NOT NULL,
			 parent bigint unsigned NOT NULL,
			 image_type tinyint default 0,
			 size varchar(200),
			 status tinyint default 0,
			 compression_type tinyint,
			 compressed_size  int,
			 original_size int,
			 tsAdded timestamp,
			 tsOptimized  timestamp,
			 extra_info LONGTEXT,
			 PRIMARY KEY id (id)
		 ) $charsetCollate;";

		return $sql;
	}

	private static function getAIPostSQL()
	{
		global $wpdb; 
		$charsetCollate = $wpdb->get_charset_collate();
		$prefix = $wpdb->prefix;

		$sql = "CREATE TABLE {$prefix}shortpixel_aipostmeta (
				id bigint unsigned not null AUTO_INCREMENT, 
				post_type tinyint default 1,
				attach_id bigint unsigned NOT NULL,  
				original_data text, 
				generated_data text, 
				old_filename varchar(300), 
				new_filename varchar(300),
				status int, 
				tsUpdated timestamp, 
				PRIMARY KEY id (id)
		) $charsetCollate";

		return $sql;
	}
} // InstallHelper
