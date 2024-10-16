<?php declare(strict_types=1);
if (!defined('MW_PATH')) {
    exit('No direct script access allowed');
}

use Interop\Queue\Context;
use Interop\Queue\Message;
use Interop\Queue\Processor;
use League\Csv\CannotInsertRecord;
use League\Flysystem\FileExistsException;

/**
 * @package MailWizz EMA
 * @author MailWizz Development Team <support@mailwizz.com>
 * @link https://www.mailwizz.com/
 * @copyright MailWizz EMA (https://www.mailwizz.com)
 * @license https://www.mailwizz.com/license/
 * @since 2.3.7
 */

class QueueProcessorBackendCampaignsBounceLogsExport implements Processor
{
    /**
     * @param Message $message
     * @param Context $context
     *
     * @return string
     * @throws CException
     * @throws CannotInsertRecord
     * @throws FileExistsException
     */
    public function process(Message $message, Context $context)
    {
        // do not retry this message
        if ($message->isRedelivered()) {
            return self::ACK;
        }

        $user = User::model()->findByPk((int)$message->getProperty('user_id'));
        if (empty($user)) {
            return self::ACK;
        }

        $storage = (string)Yii::getPathOfAlias('common.runtime.campaigns-bounce-logs-export');
        if ((!file_exists($storage) || !is_dir($storage)) && !mkdir($storage)) {
            throw new Exception(sprintf('Please make sure the folder "%s" exists and is writable!', $storage));
        }
        $fileName = StringHelper::random(40) . '.csv';
        $file = $storage . '/' . $fileName;
        $csvWriter = League\Csv\Writer::createFromPath($file, 'w');
        $csvWriter->insertOne(['Customer', 'Campaign', 'List', 'List segment', 'Subscriber', 'Message', 'Processed', 'Bounce type', 'Date added']);

        /** @var CampaignBounceLog $log */
        foreach ($this->getLogs() as $log) {
            try {
                $csvWriter->insertOne([
                    (string)$log->campaign->customer->getFullName(),
                    (string)$log->campaign->name,
                    (string)$log->campaign->list->name,
                    (string)!empty($log->campaign) && !empty($log->campaign->segment_id) ? $log->campaign->segment->name : '-',
                    (string)empty($log->subscriber) ? '-' : $log->subscriber->getDisplayEmail(),
                    (string)$log->message,
                    t('app', ucfirst($log->processed)),
                    t('app', ucfirst($log->bounce_type)),
                    (string)$log->dateAdded,
                ]);
            } catch (Exception $e) {
                Yii::log($e->getMessage(), CLogger::LEVEL_ERROR);
            }
        }

        if (!($fileHandle = fopen($file, 'r'))) {
            throw new CException(sprintf('Unable to open the "%s" file for processing!', $file));
        }

        queue()->getStorage()->getFilesystem()->writeStream($fileName, $fileHandle);
        if (FileSystemHelper::isStreamResource($fileHandle)) {
            fclose($fileHandle);
        }
        unlink($file);

        /** @var OptionUrl $optionUrl */
        $optionUrl = container()->get(OptionUrl::class);

        $message = new UserMessage();
        $message->user_id     = $user->user_id;
        $message->title       = 'Campaigns bounce logs export done';
        $message->message     = 'Your requested export is done, you can click {url} to download it! The download link is valid for 24 hours only!';
        $message->message_translation_params = [
            '{url}' => CHtml::link(t('app', 'here'), $optionUrl->getBackendUrl('download-queued/' . $fileName)),
        ];
        $message->save();

        $delay = (60 * 60 * 24);
        queue_send('backend.campaigns.bouncelogs.export.delete', ['fileName' => $fileName], [], $delay * 1000);

        return self::ACK;
    }

    /**
     * @return Generator
     */
    public function getLogs(): Generator
    {
        $criteria = new CDbCriteria();
        $criteria->limit  = 100;
        $criteria->offset = 0;

        while (true) {
            $models = CampaignBounceLog::model()->findAll($criteria);
            if (empty($models)) {
                break;
            }

            foreach ($models as $model) {
                yield $model;
            }

            $criteria->offset = (int)$criteria->offset + (int)$criteria->limit;
        }
    }
}
