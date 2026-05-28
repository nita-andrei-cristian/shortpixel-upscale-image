<?php
namespace ShortPixel;

if ( ! defined( 'ABSPATH' ) ) {
 exit; // Exit if accessed directly.
}

use ShortPixel\Helper\UiHelper as UiHelper;
?>

<section id="tab-help" class="<?php echo ($this->display_part == 'help') ? 'active setting-tab' :'setting-tab'; ?>" data-part="help" >

  <div class='help-center-wrap step-highlight-4'>
    <div class='help-center-stack'>
      <div class='help-center help-center-row-top'>
        <div class='help-center-card'>
          <span class='main-icon'><?php echo wp_kses(UIHelper::getIcon('res/images/icon/help.svg'), ['img' => ['src' => true, 'class' => true, 'height' => true, 'width' => true, 'alt' => true]]); ?></span>
          <h4><?php esc_html_e('Knowledge base', 'shortpixel-upscale-image'); ?></h4>
          <p><?php esc_html_e('Most customer questions are answered in our Knowledge Base.', 'shortpixel-upscale-image'); ?></p>

          <span class="shortpixel-button-container">
          <a href="https://shortpixel.com/knowledge-base/" target="_blank" class="button-setting">
             <?php esc_html_e('Knowledge Base', 'shortpixel-upscale-image'); ?>
          </a>
          </span>
        </div>
        <div class='help-center-card'>
          <span class='main-icon'><?php echo wp_kses(UIHelper::getIcon('res/images/icon/envelope.svg'), ['img' => ['src' => true, 'class' => true, 'height' => true, 'width' => true, 'alt' => true]]); ?></span>
          <h4><?php esc_html_e('Contact us', 'shortpixel-upscale-image'); ?></h4>
          <p><?php esc_html_e('Contact us with any issues, bug reports, or questions.', 'shortpixel-upscale-image'); ?></p>

          <span class="shortpixel-button-container">
          <a href="https://shortpixel.com/contact" target="_blank" class="button-setting">
             <?php esc_html_e('Contact Us', 'shortpixel-upscale-image'); ?>
          </a>
          </span>
        </div>
      </div>
      <div class='help-center help-center-row-bottom'>
        <div class='help-center-card'>
          <span class='main-icon'><?php echo wp_kses(UIHelper::getIcon('res/images/icon/lightbulb.svg'), ['img' => ['src' => true, 'class' => true, 'height' => true, 'width' => true, 'alt' => true]]); ?></span>
          <h4><?php esc_html_e('Feature Request', 'shortpixel-upscale-image'); ?></h4>
          <p><?php esc_html_e('Is there a feature missing? Do you have suggestions for improving ShortPixel?', 'shortpixel-upscale-image'); ?></p>

          <span class="shortpixel-button-container">
          <a href="https://ideas.shortpixel.com/" target="_blank" class="button-setting">
             <?php esc_html_e('Feature Request', 'shortpixel-upscale-image'); ?>
          </a>
          </span>
        </div>
      </div>
    </div>
  </div>
</section>
