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

/** @var string $content */
$content = (string)$controller->getData('content');

/** @var array $attributes */
$attributes = (array)$controller->getData('attributes');

/** @var Lists $list */
$list = $controller->getData('list');

/** @var ListPageType $pageType */
$pageType = $controller->getData('pageType');

/** @var ListPage $page */
$page = $controller->getData('page');

// since 1.3.5.6
$htmlOptions = ['class' => 'list-subscribe-form'];
if (!empty($attributes) && !empty($attributes['target']) && in_array($attributes['target'], ['_blank'])) {
    $htmlOptions['target'] = $attributes['target'];
}
?>

<?php
    // since 2.3.3
    hooks()->doAction('frontend_list_page_display_content_before_content_wrapper', new CAttributeCollection([
        'list'      => $list,
        'pageType'  => $pageType,
        'page'      => $page,
    ]));
?>

<div class="row">
    <div class="<?php echo (string)$controller->layout != 'embed' ? 'col-lg-6 col-lg-push-3 col-md-6 col-md-push-3 col-sm-12' : ''; ?>">
        <?php echo CHtml::form('', 'post', $htmlOptions); ?>
        <?php
            // since 2.3.3
            hooks()->doAction('frontend_list_page_display_content_before_content', new CAttributeCollection([
                'list'      => $list,
                'pageType'  => $pageType,
                'page'      => $page,
            ]));
        ?>
        
        <?php echo (string)$content; ?>
        
        <?php
            // since 2.3.3
            hooks()->doAction('frontend_list_page_display_content_after_content', new CAttributeCollection([
                'list'      => $list,
                'pageType'  => $pageType,
                'page'      => $page,
            ]));
        ?>
        
        <?php echo CHtml::endForm(); ?>
    </div>
</div>

<?php
    // since 2.3.3
    hooks()->doAction('frontend_list_page_display_content_after_content_wrapper', new CAttributeCollection([
        'list'      => $list,
        'pageType'  => $pageType,
        'page'      => $page,
    ]));
?>