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
		ajaxData[csrfTokenName] = $('meta[name=csrf-token-value]').attr('content');
	}
	// Saving only the content
	const $updateFormWrapper = $('.landing-page-update-view');
	$updateFormWrapper.on('variant.saveContent', function(e, data) {
		const $this = $(this);
		if ($this.data('running')) {
			return false;
		}
		$this.data('running', true);

		const postData = $.extend(ajaxData, {
			content: data.content
		}, ajaxData);

		$.post(data.url, postData, function(json) {
			$this.data('running', false);
			if (!data.verbose) {
				return false;
			}
			if (json.status === 'error') {
				notify.remove().addError(json.message).show();
				return false;
			}

			notify.remove().addSuccess(json.message).show();
		});
	})
});
