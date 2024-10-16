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

/** @var LandingPageRevision $lastRevision */
$lastRevision = $controller->getData('lastRevision');

/** @var LandingPageAddVariantForm $addVariantForm */
$addVariantForm = $controller->getData('addVariantForm');

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
        <div class="box-header border-bottom-none">
            <div class="pull-left">
                <?php BoxHeaderContent::make(BoxHeaderContent::LEFT)
                    ->add('<h3 class="box-title">' . IconHelper::make('fa-clipboard') . html_encode((string)$pageHeading) . '</h3>')
                    ->addIf(
                        '<span class="badge badge-light-success">' . t('landing_pages', 'Published') . '</span>',
                        $page->getIsPublished()
                    )
                    ->addIf(
                        '<span class="badge badge-light-primary">' . t('landing_pages', 'Unpublished') . '</span>',
                        !$page->getIsPublished()
                    )
                    ->addIf(
                        '<div class="unpublished-banner unpublished-banner-primary"><span>' . t(
                            'landing_pages',
                            'This page has unpublished changes'
                        ) . '</span></div>',
                        $page->getIsPublished() && $page->getHasUnpublishedChanges()
                    )
                    ->render(); ?>
            </div>
            <div class="pull-right">
                <?php BoxHeaderContent::make(BoxHeaderContent::RIGHT)
                    ->add(CHtml::link(
                        IconHelper::make('view') . t('app', 'Preview'),
                        $page->getPermalink(),
                        ['target' => '_blank', 'class' => 'btn btn-primary btn-flat', 'title' => t('app', 'Preview')]
                    ))
                    ->addIf(
                        CHtml::link(
                            IconHelper::make('fa-play') . t('app', 'Publish'),
                            ['landing_pages/publish', 'id' => $page->getHashId()],
                            ['class' => 'btn btn-primary btn-flat', 'title' => t('app', 'Publish')]
                        ),
                        $page->getIsUnpublished()
                    )
                    ->addIf(
                        CHtml::link(
                            IconHelper::make('fa-pause') . t('app', 'Unpublish'),
                            ['landing_pages/unpublish', 'id' => $page->getHashId()],
                            ['class' => 'btn btn-primary btn-flat', 'title' => t('app', 'Unpublish')]
                        ),
                        $page->getIsPublished()
                    )
                    ->addIf(
                        CHtml::link(
                            IconHelper::make('fa-play') . t('app', 'Republish'),
                            ['landing_pages/publish', 'id' => $page->getHashId()],
                            ['class' => 'btn btn-primary btn-flat', 'title' => t('app', 'Republish')]
                        ),
                        $page->getHasUnpublishedChanges() && $page->getIsPublished()
                    )
                    ->add(CHtml::link(
                        IconHelper::make('update') . t('app', 'Update'),
                        ['landing_pages/update', 'id' => $page->getHashId()],
                        ['class' => 'btn btn-primary btn-flat', 'title' => t('app', 'Update')]
                    ))
                    ->add(CHtml::link(
                        IconHelper::make('refresh') . t('app', 'Refresh'),
                        ['landing_pages/overview', 'id' => $page->getHashId()],
                        ['class' => 'btn btn-primary btn-flat', 'title' => t('app', 'Refresh')]
                    ))
                    ->add(CHtml::link(
                        IconHelper::make('back') . t('app', 'Back'),
                        ['landing_pages/index'],
                        ['class' => 'btn btn-primary btn-flat', 'title' => t('app', 'Back')]
                    ))
                    ->add(CHtml::link(IconHelper::make('info'), '#landing-page-info', [
                        'class'       => 'btn btn-primary btn-flat no-spin',
                        'title'       => t('app', 'Info'),
                        'data-toggle' => 'modal',
                    ]))
                    ->render(); ?>
            </div>
        </div>
        <div class="row items-center">
            <div class="col-lg-6">
                <div class="input-group input-margin">
                    <?php echo CHtml::tag(
                        'span',
                        ['class' => 'input-group-addon permalink first-addon'],
                        IconHelper::make('fa-link')
                    ); ?>
                    <?php echo CHtml::dropDownList('LandingPage_domain_id', $page->domain_id, $page->getDomainsArray(), [
                        'class'    => 'form-control select2',
                        'id'       => 'landing-page-domain-select',
                        'data-url' => createUrl('landing_pages/save_domain', ['id' => $page->getHashId()]),
                    ]); ?>

                    <?php echo CHtml::tag('span', [
                        'class'               => 'input-group-addon permalink landing-page-permalink-copy',
                        'data-original-title' => t('landing_pages', 'Copy to clipboard'),
                    ], IconHelper::make('fa-copy')); ?>
                    <?php echo CHtml::link(IconHelper::make('fa-eye'), $page->getPermalink(), [
                        'class'               => 'input-group-addon permalink bg-success',
                        'id'                  => 'landing-page-preview-button',
                        'target'              => '_blank',
                        'data-original-title' => t('landing_pages', 'Click to view the page'),
                    ]); ?>
                </div>
                <div class="unpublished-banner unpublished-banner-primary banner-domain-warning-page-unpublished" style="display: <?php echo $page->domain_id && $page->getIsUnpublished() ? 'block' : 'none'; ?>">
                    <span><?php echo t('landing_pages', 'Publish the page to use the custom domain!'); ?></span>
                </div>
            </div>

            <div class="col-lg-2">
                <div class="numbers text-center">
                    <p class="p-gray"><?php echo t('landing_pages', 'Visitors'); ?></p>
                    <h4 class="mb-0">
                        <?php echo formatter()->formatNumber($page->visitors_count); ?>
                    </h4>
                </div>
            </div>

            <div class="col-lg-2">
                <div class="numbers text-center">
                    <p class="p-gray"><?php echo t('landing_pages', 'Views'); ?></p>
                    <h4 class="font-weight-bolder mb-0">
                        <?php echo formatter()->formatNumber($page->views_count); ?>
                    </h4>
                </div>
            </div>

            <div class="col-lg-2">
                <div class="numbers text-center">
                    <p class="p-gray"><?php echo t('landing_pages', 'Conversions'); ?></p>
                    <h4 class="font-weight-bolder mb-0">
                        <?php echo formatter()->formatNumber($page->conversions_count); ?>
                    </h4>
                </div>
            </div>
        </div>
    </div>
    <div class="add-page-variant">
        <?php BoxHeaderContent::make()
            ->add(CHtml::link(
                t('app', 'Add Variant'),
                '#landing-page-overview-add-variant-modal',
                ['class' => 'btn btn-primary', 'title' => t('app', 'Add Variant'), 'data-toggle' => 'modal']
            ))
            ->render();
        ?>
    </div>
    <div class="variants-wrapper"
         data-url="<?php echo createUrl('landing_page_variants/index', ['page_id' => $page->getHashId()]); ?>"></div>

    <div class="modal fade" id="landing-page-overview-add-variant-modal" tabindex="-1" role="dialog"
         aria-labelledby="landing-page-overview-add-variant-modal" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                    <h4 class="modal-title"><?php echo t(
            'landing_pages',
            'How would you like to create this variant?'
        ); ?></h4>
                </div>
                <div class="modal-body">
                    <?php
                    /** @var CActiveForm $form */
                    $form = $controller->beginWidget('CActiveForm', [
                        'action' => ['landing_page_variants/create', 'id' => $page->getHashId()],
                        'id'     => 'landing-page-overview-add-variant-form',
                    ]);
                    ?>

                    <?php echo $form->radioButton($addVariantForm, 'choice', [
                        'id'           => 'add-variant-duplicate-radio',
                        'value'        => LandingPageAddVariantForm::CHOICE_DUPLICATE,
                        'uncheckValue' => null,
                    ]); ?>
                    <label for="add-variant-duplicate-radio"><?php echo t(
                        'landing_pages',
                        'Duplicate an existing variant'
                    ); ?></label><br>
                    <div class="row add-variant-option"
                         id="landing-page-overview-add-variant-duplicate" <?php echo !$addVariantForm->getIsDuplicate() ? 'style="display: none"' : ''; ?>>
                        <div class="col-lg-12">
                            <div class="form-group">
                                <?php echo $form->dropDownList(
                        $addVariantForm,
                        'variant_id',
                        $lastRevision->getVariantsDropDownArray(),
                        $addVariantForm->fieldDecorator->getHtmlOptions('variant_id', [
                                        'prompt' => t('landing_pages', 'Please choose a variant'),
                                    ])
                    ); ?>
                                <?php echo $form->error($addVariantForm, 'variant_id'); ?>
                            </div>
                        </div>
                    </div>

                    <?php echo $form->radioButton($addVariantForm, 'choice', [
                        'id'           => 'add-variant-duplicate-scratch-radio',
                        'value'        => LandingPageAddVariantForm::CHOICE_SCRATCH,
                        'uncheckValue' => null,
                    ]); ?>
                    <label for="add-variant-duplicate-scratch-radio"><?php echo t(
                        'landing_pages',
                        'Start from scratch'
                    ); ?></label><br>

                    <?php echo $form->radioButton($addVariantForm, 'choice', [
                        'id'           => 'add-variant-duplicate-template-radio',
                        'value'        => LandingPageAddVariantForm::CHOICE_TEMPLATE,
                        'uncheckValue' => null,
                    ]); ?>
                    <label for="add-variant-duplicate-template-radio"><?php echo t(
                        'landing_pages',
                        'Start with a template'
                    ); ?></label>

                    <div class="row add-variant-option"
                         id="landing-page-overview-add-variant-template" <?php echo !$addVariantForm->getIsTemplate() ? 'style="display: none"' : ''; ?>>
                        <div class="col-lg-12">
                            <div class="form-group">
                                <?php echo $form->labelEx($addVariantForm, 'template_id'); ?>
                                <?php echo $form->dropDownList(
                        $addVariantForm,
                        'template_id',
                        LandingPageHelper::getTemplatesAsOptions(),
                        $addVariantForm->fieldDecorator->getHtmlOptions('template_id')
                    ); ?>
                                <?php echo $form->error($addVariantForm, 'template_id'); ?>
                            </div>
                        </div>
                    </div>

                    <?php $controller->endWidget(); ?>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default btn-flat" data-dismiss="modal">
                        <?php echo t('app', 'Close'); ?>
                    </button>
                    <button type="button" class="btn btn-primary btn-flat"
                            onclick="$('#landing-page-overview-add-variant-form').submit(); return false;"
                    >
                        <?php echo IconHelper::make('fa-save') . '&nbsp;' . t('app', 'OK'); ?>
                    </button>
                </div>
            </div>
        </div>
    </div>
    <div class="modal modal-info fade" id="landing-page-info" tabindex="-1" role="dialog">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                    <h4 class="modal-title"><?php echo IconHelper::make('info') . t('app', 'Info'); ?></h4>
                </div>
                <div class="modal-body">
                    <p><?php echo t(
                        'landing_pages',
                        'The new Landing pages feature has at its roots a very solid revisions system. That being said, some clarifications about its inner mechanism are required.'
                    ); ?></p>
                    <p><?php echo t(
                        'landing_pages',
                        'Why do we need a revision system in the first place? Imagine that you have a published version of your page and you want to make some changes. It would be incorrect that these changes to reflect directly in the published version. Also the revision system is allowing you to move back and forth between page versions.'
                    ); ?></p>
                    <p><?php echo t(
                        'landing_pages',
                        'Let\'s break it down into some concepts. A page might have more REVISIONS. Only one is the published one, and you will always have available to edit the last revision.'
                    ); ?></p>
                    <p><?php echo t(
                        'landing_pages',
                        'A revision is a collection of VARIANTS. At the end of the day, a variant content is the one that will appear in the frontend and it will be available for edit.'
                    ); ?></p>
                    <p><?php echo t(
                        'landing_pages',
                        'Consider a revision as a snapshot of the page at a certain moment. Snapshot that is defined by the variants it has.'
                    ); ?></p>
                    <p><?php echo t(
                        'landing_pages',
                        'A very important aspect is WHEN we will create a new revision for a page? Here are the main events:'
                    ); ?></p>
                    <p><?php echo t(
                        'landing_pages',
                        'When you edit and save the title and/or the content of a variant.'
                    ); ?></p>
                    <p><?php echo t('landing_pages', 'When you add a variant to the page.'); ?></p>
                    <p><?php echo t('landing_pages', 'When you delete a variant from the page.'); ?></p>
                    <p><?php echo t('landing_pages', 'When you make a variant active/inactive.'); ?></p>
                    <p><?php echo t('landing_pages', 'When you duplicate a variant.'); ?></p>
                    <p><?php echo t(
                        'landing_pages',
                        'When a new revision is created, you will always see flags saying something like: "This page has unpublished changes".'
                    ); ?></p>
                    <p><?php echo t(
                        'landing_pages',
                        'Another important things to consider are the counters. An unpublished page or variant will not record visits/views/conversions.'
                    ); ?></p>
                    <p><?php echo t(
                        'landing_pages',
                        'The counters that you see on the variant are the ones of the published variant. A variant that is not yet published will always have its counters 0.'
                    ); ?></p>
                    <p><?php echo t(
                        'landing_pages',
                        'When re-publishing a page, the last edited variant will become publicly visible, and we will reset its counters so you can see how it behaves. In any case the page will keep track of the counters from the beginning.'
                    ); ?></p>
                </div>
            </div>
        </div>
    </div>
<?php }
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
