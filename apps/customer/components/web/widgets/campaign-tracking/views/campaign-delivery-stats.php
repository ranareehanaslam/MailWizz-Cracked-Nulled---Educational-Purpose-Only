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
 * @since 2.4.2
 */

/** @var string $dataUrl */
/** @var array $dataExportUrl */
/** @var array $dateRanges */

?>

<div class="box box-primary borderless campaign-delivery-stats-wrapper">
    <div id="campaign-delivery-stats-wrapper-content">
        <div class="box-header">
            <div class="pull-left">
                <h3 class="box-title">
                    <?php echo IconHelper::make('fa-clock-o') . t('campaigns', 'Campaign delivery stats'); ?>
                </h3>
            </div>
            <div class="pull-right">
                <?php BoxHeaderContent::make(BoxHeaderContent::RIGHT)
                    ->add(CHtml::link(IconHelper::make('export') . t('app', 'Export'), $dataExportUrl, ['target' => '_blank', 'class' => 'btn btn-primary btn-flat', 'title' => t('app', 'Export')]))
                    ->render(); ?>
            </div>
            <div class="clearfix"><!-- --></div>
        </div>
        <div class="box-body">
            <div class="row">
                <div class="col-lg-12">
                    <div class="pull-right">
                        <?php echo CHtml::dropDownList('campaign_delivery_stats_ranges', '', $dateRanges, [
                            'data-url' => $dataUrl,
                        ]); ?>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-lg-12">
                    <div style="width: 100%; height: 400px">
                        <canvas id="campaign-delivery-stats-chart" style="position: relative; height:40vh; width:80vw"></canvas>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div id="campaign-delivery-stats-wrapper-loader" style="display: none">
        <div class="box borderless">
            <div class="box-body">
                <div class="row">
                    <div class="ph-item">
                        <div class="ph-col-12">
                            <div class="ph-row">
                                <div class="ph-col-2 big"></div>
                                <div class="ph-col-10 empty big"></div>
                            </div>
                        </div>
                        <div class="ph-col-12">
                            <div class="ph-picture"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
