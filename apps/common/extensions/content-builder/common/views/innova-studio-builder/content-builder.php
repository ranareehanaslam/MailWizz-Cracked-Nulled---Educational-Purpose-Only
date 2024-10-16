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

/** @var array $options */
$options = (array)$controller->getData('options');

/** @var string $uploadHandlerUrl */
$uploadHandlerUrl = (string)$controller->getData('uploadHandlerUrl');
?>

<div class="content-builder-top-bar">
    <!-- BEGIN TOP BAR -->
    <?php hooks()->doAction('ext_content_builder_before_content_builder_form', $collection = new CAttributeCollection([
        'controller'           => $controller,
        'formAction'           => '',
        'formModelContentName' => '',
        'formModelContent'     => '',
    ]));
    /** @var CActiveForm $form */
    $form = $controller->beginWidget('CActiveForm', [
        'method' => 'post',
        'action' => $collection->itemAt('formAction'),
        'id'     => 'content-builder-save-form',
    ]);

    echo CHtml::hiddenField(
        $collection->itemAt('formModelContentName'),
        $collection->itemAt('formModelContent'),
        ['id' => 'form-model-content-input-id']
    );
    ?>
    <div id="pre-header" class="pre-header mr-75">
        <div class="stroke">
            <div class="row display-center">
                <!-- BEGIN TOP BAR LEFT PART -->
                <div class="col-md-6 col-sm-6 top-bar-left-items-block-info">
                    <ul class="list-unstyled list-inline">
                        <?php hooks()->doAction('ext_content_builder_top_bar_left_items', $collection = new CAttributeCollection([
                            'controller' => $controller,
                            'form'       => $form,
                        ])); ?>
                        <!-- END LANGS -->
                    </ul>
                </div>
                <!-- END TOP BAR LEFT PART -->

                <!-- BEGIN TOP BAR MENU -->
                <div id="pre-header-additional" class="col-md-6 col-sm-6 pre-header-additional">
                    <div class="horz-list">
                        <ul>
                            <li>
                                <?php echo CHtml::tag('button', [
                                    'class'       => '',
                                    'id'          => 'page-info-button',
                                    'data-toggle' => 'modal',
                                    'data-target' => '#page-info',
                                    'type'        => 'button',
                                ], t('app', 'Info')); ?>
                            </li>
                            <li>
                                <?php echo CHtml::tag('button', [
                                    'class' => 'btn-show-html-source',
                                    'type'  => 'button',
                                ], t('ext_content_builder', 'Source')); ?>
                            </li>
                            <?php hooks()->doAction(
                                    'ext_content_builder_top_bar_right_items',
                                    $collection = new CAttributeCollection([
                                    'controller' => $controller,
                                    'form'       => $form,
                                ])
                                );
                            ?>
                            <li>
                                <?php echo CHtml::tag('button', [
                                    'class'                             => 'content-builder-save-button',
                                    'data-no-changes-notification-text' => t(
                                        'ext_content_builder',
                                        'No changes detected. Nothing to save.'
                                    ),
                                    'type'                              => 'button',
                                ], t('app', 'Save')); ?>
                            </li>
                        </ul>
                    </div>
                </div>
                <!-- END TOP BAR MENU -->
            </div>
        </div>
    </div>
    <!-- END TOP BAR -->
    <?php hooks()->doAction(
                                    'ext_content_builder_before_form_end',
                                    $collection = new CAttributeCollection([
            'controller' => $controller,
            'form'       => $form,
        ])
                                );
    ?>
    <?php $controller->endWidget('content-builder-save-form'); ?>
</div>
<div class="container content-builder-container" style="min-height: 300px">
    <?php hooks()->doAction('ext_content_builder_add_container_content', $collection = new CAttributeCollection([
        'controller' => $controller,
    ])); ?>
</div>
<div class="content-builder-panel">
    <div class="content-changes-notify" style="display: none">
        <span>
            <?php echo t('ext_content_builder', 'There are unsaved changes'); ?>
        </span>
        <button type="button" class="btn small btn-success content-builder-save-button-small">
            <i class="fa fa-save"></i><?php echo t('app', 'Save'); ?>
        </button>
    </div>
    <?php hooks()->doAction('ext_content_builder_add_panel_items', $collection = new CAttributeCollection([
        'controller' => $controller,
    ])); ?>
</div>

<?php
hooks()->doAction('ext_content_builder_after_view_file_content', $collection = new CAttributeCollection([
    'controller' => $controller,
]));
?>

<!-- modals -->
<div class="modal modal-info fade" id="page-info" tabindex="-1" role="dialog">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                <h4 class="modal-title"><?php echo IconHelper::make('info') . '&nbsp;' . t('app', 'Info'); ?></h4>
            </div>
            <div class="modal-body">
                <?php
                $text = 'Please note, that for html content not created with the builder, you might not be able to edit it. To correct this, you will need to click the Source button and wrap the un-editable content into a div with the class row. ';
                echo t('ext_content_builder', StringHelper::normalizeTranslationString($text)); ?>
                <br /><br />
                <code>
                    <?php echo html_encode('<div class="row"><div class="col-lg-12">YOUR CONTENT</div></div>'); ?>
                </code>
            </div>
        </div>
    </div>
