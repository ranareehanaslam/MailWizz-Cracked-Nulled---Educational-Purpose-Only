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
 * @since 2.3.0
 */


/** @var Controller $controller */
$controller = controller();

/** @var LandingPageRevisionVariant $variant */
$variant = $controller->getData('variant');

/** @var CActiveForm $form */
$form = $controller->getData('form');
?>

<?php echo CHtml::tag('div', [
    'class' => 'form-controls-wrapper floating-container',
    'style' => !$variant->hasErrors() && !$variant->getIsNewRecord() ? 'display:none' : '',
]); ?>

<?php echo CHtml::tag('nav', [
    'id' => 'content-builder-floating-nav',
    'class' => !$variant->hasErrors() && !$variant->getIsNewRecord() ? 'nav' : 'nav nav-show',
]); ?>
<ul>
    <li class="list-unstyled form-group">
        <?php echo $form->labelEx($variant, 'title'); ?>
        <?php echo $form->textField($variant, 'title', $variant->fieldDecorator->getHtmlOptions('title', [
            'class'        => 'title-input',
        ])); ?>
        <?php echo $form->error($variant, 'title'); ?>
    </li>
</ul>
<?php echo CHtml::closeTag('nav'); ?>

<?php echo CHtml::closeTag('div'); ?>
