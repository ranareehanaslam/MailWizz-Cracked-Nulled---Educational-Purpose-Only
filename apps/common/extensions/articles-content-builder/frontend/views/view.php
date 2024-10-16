<?php declare(strict_types=1);
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

?>

<div class="row">
    <div class="col-lg-12">
        <hr/>
        <?php
        $controller->widget('frontend.components.web.widgets.article.ArticleCategoriesWidget', [
            'article' => $article,
        ]);
        $controller->widget('frontend.components.web.widgets.article.ArticleRelatedArticlesWidget', [
            'article' => $article,
        ]);
        ?>
    </div>
</div>
