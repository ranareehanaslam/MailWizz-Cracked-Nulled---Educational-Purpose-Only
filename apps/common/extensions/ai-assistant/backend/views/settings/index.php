<?php declare(strict_types=1);
if (!defined('MW_PATH')) {
    exit('No direct script access allowed');
}

/**
 * @package MailWizz EMA
 * @author MailWizz Development Team <support@mailwizz.com>
 * @link http://www.mailwizz.com/
 * @copyright MailWizz EMA (http://www.mailwizz.com)
 * @license http://www.mailwizz.com/license/
 */

/** @var ExtensionController $controller */
$controller = controller();

/** @var string $pageHeading */
$pageHeading = (string)$controller->getData('pageHeading');

/** @var AiAssistantExtCommon $model */
$model = $controller->getData('model');

/**
 * This hook gives a chance to prepend content or to replace the default view content with a custom content.
 * Please note that from inside the action callback you can access all the controller view
 * variables via {@CAttributeCollection $collection->controller->data}
 * In case the content is replaced, make sure to set {@CAttributeCollection $collection->renderContent} to false
 * in order to stop rendering the default content.
 * @since 1.3.3.1
 */
hooks()->doAction('before_view_file_content', $viewCollection = new CAttributeCollection([
    'controller'    => $controller,
    'renderContent' => true,
]));

// and render if allowed
if ($viewCollection->itemAt('renderContent')) { ?>
    <div class="box box-primary borderless">
        <div class="box-header">
            <div class="pull-left">
                <h3 class="box-title">
                    <?php echo IconHelper::make('fa-cogs') . $pageHeading; ?>
                </h3>
            </div>
            <div class="pull-right">
            </div>
            <div class="clearfix"><!-- --></div>
        </div>

        <?php
        /**
         * This hook gives a chance to prepend content before the active form or to replace the default active form entirely.
         * Please note that from inside the action callback you can access all the controller view variables
         * via {@CAttributeCollection $collection->controller->data}
         * In case the form is replaced, make sure to set {@CAttributeCollection $collection->renderForm} to false
         * in order to stop rendering the default content.
         * @since 1.3.3.1
         */
        hooks()->doAction('before_active_form', $collection = new CAttributeCollection([
            'controller'    => $controller,
            'renderForm'    => true,
        ]));

        // and render if allowed
        if ($collection->itemAt('renderForm')) {
            /** @var CActiveForm $form */
            $form = $controller->beginWidget('CActiveForm'); ?>
            <div class="box-body">
                <?php
                /**
                 * This hook gives a chance to prepend content before the active form fields.
                 * Please note that from inside the action callback you can access all the controller view variables
                 * via {@CAttributeCollection $collection->controller->data}
                 * @since 1.3.3.1
                 */
                hooks()->doAction('before_active_form_fields', new CAttributeCollection([
                    'controller'    => $controller,
                    'form'          => $form,
                ])); ?>
                <div class="row">
                    <div class="col-lg-3">
                        <div class="form-group">
                            <?php echo $form->labelEx($model, 'enabled'); ?>
                            <?php echo $form->dropDownList($model, 'enabled', $model->getYesNoOptions(), $model->fieldDecorator->getHtmlOptions('enabled')); ?>
                            <?php echo $form->error($model, 'enabled'); ?>
                        </div>
                    </div>
                </div>

                <hr/><h4><?php echo t('ai_assistant_ext', 'Open AI settings'); ?></h4><hr/>

                <div class="row">
                    <div class="col-lg-3">
                        <div class="form-group">
                            <?php echo $form->labelEx($model, 'secret_access_key'); ?>
                            <?php echo $form->textField($model, 'secret_access_key', $model->fieldDecorator->getHtmlOptions('secret_access_key')); ?>
                            <?php echo $form->error($model, 'secret_access_key'); ?>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-lg-3">
                        <div class="form-group">
                            <?php echo $form->labelEx($model, 'open_ai_model'); ?>
                            <?php echo $form->dropDownList($model, 'open_ai_model', $model->getOpenAiModelsList(), $model->fieldDecorator->getHtmlOptions('open_ai_model')); ?>
                            <?php echo $form->error($model, 'open_ai_model'); ?>
                        </div>
                    </div>
                </div>

                <hr /><h4><?php echo $controller->t('Customers'); ?></h4><hr />
                <div class="row">
                    <div class="col-lg-4">
                        <div class="form-group">
                            <?php echo $form->labelEx($model, 'customers_enabled'); ?>
                            <?php echo $form->dropDownList($model, 'customers_enabled', $model->getYesNoOptions(), $model->fieldDecorator->getHtmlOptions('customers_enabled')); ?>
                            <?php echo $form->error($model, 'customers_enabled'); ?>
                        </div>
                    </div>
                    <div class="col-lg-4">
                        <div class="form-group">
                            <?php echo $form->labelEx($model, 'customers_use_system'); ?>
                            <?php echo $form->dropDownList($model, 'customers_use_system', $model->getYesNoOptions(), $model->fieldDecorator->getHtmlOptions('customers_use_system')); ?>
                            <?php echo $form->error($model, 'customers_use_system'); ?>
                        </div>
                    </div>
                    <div class="col-lg-4">
                        <div class="form-group">
                            <?php echo $form->labelEx($model, 'customers_add_account'); ?>
                            <?php echo $form->dropDownList($model, 'customers_add_account', $model->getYesNoOptions(), $model->fieldDecorator->getHtmlOptions('customers_add_account')); ?>
                            <?php echo $form->error($model, 'customers_add_account'); ?>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-lg-4">
                        <div class="form-group">
                            <?php echo $form->labelEx($model, 'groups'); ?>
                            <?php echo $form->dropDownList($model, 'groups', CMap::mergeArray(['' => ''], $model->getCustomerGroupsOptions()), $model->fieldDecorator->getHtmlOptions('groups', ['multiple' => true])); ?>
                            <?php echo $form->error($model, 'groups'); ?>
                        </div>
                    </div>
                </div>
                <?php
                /**
                 * This hook gives a chance to append content after the active form fields.
                 * Please note that from inside the action callback you can access all the controller view variables
                 * via {@CAttributeCollection $collection->controller->data}
                 * @since 1.3.3.1
                 */
                hooks()->doAction('after_active_form_fields', new CAttributeCollection([
                    'controller'    => $controller,
                    'form'          => $form,
                ])); ?>
                <div class="clearfix"><!-- --></div>
            </div>
            <div class="box-footer">
                <div class="pull-right">
                    <button type="submit" class="btn btn-primary btn-flat"><?php echo IconHelper::make('save') . t('app', 'Save changes'); ?></button>
                </div>
                <div class="clearfix"><!-- --></div>
            </div>
            <?php
            $controller->endWidget();
        }
        /**
         * This hook gives a chance to append content after the active form.
         * Please note that from inside the action callback you can access all the controller view variables
         * via {@CAttributeCollection $collection->controller->data}
         * @since 1.3.3.1
         */
        hooks()->doAction('after_active_form', new CAttributeCollection([
            'controller'      => $controller,
            'renderedForm'    => $collection->itemAt('renderForm'),
        ]));
        ?>
    </div>
<?php
}
/**
 * This hook gives a chance to append content after the view file default content.
 * Please note that from inside the action callback you can access all the controller view
 * variables via {@CAttributeCollection $collection->controller->data}
 * @since 1.3.3.1
 */
hooks()->doAction('after_view_file_content', new CAttributeCollection([
    'controller'        => $controller,
    'renderedContent'   => $viewCollection->itemAt('renderContent'),
]));
