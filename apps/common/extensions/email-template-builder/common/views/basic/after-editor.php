<?php declare(strict_types=1);
if (!defined('MW_PATH')) {
    exit('No direct script access allowed');
}

/** @var Controller $controller */
$controller = controller();

/** @var string $builderId */
$builderId = (string)$controller->getData('builderId');

/** @var string $modelName */
$modelName = (string)$controller->getData('modelName');

/** @var array $json */
$json = (array)$controller->getData('json');

/** @var array $options */
$options = (array)$controller->getData('options');

/** @var EmailTemplateBuilderExt $extension */
$extension = $controller->getData('extension');

?>
<div id="builder_<?php echo html_encode($builderId); ?>" class="email-template-builder-editor"></div>
<textarea name="<?php echo html_encode($modelName); ?>[content_json]" id="<?php echo html_encode($builderId); ?>_json" style="display: none"></textarea>
<script>
    jQuery(document).ready(function($){
        (function(){
            const params = {
                builderId : '<?php echo html_encode($builderId); ?>',
                options   :  <?php echo json_encode($options); ?>,
                json      :  <?php echo json_encode($json); ?>
            };

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
                $('#<?php echo html_encode($builderId); ?>_json').val(builderHandler.getJson());
                CKEDITOR.instances['<?php echo html_encode($builderId); ?>'].setData(builderHandler.getHtml());
            };

            $('#btn_' + params.builderId).closest('form').on('submit', () => {
                if (builderHandler.isEnabled()) {
                    commitEditorChange();
                }
            });

            $('#builder_<?php echo html_encode($builderId); ?>').on('templateBuilderHandler.afterClose', () => {
                commitEditorChange();
            });
        })()
    });
</script>

<div class="modal modal-info fade" id="page-info-toggle-template-builder" tabindex="-1" role="dialog">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                <h4 class="modal-title"><?php echo IconHelper::make('info') . t('app', 'Info'); ?></h4>
            </div>
            <div class="modal-body">
				<?php echo $extension->t('The template you create with the builder must be modified only using the builder.'); ?><br />
                <?php echo $extension->t('Do not modify the template outside the builder, it will break.'); ?>
            </div>
        </div>
    </div>
</div>
