<?php declare(strict_types=1);
if (!defined('MW_PATH')) {
    exit('No direct script access allowed');
}

/**
 * AnnouncementsController
 *
 * Handles the actions for announcements related tasks
 *
 * @package MailWizz EMA
 * @author MailWizz Development Team <support@mailwizz.com>
 * @link https://www.mailwizz.com/
 * @copyright MailWizz EMA (https://www.mailwizz.com)
 * @license https://www.mailwizz.com/license/
 * @since 2.5.3
 */
class AnnouncementsController extends Controller
{
    /**
     * @return array
     */
    public function filters()
    {
        $filters = [
            'postOnly + mark_as_read',
        ];

        return CMap::mergeArray($filters, parent::filters());
    }

    /**
     * Mark an announcement as read for a certain user
     *
     * @return void
     */
    public function actionMark_as_read($id)
    {
        /** @var Announcement|null $announcement */
        $announcement = Announcement::model()->findByPk((int)$id);
        if (empty($announcement)) {
            return;
        }

        $announcement->markAsReadForUser((int)user()->getId());
    }
}
