<?php
// phpcs:disable WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound -- Template files receive local context variables from their controller.
if ( ! defined( 'ABSPATH' ) ) {
  exit;
}

foreach ( $this->view->actions as $actionName => $action ) :

  $layout   = isset( $action['layout'] ) ? $action['layout'] : false;
  $disabled = ! empty( $action['disabled'] );
  $itemId   = property_exists( $this->view, 'id' ) ? intval( $this->view->id ) : 0;
  $classes  = $actionName;

  if ( isset( $action['display'] ) ) {
    switch ( $action['display'] ) {
      case 'button':
        $classes = " button-smaller button-primary $actionName ";
        break;
      case 'button-secondary':
        $classes = " button-smaller button button-secondary $actionName ";
        break;
    }
  }

  if ( ! empty( $action['custom_classes'] ) ) {
    $classes = trim( $classes . ' ' . $action['custom_classes'] );
  }

  if ( $disabled ) {
    $classes .= ' disabled';
  }

  $link          = $disabled ? 'javascript:void(0)' : ( ( $action['type'] === 'js' ) ? 'javascript:' . $action['function'] : $action['function'] );
  $title         = isset( $action['title'] ) ? ' title="' . esc_attr( $action['title'] ) . '" ' : '';
  $disabledAttrs = $disabled ? ' aria-disabled="true" tabindex="-1" ' : '';
  $actionAttrs   = '';
  $iconHtml      = '';

  if ( $itemId > 0 ) {
    $actionAttrs .= ' data-spui-action-id="' . esc_attr( $itemId ) . '" data-spui-action-name="' . esc_attr( $actionName ) . '"';
  }

  if ( ! empty( $action['icon_html'] ) ) {
    $iconHtml = $action['icon_html'];
  }

  if ( $layout && $layout === 'paragraph' ) {
    echo '<p>';
  }
  ?>
  <a href="<?php echo esc_attr( $link ); ?>"
     <?php echo $title . $disabledAttrs . $actionAttrs; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Built above from escaped values or static strings. ?>
     class="<?php echo esc_attr( $classes ); ?>"
  ><?php echo $iconHtml; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Internal admin icon markup. ?><span><?php echo esc_html( $action['text'] ); ?></span></a>
  <?php
  if ( $layout && $layout === 'paragraph' ) {
    echo '</p>';
  }

endforeach;
