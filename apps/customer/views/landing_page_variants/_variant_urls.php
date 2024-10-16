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

/** @var LandingPageRevisionVariant $variant */
$variant = $controller->getData('variant');

/** @var array $contentUrls */
$contentUrls = $controller->getData('contentUrls');

/** @var LandingPageUrl $landingPageUrlModel */
$landingPageUrlModel = $controller->getData('landingPageUrlModel');

/** @var LandingPageUrl[] $variantUrlModels */
$variantUrlModels = $controller->getData('variantUrlModels');

/** @var CActiveForm $form */
$form = $controller->beginWidget('CActiveForm', [
    'id' => 'landing-page-save-urls-form',
]); ?>

<div class="">
    <div class="box-header">
        <div class="pull-left">
            <?php BoxHeaderContent::make(BoxHeaderContent::LEFT)
                ->add('<h3 class="box-title">' . t('landing_pages', 'Please select the items that you want to track as conversion goals') . '</h3>')
                ->render(); ?>
        </div>
        <div class="clearfix"><!-- --></div>
    </div>
    <div class="box-body">
        <?php if (!empty($variantUrlModels)) { ?>
            <?php foreach ($variantUrlModels as $urlModel) { ?>
                <div class="row">
                    <div class="col-lg-12">
                        <div class="item">
                            <?php echo CHtml::checkBox($landingPageUrlModel->getModelName() . '[]', !empty($urlModel->url_id), ['value' => $urlModel->destination]); ?>
                            <span class="pt-1 pl-1"><?php echo html_encode($urlModel->destination); ?></span>
                        </div>
                    </div>
                </div>
            <?php } ?>
        <?php } else {
                    echo t('landing_pages', 'Currently there are no items that can be used as conversion goals.');
                }?>
        <div class="clearfix"><!-- --></div>
    </div>
    <?php if (!empty($variantUrlModels)) { ?>
        <div class="box-footer">
            <div class="pull-right">
                <button type="submit" class="btn btn-primary btn-flat"><?php echo IconHelper::make('save') . '&nbsp;' . t('app', 'Save changes'); ?></button>
            </div>
            <div class="clearfix"><!-- --></div>
        </div>
    <?php } ?>
</div>
<?php
$controller->endWidget();
?>

<script type="text/javascript">
    jQuery(document).ready(function ($) {
        (function () {
            const $modal = $('#landing-page-urls-modal');
            $(document).on('submit', '#landing-page-save-urls-form', function() {
                $modal.trigger('hide.bs.modal');
                const $this = $(this);
                $.post($this.attr('action'), $this.serialize(), function (html) {
                    $modal.find('.modal-body-loader').hide();
                    $modal.find('.modal-body-content').html(html).show();
                    $('.landing-page-urls-modal-close-button').click();
                }, 'html');
                return false;
            });
        })();
    });
</script>
