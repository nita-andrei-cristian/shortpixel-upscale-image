<?php

namespace SPUI;

if (! defined('ABSPATH')) {
  exit; // Exit if accessed directly.
}

?>

<section id="tab-optimisation" class="<?php echo ($this->display_part == 'optimisation') ? 'active setting-tab' : 'setting-tab'; ?>" data-part="optimisation">

  <?php if (true === \wpSPUI()->env()->useTrustedMode()) : ?>
    <div class='compression-notice warning'>
      <p>
        <?php esc_html_e('Trusted file mode is active. This means that ShortPixel will depend on the metadata and not check the filesystem while loading the UI. Information may be incorrect and errors may occur during upscaling.', 'shortpixel-upscale-image'); ?>
      </p>
      <?php if (true === \SPUI\Pantheon::IsActive()) : ?>
        <p><?php esc_html_e('(You are on Pantheon. This setting was automatically activated)', 'shortpixel-upscale-image'); ?></p>
      <?php endif; ?>
    </div>
  <?php endif; ?>

  <settinglist>
    <h2><?php esc_html_e('Image Upscaling Settings', 'shortpixel-upscale-image'); ?></h2>

    <gridbox class='width_half step-highlight-2'>

      <setting class='switch'>
        <content>
          <?php
          $this->printSwitchButton(
            [
              'name' => 'processThumbnails',
              'checked' => $view->data->processThumbnails,
              'label' => esc_html__('Upscale Thumbnails', 'shortpixel-upscale-image'),
            ]
          );
          ?>

          <i class='documentation dashicons dashicons-editor-help' data-link="https://shortpixel.com/knowledge-base/article/settings-upscale-thumbnails/?target=iframe"></i>
          <name><?php esc_html_e('Upscale image thumbnails', 'shortpixel-upscale-image'); ?></name>
          <info>
            <?php printf(esc_html__('It is highly recommended to upscale thumbnails, as they are often the images most viewed by end users. %s Please note that thumbnails count toward your total quota.', 'shortpixel-upscale-image'), '<br>'); ?>
          </info>
        </content>
      </setting>

      <setting class='switch'>
        <content>
          <?php
          $this->printSwitchButton(
            [
              'name' => 'optimizeUnlisted',
              'checked' => $view->data->optimizeUnlisted,
              'label' => esc_html__('Upscale unlisted thumbnails', 'shortpixel-upscale-image'),
            ]
          );
          ?>

          <i class='documentation dashicons dashicons-editor-help' data-link="https://shortpixel.com/knowledge-base/article/settings---upscale-other-thumbs/?target=iframe"></i>
          <name><?php esc_html_e('Upscale unlisted thumbnails, if found.', 'shortpixel-upscale-image'); ?></name>
        </content>
        <warning class="heavy-feature-virtual unlisted">
          <message>
            <?php printf(esc_html__('This feature has been disabled in offload mode for performance reasons. You can enable it again with a %s filter hook %s ', 'shortpixel-upscale-image'), '<a target="_blank" href="https://shortpixel.com/knowledge-base/">', '</a>'); ?>
          </message>
        </warning>
      </setting>

      <setting class='switch'>
        <content>
          <?php
          $this->printSwitchButton(
            [
              'name' => 'optimizePdfs',
              'checked' => $view->data->optimizePdfs,
              'label' => esc_html__('Upscale PDFs', 'shortpixel-upscale-image'),
            ]
          );
          ?>
          <i class='documentation dashicons dashicons-editor-help' data-link="https://shortpixel.com/knowledge-base/article/settings-upscale-pdfs/?target=iframe"></i>
          <name><?php esc_html_e('Also upscale PDF documents.', 'shortpixel-upscale-image'); ?></name>
        </content>
      </setting>

      <setting class='switch'>
        <content>
          <?php
          $this->printSwitchButton(
            [
              'name' => 'optimizeRetina',
              'checked' => $view->data->optimizeRetina,
              'label' => esc_html__('Upscale Retina images', 'shortpixel-upscale-image'),
            ]
          );
          ?>

          <i class='documentation dashicons dashicons-editor-help' data-link="https://shortpixel.com/knowledge-base/article/settings-upscale-retina-images/?target=iframe"></i>
          <name><?php esc_html_e('Also upscale the Retina images (@2x) if they exist.', 'shortpixel-upscale-image'); ?></name>
        </content>

        <warning class='heavy-feature-virtual retina'>
          <message>
            <?php printf(esc_html__('This feature has been disabled in offload mode for performance reasons. You can enable it again with a %s filter hook %s ', 'shortpixel-upscale-image'), '<a target="_blank" href="https://shortpixel.com/knowledge-base/">', '</a>'); ?>
          </message>
        </warning>
      </setting>

      <?php if ($this->has_nextgen) : ?>
        <setting class='switch'>
          <content>
            <?php
            $this->printSwitchButton(
              [
                'name' => 'includeNextGen',
                'checked' => $view->data->includeNextGen,
                'label' => esc_html__('Upscale NextGen galleries', 'shortpixel-upscale-image'),
              ]
            );
            ?>
            <i class='documentation dashicons dashicons-editor-help' data-link="https://shortpixel.com/knowledge-base/article/how-to-upscale-your-nextgen-galleries-with-shortpixel-image-upscaler/?target=iframe"></i>
            <name><?php esc_html_e('Enable this option to upscale the NextGen galleries automatically.', 'shortpixel-upscale-image'); ?></name>
          </content>
        </setting>
      <?php endif; ?>

    </gridbox>
  </settinglist>

  <?php $this->loadView('settings/part-savebuttons', false); ?>

</section>
