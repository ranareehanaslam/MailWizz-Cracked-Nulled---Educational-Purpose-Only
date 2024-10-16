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
 * @since 2.5.1
 */

?>

<div class="alert alert-info">
    <?php echo t('campaigns', 'Please note you have enabled Smart Tracking. If you trigger one of the rules for opens and/or clicks, you may end up in the ignore list and your clicks and/or opens will not be tracked.'); ?>
    <br />
    <?php echo t('campaigns', 'In order to avoid this, please wait at least {n} seconds from the moment your campaign has been sent, before you open and/or click a campaign.', [
            '{n}' => 30,
    ]); ?>
    <br />
    <?php echo t('campaigns', 'Please also avoid doing repetitive opens and/or clicks in short periods of time.'); ?>
</div>