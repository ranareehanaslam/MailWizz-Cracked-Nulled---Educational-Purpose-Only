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
 * @since 2.2.15
 */

/** @var Controller $controller */
$controller = controller();

/** @var CActiveForm $form */
$form = $controller->getData('form');

/** @var CustomerGroupOptionSubaccounts $model */
$model = $controller->getData('model');

?>
<div class="box box-primary borderless">
    <div class="box-body">
        <div class="row">
            <div class="col-lg-4">
                <div class="form-group">
			        <?php echo $form->labelEx($model, 'max_landing_pages'); ?>
			        <?php echo $form->numberField($model, 'max_landing_pages', $model->fieldDecorator->getHtmlOptions('max_landing_pages')); ?>
			        <?php echo $form->error($model, 'max_landing_pages'); ?>
                </div>
            </div>
        </div>
    </div>
</div>