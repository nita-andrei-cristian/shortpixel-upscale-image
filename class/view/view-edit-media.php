<?php
// phpcs:disable WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound -- Template files receive local context variables from their controller.
namespace ShortPixel;
use ShortPixel\ShortPixelLogger\ShortPixelLogger as Log;

if ( ! defined( 'ABSPATH' ) ) {
 exit; // Exit if accessed directly.
}
?>
<div id='shortpixel-data-<?php echo esc_attr( $view->id ); ?>' class='column-spui view-edit-media'
  data-imagewidth="<?php echo esc_attr( $view->image['width'] ); ?>"
  data-imageheight="<?php echo esc_attr( $view->image['height'] ); ?>"
  data-extension="<?php echo esc_attr( $view->image['extension'] ); ?>"
>
<?php if ( ! is_null( $view->debugInfo ) && is_array( $view->debugInfo ) && count( $view->debugInfo ) > 0 ) : ?>
  <div class='debugInfo' id='debugInfo'>
    <a class='debugModal' data-modal="debugInfo"><?php esc_html_e( 'Debug Window', 'shortpixel-upscale-image' ); ?></a>
    <div class='content wrapper'>
      <?php foreach ( $view->debugInfo as $index => $item ) : ?>
      <ul class="debug-<?php echo esc_attr( $index ); ?>">
        <li><strong><?php echo esc_html( $item[0] ); ?></strong>
          <?php
          if ( is_array( $item[1] ) || is_object( $item[1] ) ) {
            echo '<pre>' . esc_html( wp_json_encode( $item[1], JSON_PRETTY_PRINT ) ) . '</pre>';
          } else {
            echo esc_html( $item[1] );
          }
          ?>
        </li>
      </ul>
      <?php endforeach; ?>
      <p>&nbsp;</p>
    </div>
  </div>
<?php endif; ?>

<?php if ( ( property_exists( $this->view, 'text' ) && strlen( $this->view->text ) > 0 ) || ( isset( $this->view->actions ) && count( $this->view->actions ) > 0 ) ) : ?>
<div class='spui-edit-media-actions shortpixel-upscale-interface' data-spui-actions="<?php echo esc_attr( $view->id ); ?>">
  <?php if ( property_exists( $this->view, 'text' ) && strlen( $this->view->text ) > 0 ) : ?>
    <p class="spui-status-text"><?php echo wp_kses_post( $this->view->text ); ?></p>
  <?php endif; ?>

  <?php if ( isset( $this->view->actions ) && count( $this->view->actions ) > 0 ) : ?>
    <?php $this->loadView( 'snippets/part-single-actions', false ); ?>
  <?php endif; ?>
</div>
<?php endif; ?>

</div>

<div id="sp-message-<?php echo esc_attr( $this->view->id ); ?>" class='spui-message'>
<?php if ( ! is_null( $view->status_message ) ) : ?>
<?php echo esc_html( $view->status_message ); ?>
<?php endif; ?>
</div>

<div id='shortpixel-errorbox' class="errorbox">&nbsp;</div>

<?php $this->loadView( 'snippets/part-comparer' ); ?>
