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
 * @since 1.0
 */

/** @var Controller $controller */
$controller = controller();

/** @var Article $article */
$article = $controller->getData('article');

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
    <div class="row">
        <div class="col-lg-12">
            <h1 class="page-heading">
                <?php echo html_encode((string)$article->title); ?>
            </h1>
            <hr/>
            <?php echo html_purify((string)$article->content); ?>
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

<?php } ?>
