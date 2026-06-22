<?php
namespace SPUI;

if ( ! defined( 'ABSPATH' ) ) {
 exit; // Exit if accessed directly.
}
?>
<div class="wrap shortpixel-other-media">
    <h2>
        <?php echo esc_html( $view->title );?>
    </h2>

    <div class='toolbar'>

			<hr class='wp-header-end' />

<?php if (property_exists($view, 'show_search') && true === $view->show_search):  ?>
      <div class="searchbox">
            <form method="get">
                <input type="hidden" name="page" value="spui-short-pixel-custom" />
                <input type='hidden' name='order' value="<?php echo esc_attr($this->order) ?>" />
                <input type="hidden" name="orderby" value="<?php echo esc_attr($this->orderby) ?>" />

                <p class="search-form">
                  <label><?php esc_html_e('Search', 'shortpixel-upscale-image'); ?></label>
                  <input type="text" name="s" value="<?php echo esc_attr($this->search) ?>" />

                </p>

            </form>
      </div>
  </div>
<?php endif;  ?>

  <div class='pagination tablenav'>

			<?php if ($this->view->pagination !== false): ?>
	      <div class='tablenav-pages'>
	        <?php echo wp_kses_post( $this->view->pagination ); ?>
	    	</div>
			<?php endif; ?>
  </div>

<?php
$spui_file_url =  esc_url(add_query_arg('part', 'files', $this->url));
$spui_folder_url = esc_url(add_query_arg('part', 'folders', $this->url));
$spui_scan_url = esc_url(add_query_arg('part', 'scan', $this->url));

// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Read-only tab switch parameter.
$spui_current_part = isset( $_GET['part'] ) ? sanitize_text_field( wp_unslash( $_GET['part'] ) ) : 'files';

$tabs = array(
	'files' => array('link' => $spui_file_url,
									 'text' => __('Files', 'shortpixel-upscale-image'),
								 ),
	 'folders' => array('link' => $spui_folder_url,
	 										'text' => __('Folders', 'shortpixel-upscale-image'),
 								),
		'scan' => array('link' => $spui_scan_url,
										'text' => __('Scan', 'shortpixel-upscale-image'),

	),
);

?>

<div class="custom-media-tabs">
		<?php foreach ( $tabs as $spui_tab_name => $spui_tab )
		{
				$spui_class = ( $spui_current_part == $spui_tab_name ) ? ' class="selected" ' : '';

				echo '<a href="' . esc_url( $spui_tab['link'] ) . '" ' . wp_kses_post( $spui_class ) . '>' . esc_html( $spui_tab['text'] ) . '</a>';
		} ?>
</div>