</div>


<script type="text/javascript">
    jQuery(document).ready(function ($) {
        (function () {
            function trimEmptyLines(content) {
                return content.replace(/(?:(?:\r\n|\r|\n)\s*){2}/gm, '');
            }

            let ajaxData = {};
            if ($('meta[name=csrf-token-name]').length && $('meta[name=csrf-token-value]').length) {
                const csrfTokenName = $('meta[name=csrf-token-name]').attr('content');
                ajaxData[csrfTokenName] = $('meta[name=csrf-token-value]').attr('content');
            }

            const builder = new ContentBuilder(<?php echo CJavaScript::encode($options); ?>);
            const $formModelContentInput = $('#form-model-content-input-id');

            // Used in the onChange callback of the builder that is coming from the options
            const initialContent = trimEmptyLines($formModelContentInput.val());
            let contentChanged = false;

            const $saveForm = $('#content-builder-save-form');
            const $saveButton = $('.content-builder-save-button');
            const $panelSaveButton = $('.content-builder-save-button-small');

            // Prevent submitting the form with enter
            $saveForm.bind('keypress keydown keyup', function(e){
                if (e.keyCode === 13) {
                    e.preventDefault();
                }
            });

            // When clicking the save form button we want to save the images that are inserted by the builder as base64
            // and after saving them, we submit the form to the server
            $saveButton.on('click', function () {
                const $this = $(this);
                if ($this.data('running')) {
                    return false;
                }
                $this.data('running', true);

                $saveForm.data('contentChanged', contentChanged);

                // Trigger event so we can hook from other extensions as needed.
                $saveForm.trigger('contentBuilder.contentChanged', [contentChanged]);

                // If no changes in the content we don't send the data to the server
                if (!$saveForm.data('contentChanged')) {
                    notify.remove().addInfo($this.data('no-changes-notification-text')).show();
                    $this.data('running', false);
                    return false;
                }

                // Add the spinners on both save buttons
                $('i', $this).removeAttr('class').addClass('fa fa-spinner fa-spin');
                $('i', $panelSaveButton).removeAttr('class').addClass('fa fa-spinner fa-spin');

                builder.saveImages('', () => {
                    // Image saving done. Then you can save the content
                    $formModelContentInput.val(trimEmptyLines(builder.html()));
                    $saveForm.submit();
                }, (img, base64, filename) => {
                    // Upload base64 images
                    let reqBody = {image: base64, filename: filename};

                    reqBody = $.extend({}, ajaxData, reqBody);

                    $.post('<?php echo $uploadHandlerUrl; ?>', reqBody, function (response) {
                        if (!response.error && response.success && response.fileUrl) {
                            const uploadedImageUrl = response.fileUrl;
                            img.setAttribute('src', uploadedImageUrl); // Update image src
                        }
                        if (response.error) {
                            notify.hide().addError(response.error).show();
                        }
                    }, 'json');
                });
            });

            // Handle the click for saving the content
            $panelSaveButton.on('click', function () {
                $('i', $(this)).removeAttr('class').addClass('fa fa-spinner fa-spin');
                $saveButton.trigger('click');

            })
            // Template builder show snippets sidebar
            if (builder && builder.snippetOpen) {
                $('body').removeClass('cb-snippets-opened').addClass('cb-snippets-opened');
            }

            const pbx = $('.pre-header');
            const notifyContainer = $('#notify-container');
            const divSnippetHandle = $('#divSnippetHandle');
            const isOpen = $('body').hasClass('cb-snippets-opened');

            handleIsOpen(isOpen);

            function handleIsOpen(isOpen) {
                if (isOpen) {
                    pbx.addClass('mr-200');
                    notifyContainer.addClass('mr-230');
                } else {
                    pbx.removeClass('mr-200');
                    notifyContainer.removeClass('mr-230');
                }
            }

            function handleSnippetClick() {
                const isOpen = $('body').hasClass('cb-snippets-opened');
                const contentBuilderSnippetsListStatus = isOpen ? 'closed' : 'opened';

                if (typeof Cookies === 'function') {
                    Cookies.set('content_builder_snippets_list_status', contentBuilderSnippetsListStatus, { expires: 365 });
                    $('body').toggleClass('cb-snippets-opened');
                }

                handleIsOpen(!isOpen);
            }

            divSnippetHandle.click(handleSnippetClick);

            $('.btn-show-html-source').click(function () {
                if(!builder) {
                    return;
                }
                builder.viewHtml();
            });
        })();
    });
</script>
