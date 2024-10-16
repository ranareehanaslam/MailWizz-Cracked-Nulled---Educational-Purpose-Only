<?php declare(strict_types=1);
defined('MW_PATH') or exit('No direct script access allowed');

/**
 * This file is part of the MailWizz EMA application.
 *
 * @package MailWizz EMA
 * @author MailWizz Development Team <support@mailwizz.com>
 * @link https://www.mailwizz.com/
 * @copyright MailWizz EMA (https://www.mailwizz.com)
 * @license https://www.mailwizz.com/license/
 */

/** @var ExtensionController $controller */
$controller = controller();

/** @var string $pageHeading */
$pageHeading = (string)$controller->getData('pageHeading');

/** @var EmailTemplateBuilderExtCommon $model */
$model = $controller->getData('model');

/** @var EmailTemplateBuilderExtStripoCommon $stripoSettings */
$stripoSettings = $controller->getData('stripoSettings');

/** @var CActiveForm $form */
$form = $controller->beginWidget('CActiveForm');

?>
<div class="box box-primary borderless">
    <div class="box-header">
        <div class="pull-left">
            <h3 class="box-title">
                <?php echo IconHelper::make('glyphicon-transfer') . $pageHeading; ?>
            </h3>
        </div>
        <div class="pull-right">
            <?php echo CHtml::link(IconHelper::make('info'), '#page-info-stripo', ['class' => 'btn btn-primary btn-flat email-template-builder-info-box stripo-info-box', 'title' => t('app', 'Info'), 'data-toggle' => 'modal']); ?>
        </div>
        <div class="clearfix"><!-- --></div>
    </div>
    <div class="box-body">
         <div class="row">
             <div class="col-lg-6">
                 <div class="form-group">
                     <?php echo $form->labelEx($model, 'current_builder'); ?>
                     <?php echo $form->dropDownList($model, 'current_builder', $model->getAvailableBuildersDropDown(), $model->fieldDecorator->getHtmlOptions('current_builder')); ?>
                     <?php echo $form->error($model, 'current_builder'); ?>
                 </div>
             </div>
         </div>
        <div class="stripo-settings-container builder-settings-container" style="display: none">
            <br />
            <h5><?php echo $controller->t('Stripo settings'); ?></h5>
            <div class="row">
                <div class="col-lg-6">
                    <div class="form-group">
                        <?php echo $form->labelEx($stripoSettings, 'plugin_id'); ?>
                        <?php echo $form->textField($stripoSettings, 'plugin_id', $stripoSettings->fieldDecorator->getHtmlOptions('plugin_id')); ?>
                        <?php echo $form->error($stripoSettings, 'plugin_id'); ?>
                    </div>
                </div>
                <div class="col-lg-6">
                    <div class="form-group">
                        <?php echo $form->labelEx($stripoSettings, 'secret_key'); ?>
                        <?php echo $form->textField($stripoSettings, 'secret_key', $stripoSettings->fieldDecorator->getHtmlOptions('secret_key')); ?>
                        <?php echo $form->error($stripoSettings, 'secret_key'); ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="box-footer">
        <div class="pull-right">
            <button type="submit" class="btn btn-primary btn-submit"><?php echo IconHelper::make('save') . t('app', 'Save changes'); ?></button>
        </div>
        <div class="clearfix"><!-- --></div>
    </div>
</div>
<?php $controller->endWidget(); ?>

<!-- modals -->
<div class="modal modal-info fade" id="page-info-stripo" tabindex="-1" role="dialog">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                <h4 class="modal-title"><?php echo IconHelper::make('info') . t('app', 'Info'); ?></h4>
            </div>
            <div class="modal-body">
                <?php echo t('extensions', '{here} is where you can find your Plugin ID and Secret Key for your Stripo integration.', [
                        '{here}' => CHtml::link(t('extensions', 'Here'), 'https://stripo.email/plugin-api/#:~:text=Key%20from%20the-,Stripo,-account%20in%20the', ['target' => '_blank']),
                ]); ?><br />
            </div>
        </div>
    </div>
</div>

<script type="text/javascript">
    jQuery(document).ready(function($){
        $('#EmailTemplateBuilderExtCommon_current_builder').on('change', function() {
            const $this = $(this);
            const selectedBuilder = $this.val();
            $('.builder-settings-container').hide();
            $('.email-template-builder-info-box').hide();
            $(`.${selectedBuilder}-settings-container`).show();
            $(`.${selectedBuilder}-info-box`).show();
        }).trigger('change');
    });
</script>
