<?php
namespace SPUI\Model\AdminNotices;

if ( ! defined( 'ABSPATH' ) ) {
 exit; // Exit if accessed directly.
}

use SPUI\Controller\StatsController as StatsController;
use SPUI\Controller\ApiKeyController as ApiKeyController;
use SPUI\Controller\AdminNoticesController as AdminNoticesController;
use SPUI\Controller\QuotaController as QuotaController;


class QuotaNoticeReached extends \SPUI\Model\AdminNoticeModel
{
	protected $key = 'MSG_QUOTA_REACHED';
	protected $errorLevel = 'error';


	public function load()
	{
    // $this->callback = array(AdminNoticesController::getInstance(), 'proposeUpgradePopup');
     $bool = parent::load();
     if (true === $bool && is_object($this->notice))
     {
        AdminNoticesController::getInstance()->proposeUpgradePopup();
     }
	}

	protected function checkTrigger()
	{
			$quotaController = QuotaController::getInstance();

			if ($quotaController->hasQuota() === true)
				return false;

		  $this->reset('MSG_UPGRADE_MONTH');
			$this->reset('MSG_UPGRADE_BULK');
	    return true;

	}

	protected function getMessage()
	{
		$statsControl = StatsController::getInstance();
		$averageCompression = $statsControl->getAverageCompression();
		$quotaController = QuotaController::getInstance();

		$keyControl = ApiKeyController::getInstance();

		//$keyModel->loadKey();

		$login_url = 'https://shortpixel.com/login/';
		$friend_url = $login_url;

		if ($keyControl->getKeyForDisplay())
		{
			$login_url .= $keyControl->getKeyForDisplay() . '/spui-unlimited';
			$friend_url = $login_url . 'tell-a-friend';
		}

	 $message = '<div class="sp-quota-exceeded-alert"  id="short-pixel-notice-exceed">';

	 if($averageCompression) {

				$message .= '<div style="float:right;">
						<div class="bulk-progress-indicator" style="height: 110px">
								<div style="margin-bottom:5px">' . __('Average image<br>reduction until now:','shortpixel-upscale-image') . '</div>
								<div id="sp-avg-optimization"><input type="text" id="sp-avg-optimization-dial" value="' . round($averageCompression) . '" class="dial percentDial" data-dialsize="60"></div>
								<script>
										jQuery(function() {
												if (ShortPixel)
												{
													SPUI.percentDial("#sp-avg-optimization-dial", 60);
												}
										});
								</script>
						</div>
				</div>';

		}

			$message .= '<h3>' . __('Quota Exceeded','shortpixel-upscale-image') . '</h3>';

			$quota = $quotaController->getQuota();

			$creditsUsed = number_format($quota->monthly->consumed + $quota->onetime->consumed);
			$totalUpscaled = $statsControl->find('total', 'images');
			$totalImagesToUpscale = number_format($statsControl->totalImagesToUpscale());

			$message .= '<p>' . sprintf(__('The plugin has upscaled <strong>%s images</strong> and has been stopped because it has reached the available quota limit.','shortpixel-upscale-image'),
						$creditsUsed);

			if($totalImagesToUpscale > 0) {

						$message .= sprintf(__('<strong> %s images and thumbnails</strong> have not been upscaled by ShortPixel yet.','shortpixel-upscale-image'), $totalImagesToUpscale  );
				}

			 $message .= sprintf('</p>
					<div>
						<button class="button button-primary" type="button" id="shortpixel-upgrade-advice" onclick="SPUI.proposeUpgrade()" style="margin-right:10px;"><strong>' .  __('Show me the best available options', 'shortpixel-upscale-image') . '</strong></button>
						<a class="button button-primary" href="%s"
							 title="' . __('Go to My Account and choose a plan','shortpixel-upscale-image') . '" target="_blank" style="margin-right:10px;">
								<strong>' . __('Upgrade','shortpixel-upscale-image') . '</strong>
						</a>
						<button type="button" name="checkQuota" class="button" onclick="SPUI.checkQuota()">'.  __('Confirm new credits','shortpixel-upscale-image') . '</button>
				</div>', $login_url);

			$message .= '</div>'; /// closing div
			return $message;
	}

}
