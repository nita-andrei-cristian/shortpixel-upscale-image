<?php
namespace SPUI;

use SPUI\Helper\UiHelper as UiHelper;

if ( ! defined( 'ABSPATH' ) ) {
 exit; // Exit if accessed directly.
}

$spui_filesystem = \wpSPUI()->filesystem();

// phpcs:ignore WordPress.Security.NonceVerification.Recommended  -- This is not a form
if ( isset($_GET['noheader']) ) {
    require_once(ABSPATH . 'wp-admin/admin-header.php');
}

$this->loadView('custom/part-othermedia-top');

?>

<div class='extra-heading top'>
  <span>&nbsp;</span>
  <span>
    <select name='bulk-actions'>
     <option><?php esc_html_e('Bulk Actions', 'shortpixel-upscale-image'); ?></option>
     <option value='optimize'><?php esc_html_e('Upscale','shortpixel-upscale-image'); ?></option>
     <option value='restore'><?php esc_html_e('Restore', 'shortpixel-upscale-image'); ?></option>
     <option value="mark-completed"><?php esc_html_e('Mark completed', 'shortpixel-upscale-image'); ?></option>
   </select> <button class='button' type='button' name='doBulkAction'><?php esc_html_e('Apply', 'shortpixel-upscale-image'); ?></button>
  </span>

  <span class='custom-filter'>
    <form method="get" action="<?php echo esc_url( $this->url ); ?>" >
      <input type='hidden' name='page' value='spui-short-pixel-custom'>
    <?php $this->printFilter(); ?>
     <button class='button' type='submit'><?php esc_html_e('Filter', 'shortpixel-upscale-image'); ?></button>
   </form>
  </span>

