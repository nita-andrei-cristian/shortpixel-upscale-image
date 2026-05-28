<?php
namespace ShortPixel;

use ShortPixel\Helper\UiHelper as UiHelper;

if ( ! defined( 'ABSPATH' ) ) {
 exit; // Exit if accessed directly.
}

?>
<hr class='wp-header-end'>

<div class="wrap is-spui-settings-page is-shortpixel-settings-page <?php echo esc_attr($this->view_mode); ?> ">

<header>
  <h1>
      <?php echo UIHelper::getIcon('res/images/illustration/logo_settings.svg'); ?>
  </h1>
  <div class='top-buttons'>
    <?php if ( !$view->key->hide_api_key ) { ?>
      <a class='header-button' href="https://shortpixel.com/<?php
        echo esc_attr(($view->key->apiKey ? "login/". $view->key->apiKey . "/dashboard" : "login"));
        ?>" target="_blank">
          <i class='shortpixel-icon user'></i><name><?php esc_html_e('ShortPixel Account','shortpixel-upscale-image'); ?></name>
      </a>
    <?php } ?>
  </div>
</header>

  <input type='checkbox' name='heavy_features' value='1' <?php echo ($this->disable_heavy_features) ? 'checked' : '' ?> class='shortpixel-hide' />

<article class='spui-settings shortpixel-settings'>
  <label class='mobile-menu closed'>
    <span class='open'><?php echo UIHelper::getIcon('res/images/icon/accordion.svg'); ?></span>
    <span class='close'><?php echo UIHelper::getIcon('res/images/icon/close.svg'); ?></span>
    <input type='checkbox'>
  </label>

  <menu>
    <ul>
      <li>
        <?php echo $this->settingLink([
          'part' => 'optimisation',
          'title' => __('Image Upscaling', 'shortpixel-upscale-image'),
          'icon' => 'shortpixel-icon optimization'
        ]); ?>
      </li>
      <li>
        <?php echo $this->settingLink([
          'part' => 'help',
          'title' => __('Help Center', 'shortpixel-upscale-image'),
          'icon' => 'shortpixel-icon help-circle'
        ]); ?>
      </li>
    </ul>
  </menu>

  <section class="wrapper">
    <form name='wp_spui_options' action='<?php echo esc_url(add_query_arg('noheader', 'true')) ?>' method='post' id='wp_spui_options'>
      <input type='hidden' name='display_part' value="<?php echo esc_attr($this->display_part) ?>" />
      <?php wp_nonce_field($this->form_action, 'sp-nonce'); ?>

      <?php $this->loadView('settings/part-optimisation'); ?>
      <?php $this->loadView('settings/part-help'); ?>
      <?php $this->loadView('settings/part-nokey'); ?>
    </form>
  </section>
</article>

<section class='ajax-save-done'>
  <div class="icon-container">
    <span class="shortpixel-icon ok" aria-hidden="true"></span>
  </div>
  <div class="text-container">
    <h2><?php esc_html_e('Settings successfully saved! ', 'shortpixel-upscale-image'); ?></h2>
    <h3 class='after-save-notices'><span class='notice_count'>X</span> <?php esc_html_e('new notices', 'shortpixel-upscale-image'); ?></h3>
  </div>
</section>

</div>

<?php $this->loadView('snippets/part-inline-modal'); ?>
