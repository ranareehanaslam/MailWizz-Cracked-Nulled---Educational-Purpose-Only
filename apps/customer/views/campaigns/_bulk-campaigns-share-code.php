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
 * @since 2.2.8
 */

/** @var Controller $controller */
$controller = controller();

$campaignShareCode = new CampaignShareCode();

?>
<div class="modal fade" id="bulk-campaigns-share-code" tabindex="-1" role="dialog" aria-labelledby="bulk-campaigns-share-code-label" aria-hidden="true">
	<div class="modal-dialog">
		<div class="modal-content">
			<div class="modal-header">
				<button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
				<h4 class="modal-title"><?php echo t('campaigns', 'Campaigns share code'); ?></h4>
			</div>
			<div class="modal-body">
                <?php
                /** @var CActiveForm $form */
                $form = $controller->beginWidget('CActiveForm', [
                    'id' => 'bulk-campaigns-share-code-form',
                ]); ?>

                <div class="form-group">
                    <?php echo $form->labelEx($campaignShareCode, 'allowed_usage'); ?>
                    <?php echo $form->textField($campaignShareCode, 'allowed_usage', $campaignShareCode->fieldDecorator->getHtmlOptions('allowed_usage')); ?>
                    <?php echo $form->error($campaignShareCode, 'allowed_usage'); ?>
                </div>
				<?php $controller->endWidget(); ?>
			</div>
			<div class="modal-footer">
				<button type="button" class="btn btn-default btn-flat" data-dismiss="modal"><?php echo t('app', 'Close'); ?></button>
				<button type="button" class="btn btn-primary btn-flat" onclick="$('#bulk-campaigns-share-code-form').submit();"><?php echo IconHelper::make('fa-save') . '&nbsp;' . t('campaigns', 'Share'); ?></button>
			</div>
		</div>
	</div>
</div>
