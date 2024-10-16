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

/** @var ExtensionInit $extension */
$extension = $controller->getData('extension');

/** @var Campaign $campaign */
$customer = $controller->getData('customer');

/** @var array $listsArray */
$listsArray = $controller->getData('listsArray');

?>

<div class="content-builder-customer-lists-forms-wrapper">
    <div class="row clearfix">
        <div class="column full">
            <div>
                <label for="lists-forms-dropdown{id}"><?php echo $extension->t('Please select the subscribe form'); ?></label>
                <div class="mt-1">
                    <?php echo CHtml::dropDownList('lists_forms_dropdown{id}', !empty($selectedList) ? $selectedList->list_uid : '', $listsArray, [
                        'id'       => 'lists-forms-dropdown{id}',
                        'class'    => 'lists-forms-dropdown w-full px-2 py-3 text-base border rounded',
                        'data-url' => $extension->createUrl('content_builder/customer_lists_forms'),
                    ]); ?>
                </div>
            </div>
        </div>
    </div>
</div>


