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

/** @var LandingPageRevision $revision */
$revision = $variant->revision;

/** @var LandingPage $page */
$page = $revision->page;

/** @var LandingPageRevisionVariant[] $variants */
$variants = $revision->variants;
?>
<li>
    <?php echo CHtml::link(
    IconHelper::make('fa-arrow-left') . t('app', 'Overview'),
    ['landing_pages/overview', 'id' => $page->getHashId()]
); ?>
</li>
<!-- BEGIN LANGS -->
<li class="top-bar-left-items-block">
    <?php echo CHtml::link($variant->title, 'javascript:void(0);', [
        'class'          => 'active top-bar-left-items-block-title',
        'title'          => t('app', 'Double click to edit'),
        'data-placement' => 'right',
        'style'          => $variant->hasErrors() ? 'display:none' : '',
    ]); ?>
    <?php if (count($variants) > 1) { ?>
        <div class="top-bar-items-wrapper">
            <div class="top-bar-items">
                <?php foreach ($variants as $revisionVariant) {
        if ($revisionVariant->variant_id === $variant->variant_id) {
            continue;
        }
        echo CHtml::link(
            $revisionVariant->title,
            ['landing_page_variants/update', 'id' => $revisionVariant->getHashId()]
        );
    } ?>
            </div>
        </div>
    <?php } ?>
</li>
