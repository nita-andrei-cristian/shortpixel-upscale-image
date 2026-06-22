<?php

namespace SPUI;
use SPUI\ShortPixelLogger\ShortPixelLogger as Log;

use SPUI\Helper\UiHelper as UiHelper;


if ( ! defined( 'ABSPATH' ) ) {
 exit; // Exit if accessed directly.
}

$spui_item = $this->view->current_item;

$spui_folder_id = $spui_item->get('id');

$spui_type_display = ( $spui_item->get( 'is_nextgen' ) ) ? __( 'Nextgen', 'shortpixel-upscale-image' ) : __( 'Custom Media', 'shortpixel-upscale-image' );
$spui_stat         = $spui_item->getStats();


$spui_fullstatus = esc_html__( 'Upscaled', 'shortpixel-upscale-image' ) . ': ' . $spui_stat['optimized'] . "\n"
      . esc_html__( 'Not Upscaled', 'shortpixel-upscale-image' ) . ': ' . $spui_stat['waiting'] . "\n"
      ;


$spui_err = ''; // unused since failed is gone.
if ( ! $spui_item->exists() && ! $spui_err ) {
  $spui_err = __( 'Directory does not exist', 'shortpixel-upscale-image' );
}


if ( $spui_item->get( 'is_nextgen' ) && $view->settings->includeNextGen == 1 ) {
  $action = false;
}

$spui_refresh_url = add_query_arg( array( 'sp-action' => 'action_refreshfolder', 'folder_id' => $spui_folder_id, 'part' => 'adv-settings' ), $this->url ); // has url

$spui_row_actions = $this->getRowActions( $spui_item );
?>
<div class='item item-<?php echo esc_attr( $spui_item->get( 'id' ) ); ?>'>
  <span><input type="checkbox" /></span>

    <span class='folder folder-<?php echo esc_attr( $spui_item->get( 'id' ) ); ?>'>
        <?php echo esc_html( $spui_item->getPath() ); ?>

      <div class="row-actions">
      <span class='item-id'>#<?php echo esc_html( $spui_item->get( 'id' ) ); ?></span>
      <?php
      if ( isset( $spui_row_actions ) ) :
        $spui_i = 0;
        foreach ( $spui_row_actions as $spui_action_name => $spui_action ) :
          $spui_classes = '';
          $spui_link    = ( 'js' === $spui_action['type'] ) ? '#' : $spui_action['function'];
          $spui_onclick = ( 'js' === $spui_action['type'] ) ? $spui_action['function'] : '';

          if ( $spui_i > 0 ) {
            echo "|";
          }
          ?>
          <a href="<?php echo esc_url( $spui_link ); ?>"<?php echo ( '' !== $spui_onclick ) ? ' onclick="' . esc_attr( $spui_onclick ) . '"' : ''; ?> class="<?php echo esc_attr( $spui_classes ); ?>"><?php echo esc_html( $spui_action['text'] ); ?></a>
          <?php
          $spui_i++;
        endforeach;

      endif;
      ?>
    </div>


    </span>
    <span>
        <?php echo esc_html( $spui_type_display ); ?>
    </span>
    <span>

        <span title="<?php echo esc_attr( $spui_fullstatus ); ?>" class='info-icon'>
            <img alt='<?php esc_attr_e( 'Info Icon', 'shortpixel-upscale-image' ); ?>' src='<?php echo esc_url( wpSPUI()->plugin_url( 'res/img/info-icon.png' ) ); ?>' style="margin-bottom: -2px;"/>
        </span>&nbsp;<?php
        //echo esc_html( $spui_type_display . ' ' );
        ?>

        <span class='files-number'><?php
          echo esc_html( $spui_stat['optimized'] );
          echo '/';
          echo esc_html( $spui_stat['total'] ); ?>
        </span> <?php esc_html_e( 'Files', 'shortpixel-upscale-image' ); ?>
    </span>
		<span class='updated'>
        <?php echo esc_html( UiHelper::formatTS( $spui_item->get( 'updated' ) ) ); ?>
    </span>
    <span class='status'>

    </span>

</div>
