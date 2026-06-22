<?php
namespace SPUI;
use SPUI\Notices\NoticeController as NoticeController;
use SPUI\Controller\StatsController as StatsController;
use SPUI\Controller\QueueController as QueueController;
use SPUI\Controller\AdminNoticesController as AdminNoticesController;
use SPUI\ShortPixelLogger\ShortPixelLogger as Log;


if ( ! defined( 'ABSPATH' ) ) {
 exit; // Exit if accessed directly.
}

$spui_opt = new QueueController();

$spui_q = $spui_opt->getQueue('media');

$spui_env = \wpSPUI()->env();
$spui_fs = \wpSPUI()->filesystem();

$spui_debug_url = add_query_arg(array('part' => 'debug', 'noheader' => true), $this->url);

if (Log::isManualDebug())
{
  // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Debug flag reflected in URL only.
  $spui_debug_value = isset( $_GET['SPUI_DEBUG'] ) ? sanitize_text_field( wp_unslash( $_GET['SPUI_DEBUG'] ) ) : '';
  $spui_debug_url = add_query_arg(['SPUI_DEBUG' => $spui_debug_value], $spui_debug_url);
}
?>

<section id="tab-debug" class="<?php echo esc_attr(($this->display_part == 'debug') ? 'active setting-tab' :'setting-tab'); ?>" data-part="debug">
  <h2><a class='tab-link' href='javascript:void(0);' data-id="tab-debug">
    <?php esc_html_e('Debug','shortpixel-upscale-image');?></a>
  </h2>

  <div class='env'>
    <h3><?php esc_html_e('Environment', 'shortpixel-upscale-image'); ?></h3>
    <div class='flex'>
      <span>NGINX</span><span><?php echo esc_html( wp_json_encode( $this->is_nginx ) ); ?></span>
      <span>KeyVerified</span><span><?php echo esc_html( wp_json_encode( $view->key->is_verifiedkey ) ); ?></span>
      <span>HtAccess writable</span><span><?php echo esc_html( wp_json_encode( $this->is_htaccess_writable ) ); ?></span>
      <span>Multisite</span><span><?php echo esc_html( wp_json_encode( $this->is_multisite ) ); ?></span>
      <span>Main site</span><span><?php echo esc_html( wp_json_encode( $this->is_mainsite ) ); ?></span>
      <span>Constant key</span><span><?php echo esc_html( wp_json_encode( $view->key->is_constant_key ) ); ?></span>
      <span>Hide Key</span><span><?php echo esc_html( wp_json_encode( $view->key->hide_api_key ) ); ?></span>
      <span>Has Nextgen</span><span><?php echo esc_html( wp_json_encode( $this->has_nextgen ) ); ?></span>
			<span>Has Offload</span><span><?php
        $spui_offload = \wpSPUI()->env()->hasOffload();
        echo esc_html( wp_json_encode( $spui_offload ) );
        if (true === $spui_offload)
        {
            echo ' (' . esc_html( \wpSPUI()->env()->getOffloadName() ) . ') ';
        }


       ?></span>

    </div>
		<div class='flex'>
			<span>GD Installed</span><span><?php echo esc_html( wp_json_encode( $spui_env->is_gd_installed ) ); ?></span>
      <span>Imagick Installed</span><span><?php echo esc_html( wp_json_encode( $spui_env->is_imagick_installed ) ); ?></span>
			<span>Curl Installed</span><span><?php echo esc_html( wp_json_encode( $spui_env->is_curl_installed ) ); ?></span>
		</div>

		<div class='flex'>
				<span>Uploads Base</span><span><?php echo esc_html((defined('SPUI_UPLOADS_BASE')) ? SPUI_UPLOADS_BASE : 'not defined'); ?></span>
				<span>Uploads Name</span><span><?php echo esc_html((defined('SPUI_UPLOADS_NAME')) ? SPUI_UPLOADS_NAME : 'not defined'); ?></span>
				<span>Backup Folder</span><span><?php echo esc_html((defined('SPUI_BACKUP_FOLDER')) ? SPUI_BACKUP_FOLDER : 'not defined'); ?></span>
			

        <span>


		</div>
  </div> <!-- /env -->

  <div class='fs'>
    <h3><?php esc_html_e('FileSystem', 'shortpixel-upscale-image'); ?></h3>
    <div class='flex'>
       <span>WpFileBase</span><span><?php echo esc_html( wp_json_encode( $spui_fs->getWPFileBase() ) ); ?></span>
       <span>Upload Base</span><span><?php echo esc_html( wp_json_encode( $spui_fs->getWPUploadBase() ) ); ?></span>
       <span>WPAbspath</span><span><?php echo esc_html( wp_json_encode( $spui_fs->getWPAbsPath() ) ); ?></span>

    </div>

  </div>

  <div class='settings'>
    <h3><?php esc_html_e('Settings', 'shortpixel-upscale-image'); ?></h3>
    <?php $spui_local = $this->view->key;

      $spui_local->apiKey = strlen($spui_local->apiKey) . ' chars'; ?>
       <h4>ApiKeySettings</h4>
    <pre><?php echo esc_html( wp_json_encode( $spui_local, JSON_PRETTY_PRINT ) ); ?></pre>

    <h4>ApiKeyModel</h4>
 <pre><?php echo esc_html( wp_json_encode( $this->keyModel->getData(), JSON_PRETTY_PRINT ) ); ?></pre>


    <?php $spui_settings = (array) $this->view->data;
     ksort($spui_settings);
    ?>
    <h4>Settings</h4>
    <pre><?php echo esc_html( wp_json_encode( $spui_settings, JSON_PRETTY_PRINT ) ); ?></pre>

  	<form method="POST" action="<?php echo esc_url(add_query_arg(['sp-action' => 'action_debug_editSetting'],$spui_debug_url)) ?>">

      <?php wp_nonce_field($this->form_action, 'sp-nonce'); ?>

      <select name="edit_setting">
          <option value="">&nbsp;</option>
      <?php foreach($spui_settings as $spui_name => $spui_value): ?>
	        <option value="<?php echo esc_attr( $spui_name ); ?>"><?php echo esc_html( $spui_name ); ?></option>
      <?php endforeach; ?>
    </select>
      New Value <input name="new_value" value="">

    <button class='button' type='submit' name="Submit" value="update">Update</button>
    <button class='button' type='submit' name="Submit" value="remove">Remove</button>
