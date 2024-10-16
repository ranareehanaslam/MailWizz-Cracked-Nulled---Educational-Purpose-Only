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

/** @var string $pageHeading */
$pageHeading = (string)$controller->getData('pageHeading');

/** @var Campaign $campaign */
$campaign = $controller->getData('campaign');

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
    $itemsCount = (int)Campaign::model()->countByAttributes([
        'customer_id' => (int)customer()->getId(),
        'status'      => array_keys($campaign->getStatusesList()),
    ]); ?>

    <div class="box box-primary borderless">
        <div class="box-header">
            <div class="pull-left">
                <?php BoxHeaderContent::make(BoxHeaderContent::LEFT)
                    ->add('<h3 class="box-title">' . IconHelper::make('envelope') . html_encode((string)$pageHeading) . '</h3>')
                    ->render(); ?>
            </div>
            <div class="pull-right">
                <?php BoxHeaderContent::make(BoxHeaderContent::RIGHT)
                    ->addIf($controller->widget('common.components.web.widgets.GridViewToggleColumns', ['model' => $campaign, 'columns' => ['campaign_id', 'campaign_uid', 'customer_id', 'name', 'type', 'group_id', 'send_group_id', 'list_id', 'segment_id', 'search_recurring', 'send_at', 'started_at', 'status', 'search_template_name', 'gridViewDelivered', 'gridViewOpens', 'gridViewClicks', 'gridViewBounces', 'gridViewUnsubs']], true), $itemsCount)
                    ->addIf(CHtml::link(IconHelper::make('glyphicon-list-alt') . t('app', 'All campaigns'), ['campaigns/index'], ['class' => 'btn btn-primary btn-flat', 'title' => t('lists', 'View all campaigns')]), $campaign->getIsArchived())
                    ->add(CHtml::link(IconHelper::make('refresh') . t('app', 'Refresh'), ['campaigns/archive'], ['class' => 'btn btn-primary btn-flat', 'title' => t('app', 'Refresh')]))
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
                'controller'    => $controller,
                'renderGrid'    => true,
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
        // since 1.3.5.6
        $controller->widget('common.components.web.widgets.GridViewBulkAction', [
                    'model'      => $campaign,
                    'formAction' => createUrl('campaigns/bulk_action', ['type' => Campaign::TYPE_REGULAR]),
                ]);

        $controller->widget('zii.widgets.grid.CGridView', hooks()->applyFilters('grid_view_properties', [
                    'ajaxUrl'           => createUrl($controller->getRoute()),
                    'id'                => $campaign->getModelName() . '-grid',
                    'dataProvider'      => $campaign->search(),
                    'filter'            => $campaign,
                    'filterPosition'    => 'body',
                    'filterCssClass'    => 'grid-filter-cell',
                    'itemsCssClass'     => 'table table-hover',
                    'selectableRows'    => 0,
                    'enableSorting'     => true,
                    'cssFile'           => false,
                    'pagerCssClass'     => 'pagination pull-right',
                    'pager'             => [
                        'class'         => 'CLinkPager',
                        'cssFile'       => false,
                        'header'        => false,
                        'htmlOptions'   => ['class' => 'pagination'],
                    ],
                    'afterAjaxUpdate' => 'function(id, data){
                        handleCampaignGridViewRows();
                    }',
                    'rowHtmlOptionsExpression' => '[
                        "data-campaign-id"  => $data->campaign_id,
                        "data-stats-url"    => createUrl("campaigns/grid_view_stats", ["campaign_uid" => $data->campaign_uid])
                    ]',
                    'columns' => hooks()->applyFilters('grid_view_columns', [
                        [
                            'class'               => 'CCheckBoxColumn',
                            'name'                => 'campaign_uid',
                            'selectableRows'      => 100,
                            'checkBoxHtmlOptions' => ['name' => 'bulk_item[]'],
                        ],
                        [
                            'name'   => 'campaign_id',
                            'value'  => '$data->campaign_id',
                            'filter' => false,
                        ],
                        [
                            'name'  => 'campaign_uid',
                            'value' => 'CHtml::link($data->campaign_uid, createUrl("campaigns/overview", array("campaign_uid" => $data->campaign_uid)))',
                            'type'  => 'raw',

                        ],
                        [
                            'name'  => 'customer_id',
                            'value' => 'HtmlHelper::accessLink($data->customer->getFullName(), array("customers/update", "id" => $data->customer_id), array("fallbackText" => true))',
                            'type'  => 'raw',
                        ],
                        [
                            'name'  => 'name',
                            'value' => 'CHtml::link($data->name, createUrl("campaigns/overview", array("campaign_uid" => $data->campaign_uid)))',
                            'type'  => 'raw',
                        ],
                        [
                            'name'        => 'type',
                            'value'       => 'ucfirst(strtolower((string)$data->getTypeNameDetails()))',
                            'type'        => 'raw',
                            'filter'      => $campaign->getTypesList(),
                            'htmlOptions' => ['style' => 'max-width: 150px'],
                        ],
                        [
                            'name'  => 'group_id',
                            'value' => '!empty($data->group_id) ? CHtml::link($data->group->name, createUrl("campaign_groups/update", array("group_uid" => $data->group->uid))) : "-"',
                            'filter'=> $campaign->getGroupsDropDownArray(),
                            'type'  => 'raw',
                        ],
                        [
                            'name'  => 'send_group_id',
                            'value' => '!empty($data->send_group_id) ? CHtml::link($data->sendGroup->name, createUrl("campaign_send_groups/update", array("group_uid" => $data->sendGroup->uid))) : "-"',
                            'filter'=> $campaign->getSendGroupsDropDownArray(),
                            'type'  => 'raw',
                        ],
                        [
                            'name'  => 'list_id',
                            'value' => 'CHtml::link(StringHelper::truncateLength($data->list->name, 100), createUrl("lists/overview", array("list_uid" => $data->list->uid)))',
                            'type'  => 'raw',
                        ],
                        [
                            'name'  => 'segment_id',
                            'value' => '!empty($data->segment_id) ? CHtml::link(StringHelper::truncateLength($data->segment->name, 100), createUrl("list_segments/update", array("list_uid" => $data->list->uid, "segment_uid" => $data->segment->uid))) : "-"',
                            'type'  => 'raw',
                        ],
                        [
                            'name'        => 'search_recurring',
                            'value'       => 't("app", $data->option->cronjob_enabled ? "Yes" : "No")',
                            'filter'      => $campaign->getYesNoOptions(),
                            'htmlOptions' => ['style' => 'max-width: 150px'],
                            'sortable'    => false,
                        ],
                        [
                            'name'  => 'send_at',
                            'value' => '$data->getSendAt()',
                            'filter'=> false,
                        ],
                        [
                            'name'  => 'started_at',
                            'value' => '$data->getStartedAt()',
                            'filter'=> false,
                        ],
                        [
                            'name'  => 'status',
                            'value' => '$data->getStatusName()',
                            'filter'=> $campaign->getStatusesList(),
                            'type'  => 'raw',
                            'htmlOptions' => ['class' => 'campaign-grid-view-status-column'],
                        ],
                        [
                            'name'      => 'search_template_name',
                            'value'     => '!empty($data->template) ? $data->template->name : ""',
                            'sortable'  => false,
                        ],
                        [
                            'name'      => 'gridViewDelivered',
                            'value'     => 'IconHelper::make("fa fa-spinner fa-spin")',
                            'filter'    => false,
                            'sortable'  => false,
                            'type'      => 'raw',
                            'htmlOptions' => ['class' => 'campaign-grid-view-delivered-column'],
                        ],
                        [
                            'name'      => 'gridViewOpens',
                            'value'     => 'IconHelper::make("fa fa-spinner fa-spin")',
                            'filter'    => false,
                            'sortable'  => false,
                            'type'      => 'raw',
                            'htmlOptions' => ['class' => 'campaign-grid-view-opens-column'],
                        ],
                        [
                            'name'      => 'gridViewClicks',
                            'value'     => 'IconHelper::make("fa fa-spinner fa-spin")',
                            'filter'    => false,
                            'sortable'  => false,
                            'type'      => 'raw',
                            'htmlOptions' => ['class' => 'campaign-grid-view-clicks-column'],
                        ],
                        [
                            'name'      => 'gridViewBounces',
                            'value'     => 'IconHelper::make("fa fa-spinner fa-spin")',
                            'filter'    => false,
                            'sortable'  => false,
                            'type'      => 'raw',
                            'htmlOptions' => ['class' => 'campaign-grid-view-bounces-column'],
                        ],
                        [
                            'name'      => 'gridViewUnsubs',
                            'value'     => 'IconHelper::make("fa fa-spinner fa-spin")',
                            'filter'    => false,
                            'sortable'  => false,
                            'type'      => 'raw',
                            'htmlOptions' => ['class' => 'campaign-grid-view-unsubs-column'],
                        ],
                        [
                            'class'     => 'DropDownButtonColumn',
                            'header'    => t('app', 'Options'),
                            'footer'    => $campaign->paginationOptions->getGridFooterPagination(),
                            'buttons'   => [
                                'quick-view'=> [
                                    'label'     => IconHelper::make('fa-search-plus'),
                                    'url'       => 'createUrl("campaigns/quick_view", array("campaign_uid" => $data->campaign_uid))',
                                    'imageUrl'  => null,
                                    'options'   => ['title' => t('campaigns', 'Quick view'), 'class' => 'btn btn-primary btn-flat btn-campaign-quick-view'],
                                    'visible'   => '!$data->isPendingDelete',
                                ],
                                'overview'=> [
                                    'label'     => IconHelper::make('info'),
                                    'url'       => 'createUrl("campaigns/overview", array("campaign_uid" => $data->campaign_uid))',
                                    'imageUrl'  => null,
                                    'options'   => ['title' => t('campaigns', 'Overview'), 'class' => 'btn btn-primary btn-flat'],
                                    'visible'   => '(!$data->getEditable() || $data->getIsPaused()) && !$data->getIsPendingDelete()',
                                ],
                                'unarchive' => [
                                    'label'     => IconHelper::make('glyphicon-expand'),
                                    'url'       => 'createUrl("campaigns/toggle_archive", array("campaign_uid" => $data->campaign_uid))',
                                    'imageUrl'  => null,
                                    'options'   => ['title' => t('app', 'Unarchive'), 'class' => 'btn btn-primary btn-flat'],
                                    'visible'   => '$data->getIsArchived()',
                                ],
                                'webversion'=> [
                                    'label'     => IconHelper::make('view'),
                                    'url'       => 'options()->get("system.urls.frontend_absolute_url") . "campaigns/" . $data->campaign_uid',
                                    'imageUrl'  => null,
                                    'options'   => ['title' => t('campaigns', 'Web version'), 'class' => 'btn btn-primary btn-flat', 'target' => '_blank'],
                                    'visible'   => '$data->canViewWebVersion',
                                ],
                                'delete' => [
                                    'label'     => IconHelper::make('delete'),
                                    'url'       => 'createUrl("campaigns/delete", array("campaign_uid" => $data->campaign_uid))',
                                    'imageUrl'  => null,
                                    'visible'   => '$data->removable',
                                    'options'   => ['title' => t('app', 'Delete'), 'class' => 'btn btn-danger btn-flat delete'],
                                ],
                            ],
                            'headerHtmlOptions' => ['style' => 'text-align: right'],
                            'footerHtmlOptions' => ['align' => 'right'],
                            'htmlOptions'       => ['align' => 'right', 'class' => 'options'],
                            'template'          => '{quick-view} {overview} {unarchive} {webversion} {delete}',
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
                'controller'    => $controller,
                'renderedGrid'  => $collection->itemAt('renderGrid'),
            ])); ?>
            <div class="clearfix"><!-- --></div>
            </div>
        </div>
    </div>

	<?php
    /**
     * Since 1.9.17
     * This creates a modal placeholder to push campaign info in.
     */
    $controller->widget('common.components.web.widgets.CampaignQuickViewWidget');

    /**
     * Since 1.9.17
     * This creates a modal placeholder to push campaign comparison info in.
     */
    $controller->widget('common.components.web.widgets.CampaignsCompareWidget'); ?>

<?php
}
/**
 * This hook gives a chance to append content after the view file default content.
 * Please note that from inside the action callback you can access all the controller view
 * variables via {@CAttributeCollection $collection->controller->getData()}
 * @since 1.3.3.1
 */
hooks()->doAction('after_view_file_content', new CAttributeCollection([
    'controller'        => $controller,
    'renderedContent'   => $viewCollection->itemAt('renderContent'),
]));
