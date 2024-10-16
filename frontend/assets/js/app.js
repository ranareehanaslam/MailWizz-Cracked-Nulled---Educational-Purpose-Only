/**
 * This file is part of the MailWizz EMA application.
 *
 * @package MailWizz EMA
 * @author MailWizz Development Team <support@mailwizz.com>
 * @link https://www.mailwizz.com/
 * @copyright MailWizz EMA (https://www.mailwizz.com)
 * @license https://www.mailwizz.com/license/
 * @since 1.0
 */
jQuery(document).ready(function($){

    // since 1.6.4
    $('.ctrl-lists form').on('submit', function(){
        $(this).css({opacity: .5});
    });

    // since 2.4.3
    $('.ctrl-surveys form').on('submit', function(){
        $(this).css({opacity: .5});
    });

});
