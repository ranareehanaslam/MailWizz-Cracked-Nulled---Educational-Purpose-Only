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

/** @var Page $page */
$page = $controller->getData('page');

/** @var CActiveForm $form */
$form = $controller->getData('form');
?>
<?php echo CHtml::tag('div', [
    'class' => 'form-controls-wrapper floating-container',
    'style' => !$page->hasErrors() && !$page->getIsNewRecord() ? 'display:none' : '',
]); ?>

    <?php echo CHtml::tag('nav', [
        'id' => 'content-builder-floating-nav',
        'class' => !$page->hasErrors() && !$page->getIsNewRecord() ? 'nav' : 'nav nav-show',
    ]); ?>
        <ul>
            <li class="list-unstyled form-group">
                <?php echo $form->labelEx($page, 'title'); ?>
                <?php echo $form->textField($page, 'title', $page->fieldDecorator->getHtmlOptions('title', [
                    'class'        => 'title-input form-control',
                    'data-page-id' => (int)$page->page_id,
                    'data-slug-url' => createUrl('pages/slug'),
                ])); ?>
                <?php echo $form->error($page, 'title'); ?>
            </li>
            <li class="list-unstyled form-group slug-wrapper"<?php if (empty($page->slug)) {
                    echo ' style="display:none"';
                } ?>>
                <?php echo $form->labelEx($page, 'slug'); ?>
                <?php echo $form->textField($page, 'slug', $page->fieldDecorator->getHtmlOptions('slug')); ?>
                <?php echo $form->error($page, 'slug'); ?>
            </li>

            <li class="list-unstyled form-group">
                <?php echo $form->labelEx($page, 'status'); ?>
                <?php echo $form->dropDownList($page, 'status', $page->getStatusesList(), $page->fieldDecorator->getHtmlOptions('status')); ?>
                <?php echo $form->error($page, 'status'); ?>
            </li>
    </ul>
    <?php echo CHtml::closeTag('nav'); ?>

<?php echo CHtml::closeTag('div'); ?>
