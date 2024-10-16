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
 * @since 2.3.0
 */

/** @var LandingPage $page */
/** @var LandingPageRevisionVariant|null $publishedVariant */
/** @var LandingPageRevisionVariant[] $variants */
/** @var string $title */

?>
<div class="box box-primary borderless">
    <div class="box-header">
        <div class="pull-left">
            <?php BoxHeaderContent::make(BoxHeaderContent::LEFT)
                ->add('<h3 class="box-title">' . $title . '</h3>')
                ->render(); ?>
        </div>
        <div class="pull-right"></div>
        <div class="clearfix"><!-- --></div>
    </div>
    <div class="box-body" style="border-bottom: none;">

        <div class="active-variant-table">
            <div class="lp-table">
                <div class="lp-table-row lp-table-header lp-table-borders variant-row-variant-column"
                     style="grid-template-columns: repeat(16, 1fr);">
                    <div class="table-cell active-variant-table-pad-left" style="grid-column: span 6 / auto;">
                        <?php echo t('landing_pages', 'Variant'); ?>
                    </div>
                    <div class="table-cell active-variant-table-num-header" style="grid-column: span 2 / auto;">
                        <?php echo t('landing_pages', 'Visitors'); ?>
                        <span class="pl-2">
                            <i class="fa fa-info-circle text-primary-color" style="font-size:15px;"
                               data-toggle="tooltip" data-placement="top" title="<?php echo t('landing_pages', 'Only the published variant will record visitors'); ?>">
                            </i>
                        </span>
                    </div>
                    <div class="table-cell active-variant-table-num-header" style="grid-column: span 2 / auto;">
                        <?php echo t('landing_pages', 'Views'); ?>
                        <span class="pl-2">
                            <i class="fa fa-info-circle text-primary-color" style="font-size:15px;"
                               data-toggle="tooltip" data-placement="top" title="<?php echo t('landing_pages', 'Only the published variant will record views'); ?>">
                            </i>
                        </span>
                    </div>
                    <div class="table-cell active-variant-table-num-header" style="grid-column: span 3 / auto;">
                        <?php echo t('landing_pages', 'Conversions'); ?>
                        <span class="pl-2">
                            <i class="fa fa-info-circle text-primary-color ml-2" style="font-size:15px;"
                               data-toggle="tooltip" data-placement="top" title="<?php echo t('landing_pages', 'Only the published variant will record conversions'); ?>"></i>
                        </span>
                    </div>
                    <div class="table-cell active-variant-table-num-header" style="grid-column: span 3 / auto;">
                    </div>
                </div>

                <?php foreach ($variants as $variant) { ?>

                    <div class="lp-table-row lp-table-borders variant-row-variant-column"
                         style="grid-template-columns: repeat(16, 1fr);">
                        <div class="table-cell variant-data-row"
                             style="grid-column: span 6 / auto;">
                            <div class="variant-data-row-variant-description">
                                <div class="variant-data-row-name-and-date">
                                    <div>
                                        <div class="variant-name-editable-name">
                                            <div class="variant-name">
                                                <?php echo html_encode($variant->title); ?>
                                            </div>
                                        </div>
                                        <div style="font-weight: 400">
                                            <small>
                                                <?php echo html_encode(t(
                    'landing_pages',
                    'Updated about {relativeTime}',
                    ['{relativeTime}' => $variant->getRelativeLastUpdated()]
                )); ?>
                                            </small>
                                            <?php if ($variant->getIsActive()) { ?>
                                                <?php if (!$variant->getIsPublished()) { ?>
                                                    <span class="badge badge-light-primary">
                                                    <?php echo t('landing_pages', 'Unpublished'); ?>
                                                </span>
                                                <?php } ?>
                                                <?php if ($variant->getIsPublished()) { ?>
                                                    <span class="badge badge-light-success small">
                                                    <?php echo t('landing_pages', 'Published'); ?>
                                                </span>
                                                <?php } ?>
                                            <?php } ?>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="table-cell table-numerical-cell" style="grid-column: span 2 / auto;">
                            <span class=""><?php echo formatter()->formatNumber($variant->visitors_count); ?></span>
                        </div>
                        <div class="table-cell table-numerical-cell" style="grid-column: span 2 / auto;">
                            <span class=""><?php echo formatter()->formatNumber($variant->views_count); ?></span>
                        </div>
                        <div class="table-cell table-numerical-cell" style="grid-column: span 3 / auto;">
                            <span class=""><?php echo formatter()->formatNumber($variant->conversions_count); ?></span>
                        </div>
                        <div class="table-cell table-numerical-cell" style="grid-column: span 1 / auto;">
                            <span class=""></span>
                        </div>
                        <div class="table-cell" style="grid-column: span 1 / auto;">
                            <?php echo CHtml::link(
                    t('app', 'Edit'),
                    ['landing_page_variants/update', 'id' => $variant->getHashId()],
                    ['class' => 'btn btn-primary', 'title' => t('app', 'Edit')]
                ); ?>
                        </div>
                        <div class="table-cell variant-data-row-more-cell" style="grid-column: span 1 / auto;">
                            <div class="flyout-wrapper">
                                <button class="more-button-wrapper" type="button" id="dropdownMenuButton"
                                        data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                    <span class="icon-icon"
                                          style="width: 22px; height: 22px;">
                                        <svg viewBox="0 0 16 16">
                                            <path class="svg-icon-dot"
                                                  d="M14.17,9.86A1.85,1.85,0,1,1,16,8,1.85,1.85,0,0,1,14.17,9.86ZM8,9.86A1.85,1.85,0,1,1,9.86,8,1.85,1.85,0,0,1,8,9.86Zm-6.16,0A1.85,1.85,0,1,1,3.7,8,1.85,1.85,0,0,1,1.85,9.86Z"></path>
                                        </svg>
                                    </span>
                                </button>
                                <ul class="dropdown-menu more-dropdown-menu " role="menu">
                                    <?php if ($variant->getCanBeMadeInactive()) { ?>
                                        <li>
                                            <?php echo CHtml::link(
                    t('app', 'Make inactive'),
                    ['landing_page_variants/toggle_active', 'id' => $variant->getHashId()],
                    [
                                                    'class' => 'landing-page-variant-action inactivate',
                                                    'title' => t('app', 'Make inactive'),
                                                ]
                ); ?>
                                        </li>
                                    <?php } ?>
                                    <?php if ($variant->getIsInactive()) { ?>
                                        <li>
                                            <?php echo CHtml::link(
                    t('app', 'Make active'),
                    ['landing_page_variants/toggle_active', 'id' => $variant->getHashId()],
                    [
                                                    'class' => 'landing-page-variant-action activate',
                                                    'title' => t('app', 'Make active'),
                                                ]
                ); ?>
                                        </li>
                                    <?php } ?>
                                    <li>
                                        <?php echo CHtml::link(
                    t('app', 'Preview'),
                    $variant->getPermalink(),
                    ['target' => '_blank', 'title' => t('app', 'Preview')]
                ); ?>
                                    </li>
                                    <li>
                                        <?php echo CHtml::link(
                    t('app', 'Duplicate'),
                    ['landing_page_variants/copy', 'id' => $variant->getHashId()],
                    [
                                                'class' => 'landing-page-variant-action duplicate',
                                                'title' => t('app', 'Duplicate'),
                                            ]
                ); ?>
                                    </li>
                                    <?php if ($variant->getCanBeDeleted()) { ?>
                                        <li>
                                            <?php echo CHtml::link(
                    t('app', 'Delete'),
                    ['landing_page_variants/delete', 'id' => $variant->getHashId()],
                    [
                                                    'class'        => 'landing-page-variant-action delete',
                                                    'title'        => t('app', 'Delete'),
                                                    'data-confirm' => t(
                                                        'landing_pages',
                                                        'Are you sure you want to delete this variant?'
                                                    ),
                                                ]
                ); ?>
                                        </li>
                                    <?php } ?>
                                    <?php if (!$variant->getIsPublished() && !empty($publishedVariant) && $publishedVariant->getIsPublished()) { ?>
                                        <li>
                                            <?php echo CHtml::link(
                    t('app', 'Revert to the last published variant'),
                    ['landing_page_variants/revert', 'id' => $variant->getHashId()],
                    [
                                                    'class'        => 'landing-page-variant-action revert',
                                                    'title'        => t('app', 'Revert to the last published variant'),
                                                    'data-confirm' => t(
                                                        'landing_pages',
                                                        'Are you sure you want to revert to the published variant?'
                                                    ),
                                                ]
                ); ?>
                                        </li>
                                    <?php } ?>
                                </ul>
                            </div>
                        </div>
                    </div>
                    <?php if ($variant->getIsActive() && !$variant->getIsPublished() && !empty($publishedVariant) && $publishedVariant->getIsPublished()) { ?>
                        <details class="tree-nav-item is-expandable" open>
                            <summary class="tree-nav-item-title">
                                <span class="badge badge-light-success small">
                                    <?php echo t('landing_pages', 'Published'); ?>
                                </span>
                            </summary>

                            <div class="inactive-section">
                                <div class="lp-table">
                                    <div class="lp-table-row lp-table-borders variant-row-variant-column"
                                         style="grid-template-columns: repeat(16, 1fr);">
                                        <div class="table-cell variant-data-row"
                                             style="grid-column: span 6 / auto;">
                                            <div class="variant-data-row-variant-description">
                                                <div class="variant-data-row-name-and-date">
                                                    <div>
                                                        <div class="variant-name-editable-name">
                                                            <div class="variant-name">
                                                                <?php echo html_encode($publishedVariant->title); ?>
                                                            </div>
                                                        </div>
                                                        <div style="font-weight: 400">
                                                            <small>
                                                                <?php echo html_encode(t(
                    'landing_pages',
                    'Updated about {relativeTime}',
                    ['{relativeTime}' => $publishedVariant->getRelativeLastUpdated()]
                )); ?>
                                                            </small>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="table-cell table-numerical-cell" style="grid-column: span 2 / auto;">
                                            <span class=""><?php echo formatter()->formatNumber($publishedVariant->visitors_count); ?></span>
                                        </div>
                                        <div class="table-cell table-numerical-cell"
                                             style="grid-column: span 2 / auto;">
                                            <span class=""><?php echo formatter()->formatNumber($publishedVariant->views_count); ?></span>
                                        </div>
                                        <div class="table-cell table-numerical-cell"
                                             style="grid-column: span 3 / auto;">
                                            <span class=""><?php echo formatter()->formatNumber($publishedVariant->conversions_count); ?></span>
                                        </div>
                                        <div class="table-cell table-numerical-cell"
                                             style="grid-column: span 3 / auto;">
                                            <span class=""></span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </details>
                    <?php } ?>
                <?php } ?>
            </div>
        </div>
    </div>
</div>
