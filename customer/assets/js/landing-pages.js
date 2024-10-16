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

    var ajaxData = {};
    if ($('meta[name=csrf-token-name]').length && $('meta[name=csrf-token-value]').length) {
        var csrfTokenName = $('meta[name=csrf-token-name]').attr('content');
        var csrfTokenValue = $('meta[name=csrf-token-value]').attr('content');
        ajaxData[csrfTokenName] = csrfTokenValue;
    }

    $('#LandingPageRevision_title').on('blur', function () {
        var $this = $(this);
        if ($this.val() != '' && $('#LandingPage_slug').val() == '') {
            var formData = {
                string: $this.val(),
                page_id: $this.data('page-id')
            };
            formData = $.extend({}, formData, ajaxData);
            $.post($this.data('slug-url'), formData, function (json) {
                if (json.result == 'success') {
                    $('#LandingPage_slug').val(json.slug).closest('.slug-wrapper').fadeIn();
                }
            }, 'json');
        }
    });

    $('#LandingPage_slug').on('blur', function () {
        const $this = $(this);
        let formData = {
            string: ($this.val() != '' ? $this.val() : $('#LandingPageRevision_title').val()),
            page_id: $('#LandingPageRevision_title').data('page-id')
        };

        formData = $.extend({}, formData, ajaxData);
        $.post($('#LandingPageRevision_title').data('slug-url'), formData, function (json) {
            if (json.result == 'success') {
                $this.val(json.slug).closest('.slug-wrapper').fadeIn();
            }
        }, 'json');
    });

    (function () {
        const $els = [
            $('.variants-wrapper'),
        ];
        $els.map(function ($el) {
            if (!$el.length) {
                return;
            }

            $.get($el.data('url'), {}, function (json) {
                $el.html(json.html);
            }, 'json');
        })
    })();

    (function () {
        $("input[name$='LandingPageAddVariantForm[choice]']").click(function () {
            let action = $(this).val();

            $("div.add-variant-option").hide();
            $("div.add-variant-option").find('select').val('');

            $("#landing-page-overview-add-variant-" + action).show();
        });
    })();

    $('.landing-page-permalink-copy').on('click', function () {
        const textArea = document.createElement("textarea");
        textArea.value = $('#landing-page-preview-button').attr('href');
        document.body.appendChild(textArea);
        textArea.select();

        try {
            document.execCommand('copy');
        } catch (err) {
        }
        document.body.removeChild(textArea);
    });

    $(document).on('change', '#landing-page-domain-select', function() {
        const $this = $(this);
        if ($this.data('running')) {
            return false;
        }
        $this.data('running', true);
        const data = $.extend({}, {
            domain_id: $this.val()
        }, ajaxData);

        $.post($this.data('url'), data, function(json) {
            $this.data('running', false);
            if (json.success) {
                notify.remove().addSuccess(json.message).show();
            } else {
                notify.remove().addError(json.message).show();
            }

            if (data.domain_id && json.page_status === 'unpublished') {
                $('.banner-domain-warning-page-unpublished').show();
            } else {
                $('.banner-domain-warning-page-unpublished').hide();
            }

            $('#landing-page-preview-button').attr('href', json.permalink);
        }, 'json');
    });
});
