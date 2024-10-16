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
 * @since 2.1.20
 */

?>

<div class="box box-primary borderless">
    
    <div class="box-header">
        <div class="pull-left">
            <?php if ($this->headingLeft && is_object($this->headingLeft)) {
    $this->headingLeft->render();
} ?>
        </div>
        <div class="pull-right">
            <?php if ($this->headingRight && is_object($this->headingRight)) {
    $this->headingRight->render();
} ?>
        </div>
        <div class="clearfix"><!-- --></div>
    </div>
    
    <div class="box-body geo-opens-wrapper">
        <div class="row">
            <div class="col-lg-12 text-center">
                <h4><?php echo t('app', 'Currently, there is no data to be shown.'); ?></h4>
            </div>
        </div>
    </div>
</div>