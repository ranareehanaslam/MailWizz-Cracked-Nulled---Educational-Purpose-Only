/**
 * This file is part of the MailWizz EMA application.
 * 
 * @package MailWizz EMA
 * @author MailWizz Development Team <support@mailwizz.com> 
 * @link https://www.mailwizz.com/
 * @copyright MailWizz EMA (https://www.mailwizz.com)
 * @license https://www.mailwizz.com/license/
 * @since 2.3.9
 */
jQuery(document).ready(function($){
    
    const handleListFieldsButtonsDisplay = function() {
        const fieldsCount = $('.list-fields .field-row').length;
        const maxFields = parseInt($('.list-fields-buttons').data('max-list-fields') || -1);

        if (maxFields > -1 && fieldsCount >= maxFields) {
            $('.list-fields-buttons').hide();
        } else {
            $('.list-fields-buttons').show();
        }
    };
    
    $(document).on('click', '.list-fields .field-row .panel-footer .btn-danger', handleListFieldsButtonsDisplay);
    
    $('.list-fields-buttons a').each(function() {
        $(this).on('click', handleListFieldsButtonsDisplay);
    });
});