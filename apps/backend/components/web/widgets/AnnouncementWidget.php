<?php declare(strict_types=1);
if (!defined('MW_PATH')) {
    exit('No direct script access allowed');
}

/**
 * AnnouncementWidget
 *
 * @package MailWizz EMA
 * @author MailWizz Development Team <support@mailwizz.com>
 * @link https://www.mailwizz.com/
 * @copyright MailWizz EMA (https://www.mailwizz.com)
 * @license https://www.mailwizz.com/license/
 * @since 2.5.3
 */

class AnnouncementWidget extends CWidget
{
    /**
     * @return void
     */
    public function init()
    {
        parent::init();
        clientScript()->registerScriptFile(AssetsUrl::js('announcement-widget.js'));
    }

    /**
     * @return void
     * @throws CException
     */
    public function run()
    {
        /** @var User $user */
        $user = user()->getModel();
        if (empty($user)) {
            return;
        }

        // this widget is required everywhere, including the update screen,
        // which will trigger an error if the database table does not exist,
        // and we try to access it.
        // this way we make sure if the database table does not exist, we stop.
        if (!db()->getSchema()->getTable('{{announcement}}')) {
            return;
        }

        $criteria = new CDbCriteria();
        $criteria->addCondition('t.last_updated >= :userCreatedAt');
        $criteria->params[':userCreatedAt'] = $user->date_added;
        $criteria->order = 't.last_updated DESC';

        $criteria->with['usersRead'] = [
            'select'    => false,
            'together'  => true,
            'joinType'  => 'LEFT OUTER JOIN',
            'on'        => 'usersRead.user_id = :uid',
            'condition' => 'usersRead.user_id IS NULL',
            'params'    => [':uid' => $user->user_id],
        ];

        $announcements = Announcement::model()->findAll($criteria);

        if (empty($announcements)) {
            return;
        }

        $this->render('announcement', [
            'announcements' => $announcements,
        ]);
    }
}
