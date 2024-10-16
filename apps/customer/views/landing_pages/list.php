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

/** @var Controller $controller */
$controller = controller();

/** @var string $pageHeading */
$pageHeading = (string)$controller->getData('pageHeading');

/** @var LandingPage $page */
$page = $controller->getData('page');

/**
 * This hook gives a chance to prepend content or to replace the default view content with a custom content.
 * Please note that from inside the action callback you can access all the controller view
 * variables via {@CAttributeCollection $collection->controller->getData()}
 * In case the content is replaced, make sure to set {@CAttributeCollection $collection->add('renderContent', false)}
 * in order to stop rendering the default content.
 * @since 1.3.3.1
 */
hooks()->doAction('before_view_file_content', $viewCollection = new CAttributeCollection([
    'controller'    => $controller,
    'renderContent' => true,
]));

// and render if allowed
if ($viewCollection->itemAt('renderContent')) {
    /**
     * @since 1.3.9.2
     */
    $itemsCount = LandingPage::model()->countByAttributes([
        'customer_id' => (int)customer()->getId(),
    ]); ?>
    <div class="box box-primary borderless">
        <div class="box-header">
            <div class="pull-left">
                <?php BoxHeaderContent::make(BoxHeaderContent::LEFT)
                    ->add('<h3 class="box-title">' . IconHelper::make('fa-clipboard') . html_encode((string)$pageHeading) . '</h3>')
                    ->render(); ?>
            </div>
            <div class="pull-right">
                <?php BoxHeaderContent::make(BoxHeaderContent::RIGHT)
                    ->addIf($controller->widget('common.components.web.widgets.GridViewToggleColumns', [
                        'model'   => $page,
                        'columns' => [
                            'title',
                            'slug',
                            'description',
                            'pageType',
                            'status',
                            'visitors_count',
                            'views_count',
                            'conversions_count',
                            'has_unpublished_changes',
                            'last_updated',
                            'date_added',
                        ],
                    ], true), $itemsCount)
                    ->add(CHtml::link(
                        IconHelper::make('create') . t('app', 'Create new'),
                        ['landing_pages/create'],
                        ['class' => 'btn btn-primary btn-flat', 'title' => t('app', 'Create new')]
                    ))
                    ->add(CHtml::link(
                        IconHelper::make('refresh') . t('app', 'Refresh'),
                        ['landing_pages/index'],
                        ['class' => 'btn btn-primary btn-flat', 'title' => t('app', 'Refresh')]
                    ))
                    ->render(); ?>
            </div>
            <div class="clearfix"><!-- --></div>
        </div>
        <div class="box-body">
            <div class="table-responsive">
                <?php
                /**
                 * This hook gives a chance to prepend content or to replace the default grid view content with a custom content.
                 * Please note that from inside the action callback you can access all the controller view
                 * variables via {@CAttributeCollection $collection->controller->getData()}
                 * In case the content is replaced, make sure to set {@CAttributeCollection $collection->itemAt('renderGrid')} to false
                 * in order to stop rendering the default content.
                 * @since 1.3.3.1
                 */
                hooks()->doAction('before_grid_view', $collection = new CAttributeCollection([
                    'controller' => $controller,
                    'renderGrid' => true,
                ]));

    /**
     * This widget renders default getting started page for this particular section.
     * @since 1.3.9.2
     */
    $controller->widget('common.components.web.widgets.StartPagesWidget', [
        'collection' => $collection,
        'enabled'    => !$itemsCount,
    ]);

    // and render if allowed
    if ($collection->itemAt('renderGrid')) {
        $controller->widget('common.components.web.widgets.GridViewBulkAction', [
            'model'      => $page,
            'formAction' => createUrl('landing_pages/bulk_action'),
        ]);
        $controller->widget('zii.widgets.grid.CGridView', hooks()->applyFilters('grid_view_properties', [
            'ajaxUrl'        => createUrl($controller->getRoute()),
            'id'             => $page->getModelName() . '-grid',
            'dataProvider'   => $page->search(),
            'filter'         => $page,
            'filterPosition' => 'body',
            'filterCssClass' => 'grid-filter-cell',
            'itemsCssClass'  => 'table table-hover',
            'selectableRows' => 0,
            'enableSorting'  => false,
            'cssFile'        => false,
            'pagerCssClass'  => 'pagination pull-right',
            'pager'          => [
                'class'       => 'CLinkPager',
                'cssFile'     => false,
                'header'      => false,
                'htmlOptions' => ['class' => 'pagination'],
            ],
            'columns'        => hooks()->applyFilters('grid_view_columns', [
                [
                    'class'               => 'CCheckBoxColumn',
                    'name'                => 'page_id',
                    'selectableRows'      => 100,
                    'checkBoxHtmlOptions' => ['name' => 'bulk_item[]'],
                ],
                [
                    'name'  => 'title',
                    'value' => '$data->getTitle()',
                ],
                [
                    'name'  => 'slug',
                    'value' => '$data->slug',
                ],
                [
                    'name'  => 'description',
                    'value' => '$data->getShortDescription()',
                ],
                [
                    'name'   => 'pageType',
                    'value'  => '$data->getPageTypeText()',
                ],
                [
                    'name'   => 'status',
                    'value'  => '$data->getStatusText()',
                    'filter' => $page->getStatusesArray(),
                ],
                [
                    'name'   => 'has_unpublished_changes',
                    'value'  => 't("app", ucfirst($data->has_unpublished_changes))',
                    'filter' => $page->getYesNoOptions(),
                ],
                [
                    'name'   => 'visitors_count',
                    'value'  => '$data->visitors_count',
                    'filter' => false,
                ],
                [
                    'name'   => 'views_count',
                    'value'  => '$data->views_count',
                    'filter' => false,
                ],
                [
                    'name'   => 'conversions_count',
                    'value'  => '$data->conversions_count',
                    'filter' => false,
                ],
                [
                    'name'   => 'last_updated',
                    'value'  => '$data->lastUpdated',
                    'filter' => false,
                ],
                [
                    'name'   => 'date_added',
                    'value'  => '$data->dateAdded',
                    'filter' => false,
                ],
                [
                    'class'             => 'DropDownButtonColumn',
                    'header'            => t('app', 'Options'),
                    'footer'            => $page->paginationOptions->getGridFooterPagination(),
                    'buttons'           => [
                        'overview'          => [
                            'label'    => IconHelper::make('fa-dashboard'),
                            'url'      => 'createUrl("landing_pages/overview", array("id" => $data->getHashId()))',
                            'imageUrl' => null,
                            'options'  => [
                                'title' => t('app', 'Overview'),
                                'class' => 'btn btn-primary btn-flat',
                            ],
                        ],
                        'preview' => [
                            'label'    => IconHelper::make('view'),
                            'url'      => '$data->permalink',
                            'imageUrl' => null,
                            'options'  => [
                                'title'  => t('app', 'Preview'),
                                'class'  => 'btn btn-primary btn-flat',
                                'target' => '_blank',
                            ],
                        ],
                        'update'        => [
                            'label'    => IconHelper::make('update'),
                            'url'      => 'createUrl("landing_pages/update", array("id" => $data->getHashId()))',
                            'imageUrl' => null,
                            'options'  => [
                                'title' => t('app', 'Update'),
                                'class' => 'btn btn-primary btn-flat',
                            ],
                        ],
                        'publish'       => [
                            'label'    => IconHelper::make('fa-play'),
                            'url'      => 'createUrl("landing_pages/publish", array("id" => $data->getHashId()))',
                            'imageUrl' => null,
                            'options'  => [
                                'title' => t('app', 'Publish'),
                                'class' => 'btn btn-primary btn-flat',
                            ],
                            'visible'  => '$data->getIsUnpublished()',
                        ],
                        'republish'     => [
                            'label'    => IconHelper::make('fa-play'),
                            'url'      => 'createUrl("landing_pages/publish", array("id" => $data->getHashId()))',
                            'imageUrl' => null,
                            'options'  => [
                                'title' => t('app', 'Republish'),
                                'class' => 'btn btn-primary btn-flat',
                            ],
                            'visible'  => '$data->getHasUnpublishedChanges() && $data->getIsPublished()',
                        ],
                        'unpublish'     => [
                            'label'    => IconHelper::make('fa-pause'),
                            'url'      => 'createUrl("landing_pages/unpublish", array("id" => $data->getHashId()))',
                            'imageUrl' => null,
                            'options'  => [
                                'title' => t('app', 'Unpublish'),
                                'class' => 'btn btn-primary btn-flat',
                            ],
                            'visible'  => '$data->getIsPublished()',
                        ],
                        'delete'        => [
                            'label'    => IconHelper::make('delete'),
                            'url'      => 'createUrl("landing_pages/delete", array("id" => $data->getHashId()))',
                            'imageUrl' => null,
                            'options'  => [
                                'title' => t('app', 'Delete'),
                                'class' => 'btn btn-danger btn-flat delete',
                            ],
                        ],
                    ],
                    'headerHtmlOptions' => ['style' => 'text-align: right'],
                    'footerHtmlOptions' => ['align' => 'right'],
                    'htmlOptions'       => ['align' => 'right', 'class' => 'options'],
                    'template'          => '{overview} {preview} {update} {publish} {republish} {unpublish} {delete}',
                ],
            ], $controller),
        ], $controller));
    }
    /**
     * This hook gives a chance to append content after the grid view content.
     * Please note that from inside the action callback you can access all the controller view
     * variables via {@CAttributeCollection $collection->controller->getData()}
     * @since 1.3.3.1
     */
    hooks()->doAction('after_grid_view', new CAttributeCollection([
                    'controller'   => $controller,
                    'renderedGrid' => $collection->itemAt('renderGrid'),
                ])); ?>
                <div class="clearfix"><!-- --></div>
            </div>
        </div>
    </div>
    <?php
}
/**
 * This hook gives a chance to append content after the view file default content.
 * Please note that from inside the action callback you can access all the controller view
 * variables via {@CAttributeCollection $collection->controller->getData()}
 * @since 1.3.3.1
 */
hooks()->doAction('after_view_file_content', new CAttributeCollection([
    'controller'      => $controller,
    'renderedContent' => $viewCollection->itemAt('renderContent'),
]));
