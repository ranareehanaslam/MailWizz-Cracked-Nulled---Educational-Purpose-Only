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
 * @since 2.5.0
 */

/** @var Controller $controller */
$controller = controller();

/** @var string $pageHeading */
$pageHeading = (string)$controller->getData('pageHeading');

/** @var CampaignTrackingIgnoreList $model */
$model = $controller->getData('model');

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
if ($viewCollection->itemAt('renderContent')) { ?>
    <div class="box box-primary borderless">
        <div class="box-header">
            <div class="pull-left">
                <?php BoxHeaderContent::make(BoxHeaderContent::LEFT)
                    ->add('<h3 class="box-title">' . IconHelper::make('glyphicon-file') . html_encode((string)$pageHeading) . '</h3>')
                    ->render();
    ?>
            </div>
            <div class="pull-right">
                <?php BoxHeaderContent::make(BoxHeaderContent::RIGHT)
                    ->add($controller->widget('common.components.web.widgets.GridViewToggleColumns', ['model' => $model, 'columns' => ['ip_address', 'action', 'reason', 'status', 'date_added', 'last_updated']], true))
                    ->add(HtmlHelper::accessLink(IconHelper::make('refresh') . t('app', 'Refresh'), ['campaigns_tracking_ignore_list/index'], ['class' => 'btn btn-primary btn-flat', 'title' => t('app', 'Refresh')]))
                    ->render();
    ?>
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
    'controller'  => $controller,
    'renderGrid'  => true,
            ]));

    // and render if allowed
    if ($collection->itemAt('renderGrid')) {
        $controller->widget('zii.widgets.grid.CGridView', hooks()->applyFilters('grid_view_properties', [
            'ajaxUrl'           => createUrl($controller->getRoute()),
            'id'                => $model->getModelName() . '-grid',
            'dataProvider'      => $model->search(),
            'filter'            => $model,
            'filterPosition'    => 'body',
            'filterCssClass'    => 'grid-filter-cell',
            'itemsCssClass'     => 'table table-hover',
            'selectableRows'    => 0,
            'enableSorting'     => false,
            'cssFile'           => false,
            'pagerCssClass'     => 'pagination pull-right',
            'pager'             => [
                'class'         => 'CLinkPager',
                'cssFile'       => false,
                'header'        => false,
                'htmlOptions'   => ['class' => 'pagination'],
            ],
            'columns' => hooks()->applyFilters('grid_view_columns', [
                [
                    'name'  => 'ip_address',
                    'value' => '$data->getDisplayIpAddress()',
                    'type'  => 'raw',
                ],
                [
                    'name'  => 'action',
                    'value' => 'ucfirst(t("app", $data->action))',
                    'filter'=> $model->getActionsList(),
                ],
                [
                    'name'  => 'reason',
                    'value' => '$data->reason',
                ],
                [
                    'name'  => 'status',
                    'value' => 'ucfirst(t("app", $data->status))',
                    'filter'=> $model->getStatusesList(),
                ],
                [
                    'name'  => 'date_added',
                    'value' => '$data->dateAdded',
                    'filter'=> false,
                ],
                [
                    'name'  => 'last_updated',
                    'value' => '$data->lastUpdated',
                    'filter'=> false,
                ],
                [
                    'class'     => 'DropDownButtonColumn',
                    'header'    => t('app', 'Options'),
                    'footer'    => $model->paginationOptions->getGridFooterPagination(),
                    'buttons'   => [
                        'toggle-status' => [
                            'label'     => IconHelper::make('glyphicon-transfer'),
                            'url'       => 'createUrl("campaigns_tracking_ignore_list/toggle_status", array("id" => $data->id))',
                            'imageUrl'  => null,
                            'options'   => ['title' => t('app', 'Toggle status'), 'class' => 'btn btn-primary btn-flat toggle-status'],
                            'visible'   => 'AccessHelper::hasRouteAccess("campaigns_tracking_ignore_list/toggle_status")',
                        ],
                        'delete' => [
                            'label'     => IconHelper::make('delete'),
                            'url'       => 'createUrl("campaigns_tracking_ignore_list/delete", array("id" => $data->id))',
                            'imageUrl'  => null,
                            'visible'   => 'AccessHelper::hasRouteAccess("campaigns_tracking_ignore_list/delete")',
                            'options'   => ['title' => t('app', 'Delete'), 'class' => 'btn btn-danger btn-flat delete'],
                        ],
                    ],
                    'headerHtmlOptions' => ['style' => 'text-align: right'],
                    'footerHtmlOptions' => ['align' => 'right'],
                    'htmlOptions'       => ['align' => 'right', 'class' => 'options'],
                    'template'          => '{toggle-status} {delete}',
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
        'controller'  => $controller,
        'renderedGrid'=> $collection->itemAt('renderGrid'),
    ]));
    ?>
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
    'controller'        => $controller,
    'renderedContent'   => $viewCollection->itemAt('renderContent'),
]));
