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

$title = empty($article->title) ? t('app', 'Double click to edit') : $article->title;

?>
<li>
    <?php echo CHtml::link(
    IconHelper::make('fa-arrow-left') . t('app', 'Back'),
    ['articles/index']
); ?>
</li>
<!-- BEGIN LANGS -->
<li class="top-bar-left-items-block">
    <?php echo CHtml::link($title, 'javascript:void(0);', [
        'class'          => 'active top-bar-left-items-block-title',
        'title'          => t('app', 'Double click to edit'),
        'data-placement' => 'right',
    ]); ?>
</li>
