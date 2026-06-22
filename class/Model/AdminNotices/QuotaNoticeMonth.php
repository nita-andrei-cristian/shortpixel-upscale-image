<?php
namespace SPUI\Model\AdminNotices;

if ( ! defined( 'ABSPATH' ) ) {
 exit; // Exit if accessed directly.
}

use SPUI\Controller\StatsController as StatsController;
use SPUI\Controller\AdminNoticesController as AdminNoticesController;
use SPUI\Controller\QuotaController as QuotaController;

class QuotaNoticeMonth extends \SPUI\Model\AdminNoticeModel
{
	protected $key = 'MSG_UPGRADE_MONTH';

	public function load()
	{
    $bool = parent::load();

  //	 $this->callback = array(AdminNoticesController::getInstance(), 'proposeUpgradePopup');
    if (true === $bool && is_object($this->notice))
    {
       AdminNoticesController::getInstance()->proposeUpgradePopup();
    }

	}

	protected function checkTrigger()
	{
			$quotaController = QuotaController::getInstance();

			if ($quotaController->hasQuota() === false)
				return false;

			$quotaData = $quotaController->getQuota();

			if ($this->monthlyUpgradeNeeded($quotaData) === false)
				return false;

			$this->addData('average', $this->getMonthAverage());
			$this->addData('month_total', $quotaData->monthly->total);
			$this->addData('onetime_remaining', $quotaData->onetime->remaining);

	}

	protected function getMessage()
	{
		$quotaController = QuotaController::getInstance();

		$quotaData = $quotaController->getQuota();
		$average = $this->getMonthAverage(); // $this->getData('average');
		$month_total = $quotaData->monthly->total;// $this->getData('month_total');
		$onetime_remaining = $quotaData->onetime->remaining; //$this->getData('onetime_remaining'); */

		/* translators: 1: Opening strong tag. 2: Average number of images added monthly. 3: Closing strong tag. 4: Monthly plan limit. 5: Remaining one-time credits. 6: Line break tag. */
		$message = '<p>' . sprintf( __( 'You add an average of %1$s %2$d images and thumbnails %3$s to your Media Library every month and you have <strong>a plan of %4$d images/month (and %5$d one-time images)</strong>.%6$s You may need to upgrade your plan to have all your images optimized.', 'shortpixel-upscale-image' ), '<strong>', $average, '</strong>', $month_total, $onetime_remaining, '<br>' ) . '</p>';

		$message .= '  <button class="button button-primary" id="shortpixel-upgrade-advice" onclick="SPUI.proposeUpgrade()" style="margin-right:10px;"><strong>' .  __('Show me the best available options', 'shortpixel-upscale-image') . '</strong></button>';

		return $message;
	}

	protected function getMonthAverage() {
			$stats = StatsController::getInstance();

			// Count how many months have some optimized images.
			for($i = 4, $count = 0; $i>=1; $i--) {
					if($count == 0 && $stats->find('period', 'months', $i) == 0)
					{
						continue;
					}
					$count++;

			}
			// Sum last 4 months, and divide by number of active months to get number of avg per active month.
			return ($stats->find('period', 'months', 1) + $stats->find('period', 'months', 2) + $stats->find('period', 'months', 3) + $stats->find('period', 'months', 4) / max(1,$count));
	}

	protected function monthlyUpgradeNeeded($quotaData)
	{
			if  (isset($quotaData->monthly->total))
			{
					$monthAvg = $this->getMonthAverage($quotaData);
					// +20 I suspect to not trigger on very low values of monthly use(?)
					$threshold = $quotaData->monthly->total + ($quotaData->onetime->remaining / 6 ) +20;

					if ($monthAvg > $threshold)
					{
							return true;
					}
			}
			return false;
	}
} // class
