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

/** @var LandingPageRevisionVariant $variant */
$variant = $controller->getData('variant');

?>
<li>
    <?php BoxHeaderContent::make(BoxHeaderContent::LEFT)
        ->add(CHtml::link(t('app', 'Preview'), $variant->getPermalink(), ['target' => '_blank']))
        ->render();
    ?>
</li>
