/**
 * This file is part of the MailWizz EMA application.
 * 
 * @package MailWizz EMA
 * @author MailWizz Development Team <support@mailwizz.com> 
 * @link https://www.mailwizz.com/
 * @copyright MailWizz EMA (https://www.mailwizz.com)
 * @license https://www.mailwizz.com/license/
 * @since 2.2.9
 */
jQuery(document).ready(function($){
	
	if ($('#api-key-permissions-list-wrapper').length) {
		$(document).on('change', '#CustomerApiKey_enable_permissions', function() {
			if ($(this).val() === 'yes') {
				$('#api-key-permissions-list-wrapper').show();
			} else {
				$('#api-key-permissions-list-wrapper').hide();
			}
		});
		$('#CustomerApiKey_enable_permissions').trigger('change');
		$('#api-key-permissions-list-wrapper .select-all').on('click', function() {
			$('#api-key-permissions-list-wrapper input[name="permissions[]"').each(function() {
				$(this).attr('checked', true);
			});
			return false;
		});
		$('#api-key-permissions-list-wrapper .select-none').on('click', function() {
			$('#api-key-permissions-list-wrapper input[name="permissions[]"').each(function() {
				$(this).attr('checked', false);
			});
			return false;
		});
	}
});