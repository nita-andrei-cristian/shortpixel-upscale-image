<?php
namespace SPUI\Replacer\Modules;
use SPUI\ShortPixelLogger\ShortPixelLogger as Log;

// Integration to reset indexes of Yoast  (used for Og:image) when something is converted.
class YoastSeo
{

	private $yoastTable;
	private static $instance;

	public static function getInstance()
	{
			if (is_null(self::$instance))
				self::$instance = new YoastSeo();

			return self::$instance;
	}

	public function __construct()
	{
		if (true === $this->yoast_is_active())   // elementor is active
		{
			 global $wpdb;
			 $this->yoastTable = $wpdb->prefix . 'yoast_indexable';

			 add_action('spui/replacer/replace_urls', array($this, 'removeIndexes'),10,2);
		}
	}

		public function removeIndexes($search_urls, $replace_urls)
		{
			 global $wpdb;
			 $yoast_table = esc_sql( $this->yoastTable );

			$base = isset($search_urls['base']) ? $search_urls['base'] : null;
			$file = isset($search_urls['file']) ? $search_urls['file'] : null;

				if (! is_null($base))
				{
								// phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared, WordPress.DB.PreparedSQL.InterpolatedNotPrepared, WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Query is prepared here and the table name is plugin-owned.
								$wpdb->query(
								// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- Query is prepared here and the table name is plugin-owned.
								$wpdb->prepare(
									"DELETE FROM {$yoast_table} WHERE twitter_image like %s or open_graph_image like %s ",
									'%' . $base . '%',
									'%' . $base . '%'
								)
							);
				}

				if (! is_null($file))
				{
								// phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared, WordPress.DB.PreparedSQL.InterpolatedNotPrepared, WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Query is prepared here and the table name is plugin-owned.
								$wpdb->query(
								// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- Query is prepared here and the table name is plugin-owned.
								$wpdb->prepare(
									"DELETE FROM {$yoast_table} WHERE twitter_image like %s or open_graph_image like %s ",
									'%' . $file . '%',
									'%' . $file . '%'
								)
							);
				}

	}

	protected function yoast_is_active()
	{
		 if (defined('WPSEO_VERSION'))
		 {
			  return true;
		 }
		 return false;
	}




}
