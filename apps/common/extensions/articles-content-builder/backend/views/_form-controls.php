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

/** @var Article $article */
$article = $controller->getData('article');

/** @var ArticleToCategory $articleToCategory */
$articleToCategory = $controller->getData('articleToCategory');

/** @var CActiveForm $form */
$form = $controller->getData('form');
?>
<?php echo CHtml::tag('div', [
    'class' => 'form-controls-wrapper floating-container',
    'style' => !$article->hasErrors() && !$article->getIsNewRecord() ? 'display:none' : '',
]); ?>

<?php echo CHtml::tag('nav', [
    'id'    => 'content-builder-floating-nav',
    'class' => !$article->hasErrors() && !$article->getIsNewRecord() ? 'nav' : 'nav nav-show',
]); ?>
<ul>
    <li class="list-unstyled form-group">
        <?php echo $form->labelEx($article, 'title'); ?>
        <?php echo $form->textField($article, 'title', $article->fieldDecorator->getHtmlOptions('title', [
            'class'           => 'title-input form-control',
            'data-article-id' => (int)$article->article_id,
            'data-slug-url'   => createUrl('articles/slug'),
        ])); ?>
        <?php echo $form->error($article, 'title'); ?>
    </li>
    <li class="list-unstyled form-group slug-wrapper"<?php if (empty($article->slug)) {
            echo ' style="display:none"';
        } ?>>
        <?php echo $form->labelEx($article, 'slug'); ?>
        <?php echo $form->textField($article, 'slug', $article->fieldDecorator->getHtmlOptions('slug')); ?>
        <?php echo $form->error($article, 'slug'); ?>
    </li>

    <li class="list-unstyled form-group">
        <?php echo $form->labelEx($article, 'status'); ?>
        <?php echo $form->dropDownList(
            $article,
            'status',
            $article->getStatusesArray(),
            $article->fieldDecorator->getHtmlOptions('status')
        ); ?>
        <?php echo $form->error($article, 'status'); ?>
    </li>
    <li class="list-unstyled form-group pt-10">
        <?php echo $form->labelEx($articleToCategory, 'category_id'); ?>
        <div class="article-categories-scrollbox list-unstyled-builder">
            <ul class="list-group">
                <?php echo CHtml::checkBoxList(
            $articleToCategory->getModelName(),
            $article->getSelectedCategoriesArray(),
            $article->getAvailableCategoriesArray(),
            $articleToCategory->fieldDecorator->getHtmlOptions('category_id', [
                        'class'        => '',
                        'template'     => '<li class="list-group-item list-unstyled-builder">{beginLabel}{input} <span>{labelTitle}</span> {endLabel}</li>',
                        'container'    => '',
                        'separator'    => '',
                        'labelOptions' => ['style' => 'margin-right: 10px;'],
                    ])
        ); ?>
            </ul>
        </div>
        <?php echo $form->error($articleToCategory, 'category_id'); ?>
    </li>
</ul>
<?php echo CHtml::closeTag('nav'); ?>

<?php echo CHtml::closeTag('div'); ?>
