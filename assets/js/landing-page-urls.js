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
jQuery(document).ready(function ($) {

    let ajaxData = {};
    if ($('meta[name=csrf-token-name]').length && $('meta[name=csrf-token-value]').length) {
        const csrfTokenName = $('meta[name=csrf-token-name]').attr('content');
        ajaxData[csrfTokenName] = $('meta[name=csrf-token-value]').attr('content');
    }

    const $modal = $('#landing-page-urls-modal');
    $modal.on('hide.bs.modal', function () {
        $modal.find('.modal-body-loader').show();
        $modal.find('.modal-body-content').html('').hide();
        $modal.data('url', '');
    }).on('shown.bs.modal', function () {
        const url = $modal.data('url');
        if (!url) {
            return false;
        }
        // const saveContentUrl = $modal.data('save-content-url');
        // if (!saveContentUrl) {
        //     return false;
        // }

        // Uncomment if we want to save the content before showing the urls tracking modal.
        // We should add back data-save-content-url to the buttons
        // $('.landing-page-update-view').trigger('variant.saveContent', [{
        //     content: $('#LandingPageRevisionVariant_content').val(),
        //     url: saveContentUrl,
        //     verbose: 0
        // }]);
        // TODO - @Cristi - help me with this
        // setTimeout(function() {
        //     $.get(url, {}, function (html) {
        //         $modal.find('.modal-body-loader').hide();
        //         $modal.find('.modal-body-content').html(html).show();
        //     }, 'html');
        // }, 1000)

        $.get(url, {}, function (html) {
            $modal.find('.modal-body-loader').hide();
            $modal.find('.modal-body-content').html(html).show();
        }, 'html');

    });

    $(document).on('click', '.btn-landing-page-urls', function () {
        $modal.data('url', $(this).data('url'));
        //$modal.data('save-content-url', $(this).data('save-content-url'));
        $modal.modal('show');
        return false;
    });
});
