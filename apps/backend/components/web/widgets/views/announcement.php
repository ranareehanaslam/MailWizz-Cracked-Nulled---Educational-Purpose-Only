<?php declare(strict_types=1);
if (!defined('MW_PATH')) {
    exit('No direct script access allowed');
}

/**
 * This file is part of the MailWizz EMA application.
 *
 * @package MailWizz EMA
 * @author MailWizz Development Team <support@mailwizz.com>
 * @link https://www.mailwizz.com/
 * @copyright MailWizz EMA (https://www.mailwizz.com)
 * @license https://www.mailwizz.com/license/
 * @since 2.5.3
 */

/** @var Announcement[] $announcements */
?>

<div class="box box-primary borderless announcements-wrapper">
    <div class="box-header">
        <div class="pull-left">
            <h3 class="box-title">
                <?php echo IconHelper::make('fa-bullhorn') . t('app', 'Announcements'); ?>
            </h3>
        </div>
    </div>
    <div class="box-body">
        <div class="row">
            <?php foreach ($announcements as $announcement) { ?>
                <div class="col-lg-12 announcement-item">
                    <div class="alert alert-info">
                        <button
                                type="button"
                                class="close announcement-read-button"
                                data-url="<?php echo createUrl('announcements/mark_as_read', ['id' => $announcement->announcement_id]); ?>"
                                data-message="<?php echo t('announcements', 'Are you sure? You will never be able to see this message again after closing it.'); ?>"
                        >×</button>
                        <?php echo IconHelper::make('fa-arrow-right'); ?>
                        <strong><?php echo html_purify($announcement->title); ?></strong>
                        <?php echo html_purify($announcement->message); ?>
                    </div>
                </div>
            <?php } ?>
        </div>
    </div>
</div>
