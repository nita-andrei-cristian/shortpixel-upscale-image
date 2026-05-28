<?php
// phpcs:disable WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound -- Template files receive local context variables from their controller.
namespace ShortPixel;
use ShortPixel\ShortPixelLogger\ShortPixelLogger as Log;

if ( ! defined( 'ABSPATH' ) ) {
 exit; // Exit if accessed directly.
}

$spui_datastring = '';
if ( property_exists( $this->view, 'infoData' ) ) {
  foreach ( $this->view->infoData as $spui_key => $spui_data ) {
    $spui_datastring .= ' data-' . esc_attr( $spui_key ) . '="' . esc_attr( $spui_data ) . '"';
  }
}
?>

<div class='sp-column-info <?php echo esc_attr( property_exists( $this->view, 'infoClass' ) ? $this->view->infoClass : '' ); ?>'
     <?php echo wp_kses_data( $spui_datastring ); ?>
     id='spui-data-<?php echo esc_attr( $this->view->id ); ?>'>

  <?php if ( isset( $this->view->list_actions ) ) : ?>
    <?php echo wp_kses_post( $this->view->list_actions ); ?>
  <?php endif; ?>

  <div class='statusText'>
    <?php if ( property_exists( $this->view, 'text' ) && ! is_null( $this->view->text ) && strlen( $this->view->text ) > 0 ) : ?>
      <?php echo wp_kses_post( $this->view->text ); ?>
    <?php endif; ?>
  </div>

  <?php if ( property_exists( $this->view, 'actions' ) ) : ?>
    <?php $this->loadView( 'snippets/part-single-actions', false ); ?>
  <?php endif; ?>

</div>
