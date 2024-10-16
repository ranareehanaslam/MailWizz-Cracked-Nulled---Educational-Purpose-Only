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
?>

<script type="text/javascript">
    jQuery(document).ready(function ($) {
        (function () {
            // Show the edit input for title when dbl-clicking the page title
            const $titleWrapper = $('.top-bar-left-items-block-title');
            const initialTitle = $titleWrapper.html();
            const $titleEdit = $('input.title-input');
            const $formControlsWrapper = $('.form-controls-wrapper');
            const $floatingNav = $('#content-builder-floating-nav');

            // Do not detect content changes, so we can submit the form and collect errors from other fields
            const $saveForm = $('#content-builder-save-form');
            $saveForm.on('contentBuilder.contentChanged', function (e) {
                $(this).data('contentChanged', true);
            });

            $titleWrapper.on('dblclick', function () {
                $formControlsWrapper.toggle();
                $floatingNav.addClass('nav-show');
            })

            // Hide the title edit input when clicking outside
            $(document).mouseup(function (e) {

                // if the target of the click isn't the container nor a descendant of the container
                if (!$formControlsWrapper.is(e.target) && $formControlsWrapper.has(e.target).length === 0 && !$formControlsWrapper.find('.errorMessage').length && $formControlsWrapper.is(':visible')) {
                    let title = $titleEdit.val();
                    if (!title) {
                        title = initialTitle;
                    }
                    $titleWrapper.html(title); // we update the selected one title
                    $formControlsWrapper.hide();
                    $('.errorMessage').hide();
                    $titleWrapper.show();
                    $floatingNav.removeClass('nav-show');
                }
            });
        })();
    });
</script>

