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

/** @var LandingPageTemplate[] $templates */

?>
<div class="col-lg-6 landing-page-templates-container">
    <div class="box">
        <div class="box-header">
            <div class="pull-left">
                <?php BoxHeaderContent::make(BoxHeaderContent::LEFT)
                    ->add('<h5 class="box-title">' . t('landing_pages', 'Please choose a template') . '</h5>')
                    ->render(); ?>
            </div>
        </div>
        <div class="box-body">
            <?php foreach ($templates as $template) { ?>
                <div class="col-xs-3 hover-mask animation landing-page-template-thumbnail-container"
                     data-content-url="<?php echo html_encode($template->getScreenshotSrc()); ?>"
                     data-template-id="<?php echo (int)$template->template_id; ?>"
                     data-template-title="<?php echo html_encode($template->title); ?>"
                     data-blank-template="<?php echo (int)$template->getIsBlank(); ?>"
                >
                    <div class="overlay-item overlay-effect">
                        <?php echo CHtml::image($template->getScreenshotSrc(), $template->title, [
                            'class' => 'landing-page-template-link',
                            'title' => $template->title,
                        ]); ?>
                    </div>
                    <h5 class="text-center"><?php echo html_encode(StringHelper::truncateLength($template->title, 12)); ?></h5>
                </div>
            <?php } ?>

        </div>
    </div>
</div>

