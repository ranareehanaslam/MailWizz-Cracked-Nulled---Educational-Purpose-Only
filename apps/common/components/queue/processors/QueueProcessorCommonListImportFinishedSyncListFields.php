<?php declare(strict_types=1);
if (!defined('MW_PATH')) {
    exit('No direct script access allowed');
}

use Interop\Queue\Context;
use Interop\Queue\Message;
use Interop\Queue\Processor;

/**
 * @package MailWizz EMA
 * @author MailWizz Development Team <support@mailwizz.com>
 * @link https://www.mailwizz.com/
 * @copyright MailWizz EMA (https://www.mailwizz.com)
 * @license https://www.mailwizz.com/license/
 * @since 2.2.2
 */

class QueueProcessorCommonListImportFinishedSyncListFields implements Processor
{
    /**
     * @param Message $message
     * @param Context $context
     *
     * @return string
     */
    public function process(Message $message, Context $context)
    {
        // do not retry this message
        if ($message->isRedelivered()) {
            return self::ACK;
        }

        // another request has been queued for this list
        // which means we don't need to process this one anymore
        if ((string)cache()->get((string)$message->getProperty('request_key')) !== (string)$message->getProperty('request_value')) {
            return self::ACK;
        }

        $retryDelay         = (int)$message->getProperty('retry.attempts.delay', 3600 * 1000);
        $maxAttempts        = (int)$message->getProperty('retry.attempts.max', 5);
        $currentAttempts    = (int)$message->getProperty('retry.attempts.current', 1);

        if ($currentAttempts > $maxAttempts) {
            return self::REJECT;
        }

        /** @var Lists|null $list */
        $list = Lists::model()->findByAttributes([
            'list_id'   => (int)$message->getProperty('list_id'),
            'status'    => Lists::STATUS_ACTIVE,
        ]);

        if (empty($list)) {
            return self::ACK;
        }

        // do not allow processing same list multiple times at same time.
        // but at the same time, don't lose this message, just re-queue it after x minutes
        $mutexKey = sha1(sprintf('%s:list:%d:access', __METHOD__, $list->list_id));
        if (!mutex()->acquire($mutexKey)) {

            // acknowledge the original message, but create a copy of it, and re-queue it,
            // this time with a custom delay
            try {
                $requeueMessage = clone $message;
                $requeueMessage->setProperty('retry.attempts.delay', $currentAttempts * $retryDelay);
                $requeueMessage->setProperty('retry.attempts.max', $maxAttempts);
                $requeueMessage->setProperty('retry.attempts.current', $currentAttempts + 1);

                $producer = $context->createProducer();
                $producer->setDeliveryDelay($retryDelay);
                $producer->send($context->createQueue((string)$message->getProperty('queue_name')), $requeueMessage);
            } catch (Throwable $e) {
                Yii::log($e->getMessage(), CLogger::LEVEL_ERROR);
            }

            return self::ACK;
        }

        try {

            /** @var OptionUrl $optionUrl */
            $optionUrl = container()->get(OptionUrl::class);

            $message = new CustomerMessage();
            $message->customer_id = $list->customer_id;
            $message->title = 'Sync list custom fields';
            $message->message = 'Started the sync custom fields process for the "{list}" list';
            $message->message_translation_params = [
                '{list}' => CHtml::link($list->name, $optionUrl->getCustomerUrl('lists/' . $list->list_uid . '/overview')),
            ];
            $message->save();

            $message = new CustomerMessage();
            $message->customer_id = $list->customer_id;
            $message->title = 'Sync list custom fields';
            $message->message = 'Completed the sync custom fields process for the "{list}" list';
            $message->message_translation_params = [
                '{list}' => CHtml::link($list->name, $optionUrl->getCustomerUrl('lists/' . $list->list_uid . '/overview')),
            ];

            try {
                (new SyncListCustomFieldsRunner())
                    ->setListId((int)$list->list_id)
                    ->run();
            } catch (Exception $e) {
                Yii::log($e->getMessage(), CLogger::LEVEL_ERROR);

                $message->message = 'Error while running the sync process for the "{list}" list custom fields';
            }

            $message->save();
        } catch (Exception $e) {
            Yii::log($e->getMessage(), CLogger::LEVEL_ERROR);
        }

        mutex()->release($mutexKey);

        return self::ACK;
    }
}
