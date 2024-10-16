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
</div>

<script type="text/javascript">
    jQuery(document).ready(function($){
        (function(){
            const params = {
                builderId       : '<?php echo html_encode($builderId); ?>',
                options         : <?php echo CJavaScript::encode($options); ?>
            };

            const $textArea = $('#' + params.builderId);

            const builderHandler = new TemplateBuilderHandler(params);

            $('#builder_<?php echo html_encode($builderId); ?>').data('templateBuilderHandler', builderHandler);

            $(document).on('click', '#btn_' + params.builderId, () => {
                builderHandler.toggle();
                return false;
            });

            if (builderHandler.shouldOpen()) {
                setTimeout(function(){
                    builderHandler.open();
                }, 1000);
            }

            const commitEditorChange = () => {
                const fullHtml = builderHandler.getFullHTML();
                $textArea.val(fullHtml);
                CKEDITOR.instances[params.builderId].setData(fullHtml);
            };

            $('#btn_' + params.builderId).closest('form').on('submit', () => {
                if (builderHandler.isEnabled()) {
                    commitEditorChange();
                }
            });

            $('#builder_' + params.builderId).on('templateBuilderHandler.afterOpen', () => {
                // Avoid throwing error when using the ckeditor to edit some element
                CKEDITOR.dtd.$editable.a = 1;
                CKEDITOR.dtd.$editable.span = 1;

                const ckeditorData = CKEDITOR.instances[params.builderId].getData();
                const pattern = /<body[^>]*>(.*)<\/body>/g;
                const matches = pattern.exec(ckeditorData);
                const ckeditorHasNoNewData = (matches !== null) && matches.length && matches[1].toString().trim().length === 0;

                builderHandler.getInstance().runCommand('core:canvas-clear');

                if (ckeditorHasNoNewData) {
                    builderHandler.getInstance().editor.setComponents(params.options.defaultTemplate);
                } else {
                    builderHandler.getInstance().editor.setComponents(ckeditorData);
                }

                commitEditorChange();
            });

            $('#builder_' + params.builderId).on('templateBuilderHandler.afterClose', () => {
                commitEditorChange();
            });

            // Beautify tooltips
            $('*[title]').each(function () {
                let el = $(this);
                let title = el.attr('title').trim();
                if (!title) {
                    return;
                }
                el.attr('data-tooltip', el.attr('title'));
                el.attr('title', '');
            });
        })()
    });
</script>