</div>
    <div class='list-overview'>


      <div class='heading'>
        <?php foreach ( $this->view->headings as $spui_heading_name => $spui_heading ) :
        ?>
          <span class='heading <?php echo esc_attr( $spui_heading_name ); ?>'>
              <?php echo wp_kses_post( $this->getDisplayHeading( $spui_heading ) ); ?>
          </span>

        <?php endforeach; ?>
      </div>

        <?php if (count($this->view->items) == 0) : ?>
          <div class='no-items'> <p>
            <?php

            if (true === $view->hasSearch)
            {
              echo esc_html__('Your search query didn\'t result in any images. ', 'shortpixel-upscale-image');
             }
             elseif (true === $view->hasFilter )
             {
               /* translators: 1: Opening reset filter link. 2: Closing reset filter link. */
               printf( wp_kses_post( __( 'Filter didn\'t yield any results. %1$s Show all Items %2$s ', 'shortpixel-upscale-image' ) ), '<a href="' . esc_url( $this->url ) . '">', '</a>' );
             }
             else
             {
               $spui_folder_url = esc_url(add_query_arg('part', 'folders', $this->url));

               /* translators: 1: Opening folders link. 2: Closing folders link. */
               printf( wp_kses_post( __( 'No images available. Go to %1$s Folders %2$s to configure additional folders to be upscaled.', 'shortpixel-upscale-image' ) ), '<a href="' . esc_url( $spui_folder_url ) . '">', '</a>' );

             } ?>
          </p>
          </div>

        <?php endif; ?>

        <?php
        $spui_folders = $this->view->folders;

        foreach ( $this->view->items as $spui_item ) :

        ?>

        <div class='item item-<?php echo esc_attr( $spui_item->get('id') ) ?>'>
            <?php

              $spui_all_actions = array_merge( UiHelper::getActions( $spui_item ), UiHelper::getListActions( $spui_item ) );

              $spui_checkbox_actions = array();
              if ( array_key_exists( 'optimize', $spui_all_actions ) )
              {
                  $spui_checkbox_actions[] = 'is-optimizable';
              }
              if ( array_key_exists( 'restore', $spui_all_actions ) )
              {
                  $spui_checkbox_actions[] = 'is-restorable';
              }

              $spui_filesize = $spui_item->getFileSize();
              $spui_display_date = $this->getDisplayDate( $spui_item );
              $spui_folder_id = $spui_item->get('folder_id');

              $spui_row_actions = $this->getRowActions( $spui_item );


              $spui_folder = isset( $spui_folders[ $spui_folder_id ] ) ? $spui_folders[ $spui_folder_id ] : false;
              $spui_media_type = ( $spui_folder && $spui_folder->get('is_nextgen') ) ? __( 'Nextgen', 'shortpixel-upscale-image' ) : __( 'Custom', 'shortpixel-upscale-image' );
              $spui_img_url = $spui_filesystem->pathToUrl( $spui_item );
              $spui_is_heavy = ( $spui_filesize >= 500000 && $spui_filesize > 0 );

              $spui_item_class = '';
              if ( count( $spui_checkbox_actions ) > 0 ) {
              $spui_item_class = ' class="' . implode( ' ', $spui_checkbox_actions ) . '" ';
              }

            ?>
            <span><input type='checkbox' name='select[]' value="<?php echo esc_attr( $spui_item->get('id') ); ?>" <?php echo wp_kses_post( $spui_item_class ); ?>/></span>
            <span><a href="<?php echo esc_url( $spui_img_url ); ?>" target="_blank">
                <div class='thumb' <?php if ( $spui_is_heavy )
								{
								 	echo 'title="' . esc_attr__( 'This image is heavy and it would slow this page down if displayed here. Click to open it in a new browser tab.', 'shortpixel-upscale-image' ) . '"';
								}
                ?> style="background-image:url('<?php echo esc_url( $spui_is_heavy ? wpSPUI()->plugin_url('res/img/heavy-image-2x.png') : $spui_img_url ); ?>')">
							</div>
                </a></span>
            <span class='filename'><?php echo esc_html( $spui_item->getFileName() ) ?>

                <div class="row-actions">
                  <span class='item-id'>#<?php echo esc_attr( $spui_item->get('id') ); ?></span>

                  <?php
								if ( isset( $spui_row_actions ) ) :
									$spui_action_index = 0;
								  foreach ( $spui_row_actions as $spui_action_name => $spui_action ) :


								    $spui_classes = '';
								    $spui_link = ( $spui_action['type'] == 'js' ) ? '#' : $spui_action['function'];
								    $spui_onclick = ( $spui_action['type'] == 'js' ) ? $spui_action['function'] : '';
										$spui_new_tab  = ( $spui_action_name == 'extendquota' || $spui_action_name == 'view' ) ? 'target="_blank"' : '';

										if ( $spui_action_index > 0 )
											echo "|";
								    ?>
								   	<a href="<?php echo esc_url( $spui_link ); ?>" <?php echo wp_kses_post( $spui_new_tab ); ?><?php echo ( '' !== $spui_onclick ) ? ' onclick="' . esc_attr( $spui_onclick ) . '"' : ''; ?> class="<?php echo esc_attr( $spui_classes ); ?>"><?php echo esc_html( $spui_action['text'] ); ?></a>
								    <?php
										$spui_action_index++;
								  endforeach;
                  UiHelper::getActions( $spui_item );

								endif;
                ?>
							</div>
            </span>
            <span class='folderpath'><?php echo  esc_html( (string) $spui_item->getFileDir()); ?></span>
            <span class='mediatype'><?php echo esc_html( $spui_media_type ) ?></span>
            <span class="date"><?php echo esc_html( $spui_display_date ) ?></span>

            <span >
								<?php $this->doActionColumn( $spui_item ); ?>
	          </span>

        </div>
        <?php endforeach; ?>
      </div>


      <div class='pagination tablenav bottom'>
				<div class="view_switch">
					<?php if ($this->has_hidden_items || $this->show_hidden):

						if ($this->show_hidden)
						{
							 printf('<a href="%s">%s</a>', esc_url(add_query_arg('show_hidden',false)), esc_html__('Back to normal items', 'shortpixel-upscale-image'));
						}
						else
						{
							 printf('<a href="%s">%s</a>', esc_url(add_query_arg('show_hidden',true)), esc_html__('Show hidden items', 'shortpixel-upscale-image'));
						}

					 endif; ?>
				</div>
        <div class='tablenav-pages'>
            <?php echo wp_kses_post( $this->view->pagination ); ?>
        </div>
      </div>


</div> <!-- wrap -->

<?php $this->loadView('snippets/part-comparer'); ?>
