/**
 * This file is part of the MailWizz EMA application.
 *
 * @package MailWizz EMA
 * @author MailWizz Development Team <support@mailwizz.com>
 * @link https://www.mailwizz.com/
 * @copyright MailWizz EMA (https://www.mailwizz.com)
 * @license https://www.mailwizz.com/license/
 * @since 2.5.3
 */
jQuery(document).ready(function($){

    const ajaxData = {};
    if ($('meta[name=csrf-token-name]').length && $('meta[name=csrf-token-value]').length) {
        const csrfTokenName = $('meta[name=csrf-token-name]').attr('content');
        const csrfTokenValue = $('meta[name=csrf-token-value]').attr('content');
        ajaxData[csrfTokenName] = csrfTokenValue;
    }

    $(document).on('click', '.announcements-wrapper .announcement-item .announcement-read-button', function () {
        const $this = $(this);

        const url = $this.data('url')
        if (!url) {
            return;
        }

        if (!confirm($(this).data('message'))) {
            return false;
        }

        $this.closest('.announcement-item').remove();
        if ($('.announcements-wrapper .announcement-item').length === 0) {
            $('.announcements-wrapper').remove();
        }

        $.post(url, ajaxData);

        return false;
    });
});
