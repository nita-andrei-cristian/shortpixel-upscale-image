<?php
namespace SPUI;

if ( ! defined( 'ABSPATH' ) ) {
 exit; // Exit if accessed directly.
}

$view->settings = [];
$view->settings['bg_type'] = 'placeholder'; // @todo Add something here.
$view->settings['bg_color'] = '#000';
$view->settings['bg_transparency'] = 100;

$originalImage = $this->data['originalImage'];
$previewImage = $this->data['previewImage'];
$fileName = $originalImage->getFileName();
$placeholderImage = $this->data['placeholderImage'];
$post_title = $this->data['post_title'];
$action_name = $this->data['action_name'];
$defaultScale = isset($this->data['defaultScale']) ? intval($this->data['defaultScale']) : 2;

if (! in_array($defaultScale, [2, 3, 4], true))
{
	$defaultScale = 2;
}

	switch($action_name)
	{
		case 'remove':
			$modal_title = __('AI Background Removal', 'shortpixel-upscale-image');
			$suggestedFileName = $originalImage->getFileBase() . '_nobg.' . $originalImage->getExtension();

	break;

		case 'scale':
			$modal_title = __('AI Image Upscale', 'shortpixel-upscale-image');
			$suggestedFileName = \SPUI\Helper\UiHelper::buildUpscaleFileName($originalImage, $defaultScale);

	break;
}

$image_width = $originalImage->get('width');
$scale_sizes =
 [
	'2' => 1200,
	'3' => 1200,
	'4' => 1024,
 ];

 $scaleOptions = '';
 $checked = $defaultScale;
 foreach($scale_sizes as $scaleName => $max_size)
 {
	$isChecked = ((string) $checked === (string) $scaleName) ? 'checked' : '';
	$disabled = ($max_size <= $image_width) ? ' disabled ' : '';

		 $scaleOptions .= sprintf('<li><input type="radio" name="scale" value="%1$s" %2$s > %3$s </li>',
		 esc_attr( $scaleName ), esc_attr( $isChecked . $disabled ), esc_html( $scaleName . 'x' )
		);
 }

?>

