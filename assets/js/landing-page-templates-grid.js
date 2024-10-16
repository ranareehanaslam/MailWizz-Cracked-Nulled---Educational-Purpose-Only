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
    const $previewImageContainer = $('.landing-page-template-preview-image');

    $(document).on('click', '.landing-page-template-thumbnail-container', function () {
        const $this = $(this);

        // Remove the class active from all, so the previous selected will not be active
        $('.landing-page-template-thumbnail-container').removeClass('active');
        // Add the active class for the clicked one
        $this.addClass('active');

        const contentUrl = $this.data('content-url');
        const templateId = $this.data('template-id');
        const templateTitle = $this.data('template-title');

        // Shows the preview box and the spinner
        if (!$this.data('blank-template')) {
            $previewImageContainer.show();
            $('.landing-page-template-preview-container').removeClass('box').addClass('box');
            // Change the image src
            $previewImageContainer.attr('src', contentUrl);
        } else {
            $previewImageContainer.hide();
            $('.landing-page-template-preview-container').removeClass('box');
        }

        // Send the selected template details to the form
        $('#LandingPageRevision_template_id').val(templateId);
        $('#LandingPageRevision_title').val(templateTitle);
    })
});
