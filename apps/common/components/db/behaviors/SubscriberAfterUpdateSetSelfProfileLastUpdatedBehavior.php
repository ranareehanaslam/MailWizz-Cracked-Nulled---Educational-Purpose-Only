<?php declare(strict_types=1);
if (!defined('MW_PATH')) {
    exit('No direct script access allowed');
}

/**
 * SubscriberAfterUpdateSetSelfProfileLastUpdatedBehavior
 *
 * @package MailWizz EMA
 * @author MailWizz Development Team <support@mailwizz.com>
 * @link https://www.mailwizz.com/
 * @copyright MailWizz EMA (https://www.mailwizz.com)
 * @license https://www.mailwizz.com/license/
 * @since 2.2.3
 *
 */

/**
 * @property ListSubscriber $owner
 */
class SubscriberAfterUpdateSetSelfProfileLastUpdatedBehavior extends CActiveRecordBehavior
{
    /**
     * @param CEvent $event
     *
     * @return void
     */
    public function afterSave($event)
    {
        parent::afterSave($event);

        $this->owner->setCustomMetaData(ListSubscriber::META_DATA_PROFILE_LAST_UPDATED_KEY_NAME, MW_DATETIME_NOW);
    }
}