</form>
  </div>


  <div class='quotadata'>
    <h3><?php esc_html_e('Quota Data', 'shortpixel-upscale-image'); ?></h3>
    <pre><?php echo esc_html( wp_json_encode( $this->quotaData, JSON_PRETTY_PRINT ) ); ?></pre>
  </div>


  <div class='debug-quota'>
    <form method="POST" action="<?php echo esc_url(add_query_arg(array('sp-action' => 'action_debug_resetquota'), $spui_debug_url)) ?>">
			<?php wp_nonce_field($this->form_action, 'sp-nonce'); ?>
      <button class='button' type='submit'>Clear Quota Data</button>
      </form>
  </div>
  <div class="stats env">
      <h3><?php esc_html_e('Stats', 'shortpixel-upscale-image'); ?></h3>
      <h4>Media</h4>
      <div class='flex'>
        <?php $spui_stats_control = StatsController::getInstance();
        ?>
        <span>Items</span><span><?php echo esc_html($spui_stats_control->find('media', 'items')); ?></span>
        <span>Thumbs</span><span><?php echo esc_html($spui_stats_control->find('media', 'thumbs')); ?></span>
        <span>Images</span><span><?php echo esc_html($spui_stats_control->find('media', 'images')); ?></span>
        <span>ItemsTotal</span><span><?php echo esc_html($spui_stats_control->find('media', 'itemsTotal')); ?></span>
        <span>ThumbsTotal</span><span><?php echo esc_html($spui_stats_control->find('media', 'thumbsTotal')); ?></span>

     </div>
     <h4>Custom</h4>
     <div class='flex'>
       <span>Custom Upscaled</span><span><?php echo esc_html($spui_stats_control->find('custom', 'items')); ?></span>
       <span>Custom itemsTotal</span><span><?php echo esc_html($spui_stats_control->find('custom', 'itemsTotal')); ?>
       </span>
     </div>
     <h4>Total</h4>
     <div class='flex'>
        <span>Items</span><span><?php echo esc_html($spui_stats_control->find('total', 'items')); ?></span>
        <span>Images</span><span><?php echo esc_html($spui_stats_control->find('total', 'images')); ?></span>
        <span>Thumbs</span><span><?php echo esc_html($spui_stats_control->find('total', 'thumbs')); ?></span>
     </div>
     <h4>Period</h4>
     <div class='flex'>
        <span>Month #1 </span><span><?php echo esc_html($spui_stats_control->find('period', 'months', '1')); ?></span>
        <span>Month #2 </span><span><?php echo esc_html($spui_stats_control->find('period', 'months', '2')); ?></span>
        <span>Month #3 </span><span><?php echo esc_html($spui_stats_control->find('period', 'months', '3')); ?></span>
        <span>Month #4 </span><span><?php echo esc_html($spui_stats_control->find('period', 'months', '4')); ?></span>
  	</div>
	</div> <!-- stats -->

  <div class='debug-stats'>
    <form method="POST" action="<?php echo esc_url(add_query_arg(array('sp-action' => 'action_debug_resetStats'), $spui_debug_url)) ?>"
      >
			<?php wp_nonce_field($this->form_action, 'sp-nonce'); ?>
      <button class='button' type='submit'>Clear statistics cache</button>
      </form>
  </div>

  <?php $spui_notice_controller =  NoticeController::getInstance();
    $spui_notices = $spui_notice_controller->getNotices();
  ?>

  <h3>Notices (<?php echo esc_html(count($spui_notices)); ?>)</h3>
  <div class='table notices'>

    <div class='head'>
      <span>ID</span><span>Done</span><span>Dismissed</span><span>Persistent</span><span>Exclude</span><span>Include</span>
    </div>

  <?php foreach ($spui_notices as $spui_notice_obj):
			$spui_exclude = $spui_notice_obj->_debug_getvar('exclude_screens');
			$spui_include = $spui_notice_obj->_debug_getvar('include_screens');

			$spui_exclude = is_array($spui_exclude) ? implode(',', $spui_exclude) : $spui_exclude;
			$spui_include = is_array($spui_include) ? implode(',', $spui_include) : $spui_include;

	?>

  <div>
      <span><?php echo esc_html($spui_notice_obj->getID()); ?></span>
      <span><?php echo $spui_notice_obj->isDone() ? 'Y' : 'N'; ?> </span>
      <span><?php echo $spui_notice_obj->isDismissed() ? 'Y' : 'N'; ?> </span>
      <span><?php echo $spui_notice_obj->isPersistent() ? 'Y' : 'N'; ?> </span>
			<span><?php echo esc_html( $spui_exclude ); ?></span>
			<span><?php echo esc_html( $spui_include ); ?></span>

  </div>


  <?php endforeach ?>
  </div>

  <div class='debug-notices'>
    <form method="POST" action="<?php echo esc_url(add_query_arg(array('sp-action' => 'action_debug_resetNotices'), $spui_debug_url)) ?>"
      >
			<?php wp_nonce_field($this->form_action, 'sp-nonce'); ?>
      <button class='button' type='submit'>Reset Notices</button>
      </form>
  </div>

	<div class='trigger-notices'>
		<form method="POST" action="<?php echo esc_url(add_query_arg(array('sp-action' => 'action_debug_triggerNotice'), $spui_debug_url)) ?>"
      >
			<?php wp_nonce_field($this->form_action, 'sp-nonce'); ?>
			<?php
				$spui_controller = AdminNoticesController::getInstance();
				$spui_notices = $spui_controller->getAllNotices();

		 ?>
				<select name="notice_constant">
					 <option value="trigger-all">Trigger All</option>

					<?php
          if (is_array($spui_notices))
          {
            foreach($spui_notices as $spui_key => $spui_notice_obj)
							 echo '<option value="' . esc_attr( $spui_key ) . '">' . esc_html( $spui_key ) . '</option>';
          }
						?>
				</select>
				<button class="button" type="submit">Trigger this Notice</button>

		</form>
	</div>

  <p>&nbsp;</p>

	<div class='table queue-stats'>
		<?php
      $spui_opt = new QueueController();

		 	$spui_stats_media = $spui_opt->getQueue('media');
			$spui_stats_custom = $spui_opt->getQueue('custom');

      $spui_opt = new QueueController(['is_bulk' => true]);

		 	$spui_bulk_media = $spui_opt->getQueue('media');
			$spui_bulk_custom = $spui_opt->getQueue('custom');


			$spui_queues = array('media' => $spui_stats_media, 'custom' => $spui_stats_custom, 'mediaBulk' => $spui_bulk_media, 'customBulk' => $spui_bulk_custom);

			?>
			  <div class='head'>
					<span>Name</span>
					<span>In Queue</span>
					<span>In process</span>
					<span>Errors</span>
					<span>Fatal</span>
					<span>Done</span>
					<span>Total</span>
          <span>IsCustomOp</span>
				</div>
			<?php

			foreach($spui_queues as $spui_name => $spui_queue):
					$spui_stats = $spui_queue->getStats();
          $spui_options = $spui_queue->getOptions();

          // Lazy options merger to show in titles. 
          $spui_options_txt = false; 

          if (is_array($spui_options))
          {
              $spui_filters = []; 
              
              if(isset($spui_options['filters']) && is_array($spui_options['filters'])) 
              {
                  $spui_filters = $spui_options['filters'];
                  unset($spui_options['filters']); 
              }
              
              
              $spui_options = array_merge($spui_options, $spui_filters); 
              
              foreach($spui_options as $spui_opt_name => $spui_val)
              {
                $spui_options_txt .= " $spui_opt_name : $spui_val \n"; 
              }
          }

					echo "<div>";
            if (false !== $spui_options_txt)
            {
                echo '<span title="' . esc_attr( $spui_options_txt ) . '" ><u>' .  esc_html($spui_name) . '</u></span>';
            }
            else
            {
                echo "<span >" .  esc_html($spui_name) . '</span>';
            }

						echo "<span>" .  esc_html($spui_stats->in_queue) . '</span>';
						echo "<span>" .  esc_html($spui_stats->in_process) . '</span>';
						echo "<span>" .  esc_html($spui_stats->errors) . '</span>';
						echo "<span>" .  esc_html($spui_stats->fatal_errors) . '</span>';
						echo "<span>" .  esc_html($spui_stats->done) . '</span>';
						echo "<span>" .  esc_html($spui_stats->total) . '</span>';
	            echo '<span>' . esc_html( $spui_queue->getCustomDataItem('customOperation') ) . '</span>';
            
					echo "</div>";

				?>

			<?php endforeach; ?>

  <div class='debug-queue'>
    <form method="POST" action="<?php echo esc_url(add_query_arg(array('sp-action' => 'action_debug_resetQueue'),$spui_debug_url)) ?>"
      id="shortpixel-form-reset-queue">
			<?php wp_nonce_field($this->form_action, 'sp-nonce'); ?>
      <button class='button' type='submit'>Reset ShortQ</button>
			<select name="queue">
					<option>All</option>
					<?php foreach($spui_queues as $spui_name => $spui_queue_item)
					{
						 echo "<option>" . esc_attr($spui_name) . "</option>";
					}
					?>
			</select>
      <label><input type="checkbox" name="use_uninstall">Uninstall</label>
      </form>
  </div>
</div> <!--- stats -->

<p></p>



<div class='debug-key'>
	<form method="POST" action="<?php echo esc_url(add_query_arg(array('sp-action' => 'action_debug_removeProcessorKey'),$spui_debug_url)) ?>"
		>
		<?php wp_nonce_field($this->form_action, 'sp-nonce'); ?>
		<button class='button' type='submit'>Reset Processor Key</button>
		</form>
</div>

</section>