<div class="modal-wrapper" id="media-modal" data-item-id="<?php echo esc_attr( intval($this->data['item_id']) ); ?>" data-action-name="<?php echo esc_attr($action_name) ?>" >
    <div class="title"><h3><?php echo esc_html( $modal_title ); ?> <span data-action='close'>X</span></h3> </div>
	<div class='modal-content-wrapper'>

    <div class="image-wrapper">
            <div class="image-original">
                <i style="background-image: url('<?php echo esc_url($previewImage->getURL()); ?>');"></i>
					<span><?php esc_html_e('Before', 'shortpixel-upscale-image'); ?>
            </div>
			<div class="image-arrow">
				<i class='shortpixel-icon arrow-right'></i>
			</div>
            <div class="image-preview">
					<span><?php esc_html_e('After', 'shortpixel-upscale-image'); ?></span>
                <i data-placeholder="<?php echo esc_url($placeholderImage) ?>" style="background-image: url('<?php echo esc_url($placeholderImage) ?>');" ></i>
				<div class='error-message shortpixel-hide'>&nbsp;</div>
                <div class='load-preview-spinner shortpixel-hide'><img class='loadspinner' src="<?php echo esc_url(\wpSPUI()->plugin_url('res/img/bulk/loading-hourglass.svg')); ?>" /></div>
            </div>
    </div>

    <div class='action-bar'>

    <section class="remove action_wrapper">
			<h3><?php esc_html_e("Options", 'shortpixel-upscale-image'); ?></h3>
			<p><?php esc_html_e('Note: transparency options only work with supported file formats, such as PNG', 'shortpixel-upscale-image'); ?></p>

						<label for="transparent_background">
							<input id="transparent_background" type="radio" name="background_type" value="transparent" <?php checked('transparent', $view->settings['bg_type']); ?> checked >
								<?php esc_html_e('Transparent/white background', 'shortpixel-upscale-image'); ?>
						</label>
						<p class="howto">
								<?php esc_html_e('Returns a transparent background if it is a PNG image, or a white one if it is a JPG image.', 'shortpixel-upscale-image'); ?>
						</p>

                        <label for="solid_background">
							<input id="solid_background" type="radio" name="background_type" value="solid" <?php checked('solid', $view->settings['bg_type']); ?>>
								<?php esc_html_e('Solid background', 'shortpixel-upscale-image'); ?>
						</label>
						<div id="solid_selector">
							<label for="bg_display_picker">
									<p><?php esc_html_e('Background Color:','shortpixel-upscale-image'); ?>
								<strong>
								<input type="color" value="<?php echo esc_attr($view->settings['bg_color']); ?>" name="bg_display_picker" id="bg_display_picker" />
								<!--<span style="text-transform: uppercase;" id="color_range">
									<?php echo esc_attr($view->settings['bg_color']); ?></span> -->
								</strong>

								<input type="hidden"  value="<?php echo esc_attr($view->settings['bg_color']); ?>" name="bg_color" id="bg_color" />
								</p>
							</label>
						
							<label for="bg_transparency">
									<p><?php esc_html_e('Opacity:', 'shortpixel-upscale-image'); ?>
									<strong>
										<span id="transparency_range"><?php echo esc_attr($view->settings['bg_transparency']); ?></span>%</strong>
										<input type="range" min="0" max="100" value="<?php echo esc_attr($view->settings['bg_transparency']); ?>" id="bg_transparency" />
								</p>
								
							</label>
						</div>



		</section>

		<section class="scale action_wrapper">
				<h3><?php esc_html_e("Options", 'shortpixel-upscale-image'); ?></h3>
				<h4><?php esc_html_e('AI Image Upscale', 'shortpixel-upscale-image'); ?></h4>
				<ul>
					<?php
					echo wp_kses(
						$scaleOptions,
						array(
							'li'    => array(),
							'input' => array(
								'type'     => true,
								'name'     => true,
								'value'    => true,
								'checked'  => true,
								'disabled' => true,
							),
						)
					);
					?>

			</ul>
		</section>

		<section class='new_file_title wrapper'>
			<span>
					<p><?php esc_html_e('New File Name', 'shortpixel-upscale-image'); ?></p>
				<input type="text" name="new_filename" value="<?php echo esc_attr($suggestedFileName) ?>" data-file-base="<?php echo esc_attr($originalImage->getFileBase()); ?>" data-file-extension="<?php echo esc_attr($originalImage->getExtension()); ?>" data-generated-value="<?php echo esc_attr($suggestedFileName); ?>">
			</span>

			<span>
					<p><?php esc_html_e('New Image Title', 'shortpixel-upscale-image'); ?></p>
				<input type="text" name="new_posttitle" value="<?php echo esc_attr($post_title) ?>">
			</span>

		</section>

		<section class='filler'></section>

    </div> <!-- // action_bar -->
	<div class='button-wrapper'>
		<span>
			<button class='button' type='button' id='media-get-preview' data-action='media-get-preview'>
				<i class="shortpixel-icon eye"></i>
					<?php esc_html_e('Preview','shortpixel-upscale-image'); ?>
			</button>
		</span>
		<span>
			<button class='button button-primary' type='button' id='media-save-button' data-action='media-save-button'>
				<i class="shortpixel-icon save"></i>
					<?php esc_html_e('Save', 'shortpixel-upscale-image'); ?>
			</button>
		
		</span>
			<p><strong><?php esc_html_e('A new image will be created and added to the Media Library!', 'shortpixel-upscale-image'); ?></strong></p>
	</div> <!-- button-wrapper -->
</div> <!-- modal-content-wrapper -->
</div> <!-- // modal -->
