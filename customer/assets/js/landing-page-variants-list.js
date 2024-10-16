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
jQuery(document).ready(function($){

	let ajaxData = {};
	if ($('meta[name=csrf-token-name]').length && $('meta[name=csrf-token-value]').length) {
			const csrfTokenName = $('meta[name=csrf-token-name]').attr('content');
			const csrfTokenValue = $('meta[name=csrf-token-value]').attr('content');
			ajaxData[csrfTokenName] = csrfTokenValue;
	}

	$(document).on('click', 'a.landing-page-variant-action', function() {
		const $this = $(this);

		if (($this.hasClass('delete') || $this.hasClass('revert')) && !confirm($this.data('confirm'))) {
			return false;
		}
		$.post($this.attr('href'), ajaxData, function() {
			window.location.reload();
		});

		return false;
	});


});
