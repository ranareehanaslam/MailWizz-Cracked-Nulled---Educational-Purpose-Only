<?php declare(strict_types=1);
defined('MW_PATH') or exit('No direct script access allowed');

/** @var Controller $controller */
$controller = controller();

/** @var string $builderId */
$builderId = (string)$controller->getData('builderId');

/** @var string $modelName */
$modelName = (string)$controller->getData('modelName');

/** @var CustomerEmailTemplate $model */
$model = $controller->getData('model');

/** @var array $options */
$options = (array)$controller->getData('options');

?>

<div id="builder_<?php echo html_encode($builderId); ?>" style="display: none" class="email-template-builder-editor">
    <textarea name="<?php echo html_encode($modelName); ?>[content_html]" id="<?php echo html_encode($builderId); ?>_html" style="display: none"></textarea>
    <textarea name="<?php echo html_encode($modelName); ?>[content_css]" id="<?php echo html_encode($builderId); ?>_css" style="display: none"></textarea>

    <div class="row">
        <div class="col-lg-4">
            <div id="<?php echo html_encode($options['settingsId']); ?>"></div>
        </div>
        <div class="col-lg-8">
            <div id="<?php echo html_encode($options['previewId']); ?>"></div>
        </div>
    </div>
</div>

<script type="text/javascript">
    jQuery(document).ready(function($){
        (function(){
            const params = {
                builderId       : '<?php echo html_encode($builderId); ?>',
                options         : <?php echo CJavaScript::encode($options); ?>
            };

            const $textAreaHtml = $('#' + params.builderId + '_html');
            const $textAreaCss = $('#' + params.builderId + '_css');
            const $textAreaContent = $('#' + params.builderId);

            const builderHandler = new TemplateBuilderHandler(params);

            $('#builder_<?php echo html_encode($builderId); ?>').data('templateBuilderHandler', builderHandler);

            if (builderHandler.shouldOpen()) {
                builderHandler.open();
            }

            $(document).on('click', '#btn_' + params.builderId, () => {
                builderHandler.toggle();
                return false;
            });

            const $form = $('#btn_' + params.builderId).closest('form');

            const commitEditorChange = async () => {
                const html = await builderHandler.getHtml();
                const css = await builderHandler.getCss();
                const fullHtml = await builderHandler.getFullHtml();
                $textAreaHtml.val(html);
                $textAreaCss.val(css);
                $textAreaContent.val(fullHtml);
                CKEDITOR.instances[params.builderId].setData(fullHtml);
            };

            const submitHandler = async (e) => {
                e.preventDefault();

                await commitEditorChange();

                const $clickedSubmitButton = (e.originalEvent && e.originalEvent.submitter) ? $(e.originalEvent.submitter) : null;
                if ($clickedSubmitButton) {
                    $form.append(`<input type="hidden" name="is_next" id="is_next" value="${$clickedSubmitButton.attr('value')}"/>`);
                }

                $form.off('submit', submitHandler);
                $form.submit();
            }

            $('#builder_<?php echo html_encode($builderId); ?>').on('templateBuilderHandler.beforeOpen', () => {
                $form.on('submit', submitHandler);
            });

            $('#builder_<?php echo html_encode($builderId); ?>').on('templateBuilderHandler.afterOpen', async () => {
                await commitEditorChange();
            });

            $('#builder_<?php echo html_encode($builderId); ?>').on('templateBuilderHandler.afterClose', async () => {
                await commitEditorChange();

                $form.off('submit', submitHandler);
            });

        })()
    });
</script>
