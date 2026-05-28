<?php
namespace ShortPixel;
use ShortPixel\Notices\NoticeController as Notice;
use ShortPixel\ShortPixelLogger\ShortPixelLogger as Log;
use ShortPixel\Helper\UiHelper as UiHelper;

if ( ! defined( 'ABSPATH' ) ) {
 exit; // Exit if accessed directly.
}

// Notices for fringe cases
if (! $view->key->is_verifiedkey && $view->key->hide_api_key && ! $view->key->is_constant_key)
{

	$error_message = __('wp-config.php is hiding the API key, but no API key was found. Remove the constant, or define the SHORTPIXEL_API_KEY constant as well', 'shortpixel-upscale-image');
	Notice::addError($error_message);
}
elseif ($view->key->is_constant_key && ! $view->key->is_verifiedkey)
{
  $dkey = ($view->key->hide_api_key) ? '' : '(' . SHORTPIXEL_API_KEY.  ')';
	$error_message = sprintf(__('Constant API Key is not verified. Please check if this is a valid API key %s'),$dkey);
  Notice::addError($error_message);
}

$adminEmail = get_bloginfo('admin_email');


// When key is not editable, basically all fields should be off.
$spui_is_disabled = ! $view->key->is_editable;

?>
<section id="tab-nokey" class="<?php echo ($this->display_part == 'nokey') ? 'active setting-tab' :'setting-tab'; ?>" data-part="nokey" >

  <h1><?php _e('Welcome Onboard!', 'shortpixel-upscale-image'); ?></h1>
  <div class='onboarding-logo'>
        <?php echo UIHelper::getIcon('res/images/illustration/onboarding.svg'); ?>
  </div>

    <progressbar>

    </progressbar>

    <!--  <h2><a class='tab-link' href='javascript:void(0);' data-id="tab-settings">
      <?php esc_html_e('Join ShortPixel','shortpixel-upscale-image');?></a>
    </h2> -->


		<div class='onboarding-join-wrapper'>

	 <!-- // @todo Remove Inline CSS on whole page-->


<settinglist class='new-customer now-active' tabindex="0" role="button" aria-pressed="true">

	<h3><?php esc_html_e('New user?','shortpixel-upscale-image');?></h3>
	<?php echo UiHelper::getIcon('res/images/icon/new-user.svg'); ?>
	<h2><?php esc_html_e('Create account','shortpixel-upscale-image');?></h2>
	<p><?php esc_html_e('If you don\'t have an API Key, you can request one for free. Just press the "Request Key" button after checking that the e-mail is correct.','shortpixel-upscale-image');?></p>

  <div id="shortpixel-form-request-key">

  <setting>
      <content>
      <name for="pluginemail"><?php esc_html_e('E-mail address:','shortpixel-upscale-image');?></name>
              <input name="pluginemail" type="text" id="pluginemail" value="<?php echo esc_attr( sanitize_email($adminEmail) );?>" class="regular-text" <?php disabled( $spui_is_disabled ); ?> />

              <span class="spinner" id="pluginemail_spinner" style="float:none;"></span>
<!--
              <button type="submit" id="request_key" class="button button-primary" title="<?php esc_html_e('Request a new API key','shortpixel-upscale-image');?>"
                 href="https://shortpixel.com/free-sign-up?pluginemail=<?php echo esc_attr( esc_url($adminEmail) );?>"
								 <?php disabled( $spui_is_disabled ); ?>  >
                 <?php esc_html_e('Request Key','shortpixel-upscale-image');?>
              </button>
-->
              <info>
                <p class="settings-info shortpixel-settings-error" style='display:none;' id='pluginemail-error'>
                    <b><?php esc_html_e('Please provide a valid e-mail address.', 'shortpixel-upscale-image');?></b>
                </p>
                <p class="settings-info" id='pluginemail-info'>
                    <?php if($adminEmail) {
                        printf(esc_html__('%s %s %s is the e-mail address in your WordPress Settings. You can use it, or change it to any valid e-mail address that you own.','shortpixel-upscale-image'), '<b>', esc_html(sanitize_email($adminEmail)),  '</b>');
                    } else {
                        esc_html_e('Please input your e-mail address and press the Request Key button.','shortpixel-upscale-image');
                    }
                    ?>
                </p>
                <p>
                    <label for='tos'>
                      <span style="position:relative;">

                        <input name="tos" type="checkbox" id="tos">
                        <img class="tos-robo" alt="<?php esc_html_e('ShortPixel logo', 'shortpixel-upscale-image'); ?>"
                             src="<?php echo esc_url(\wpSPIO()->plugin_url('res/img/slider.png' ));?>" style="position: absolute;left: -95px;bottom: -26px;display:none;">
                        <img class="tos-hand" alt="<?php esc_html_e('Hand pointing', 'shortpixel-upscale-image'); ?>"
                             src="<?php echo esc_url(\wpSPIO()->plugin_url('res/img/point.png' ));?>" style="position: absolute;left: -39px;bottom: -9px;display:none;">

                    </span>
                    <?php printf(esc_html__('I have read and I agree to the %s Terms of Service %s and the %s Privacy Policy %s (%s GDPR compliant %s).','shortpixel-upscale-image'), '<a href="https://shortpixel.com/tos" target="_blank">', '</a>', '<a href="https://shortpixel.com/privacy" target="_blank">', '</a>', '<a href="https://shortpixel.com/privacy#gdpr" target="_blank">', '</a>');
                    ?></label> </p>
              </info>
      </content>
  </setting>

  </div>

</settinglist>

<settinglist class='existing-customer' tabindex="0" role="button" aria-pressed="false">
	<h3>
			<?php esc_html_e('Already have an account?','shortpixel-upscale-image');?>
	</h3>

	<?php echo UiHelper::getIcon('res/images/icon/login.svg'); ?>
	<h2><?php esc_html_e('Login','shortpixel-upscale-image');?></h2>

	<p>
	    <?php esc_html_e('Welcome back! If you already have an API Key please input it below and press Validate.','shortpixel-upscale-image');?>
	</p>

  <setting>
      <content>
      <name>
          <?php esc_html_e('API Key:','shortpixel-upscale-image');?>
      </name>
        <input name="login_apiKey" type="text" id="new-key" value="<?php echo esc_attr( $view->key->apiKey );?>"
           class="regular-text" <?php disabled( $spui_is_disabled ); ?>>

              <input type="hidden" name="validate" id="valid" value="validate"/>
              <span class="spinner" id="pluginemail_spinner" style="float:none;"></span>
            <!--  <button type="submit" id="validate" class="button button-primary" title="<?php esc_html_e('Validate the provided API key','shortpixel-upscale-image');?>" <?php disabled( $spui_is_disabled ); ?>
                  >
                  <?php esc_html_e('Validate','shortpixel-upscale-image');?>
              </button> -->
      </content>

  </setting>

</settinglist>




</div> <!-- // Join Wrapper -->



  <div class='submit-errors'>

  </div>
<settinglist class='onboard-submit'>

  <button type="button" name="add-key"><?php esc_html_e('Continue', 'shortpixel-upscale-image'); ?><span class='dots'>.</span></button>

</settinglist>



</section>
