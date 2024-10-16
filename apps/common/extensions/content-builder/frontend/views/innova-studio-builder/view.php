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
?>
<?php
hooks()->doAction('ext_content_builder_frontend_before_view_content', $collection = new CAttributeCollection([
    'controller' => $controller,
]));
?>

<div class="container">
    <?php
    hooks()->doAction('ext_content_builder_frontend_view_content', $collection = new CAttributeCollection([
        'controller' => $controller,
    ]));
    ?>
</div>
<?php
hooks()->doAction('ext_content_builder_frontend_after_view_content', $collection = new CAttributeCollection([
    'controller' => $controller,
]));
