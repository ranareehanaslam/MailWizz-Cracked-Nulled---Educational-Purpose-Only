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

/** @var Article $article */
$article = $controller->getData('article');

?>
<?php if (!$article->getIsNewRecord()) {?>
<li>
    <?php BoxHeaderContent::make(BoxHeaderContent::LEFT)
        ->add(CHtml::link(t('app', 'Preview'), $article->getPermalink(), ['target' => '_blank']))
        ->render();
    ?>
</li>
<?php } ?>
