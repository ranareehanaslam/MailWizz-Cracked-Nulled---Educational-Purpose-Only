<?php declare(strict_types=1);
if (!defined('MW_PATH')) {
    exit('No direct script access allowed');
}

use Carbon\Carbon;

/**
 * CountdownController
 *
 * Handles the actions for generating the countdowns
 *
 * @package MailWizz EMA
 * @author MailWizz Development Team <support@mailwizz.com>
 * @link https://www.mailwizz.com/
 * @copyright MailWizz EMA (https://www.mailwizz.com)
 * @license https://www.mailwizz.com/license/
 * @since 2.1.15
 */

class CountdownController extends Controller
{
    /**
     * Generates the countdown counter
     *
     * @return void
     * @throws Exception
     */
    public function actionIndex()
    {
        $until = (string)request()->getQuery('until', '');

        $destinationDateTime = Carbon::createFromTimestamp((int)strtotime($until));
        if ($destinationDateTime->greaterThan(Carbon::createFromTimestamp((int)strtotime('+14 days')))) {
            $destinationDateTime = Carbon::createFromTimestamp((int)strtotime('+14 days'));
        }

        $showCircle = (string)request()->getQuery('show-circle', 'yes') === 'yes';

        $minFrames = 60;
        $maxFrames = (int)request()->getQuery('max-frames', $minFrames);
        $maxFrames = $maxFrames < $minFrames ? $minFrames : min($maxFrames, 120);

        $emailCountdown = (new Countdown())
            ->setDestinationTime($destinationDateTime)
            ->setTextColor((string)request()->getQuery('text-color', ''))
            ->setBackgroundColor((string)request()->getQuery('background-color', ''))
            ->setMaxFrames($maxFrames)
            ->setShowTextLabel((string)request()->getQuery('show-text-label', 'yes') === 'yes')
            ->setDaysLabel(t('app', 'Days'))
            ->setHoursLabel(t('app', 'Hours'))
            ->setMinutesLabel(t('app', 'Minutes'))
            ->setSecondsLabel(t('app', 'Seconds'))
            ->setShowCircle($showCircle)
            ->setSize((string)request()->getQuery('size', Countdown::MEDIUM_SIZE))
        ;

        if ($showCircle) {
            $emailCountdown
                ->setCircleBackgroundColor((string)request()->getQuery('circle-background-color', ''))
                ->setCircleForegroundColor((string)request()->getQuery('circle-foreground-color', ''));
        }

        $accessKey = sha1((string)json_encode(request()->getQuery('')));
        $imageContent = (string)cache()->get($accessKey);
        if (empty($imageContent) && mutex()->acquire($accessKey)) {
            $imageContent = $emailCountdown->getGIFAnimation();
            cache()->set($accessKey, $imageContent);
            mutex()->release($accessKey);
        }

        header('Content-Type: image/gif');
        header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
        header('Cache-Control: post-check=0, pre-check=0', false);
        header('Pragma: no-cache');

        echo $imageContent;

        app()->end();
    }
}
