/**
 * This file is part of the MailWizz EMA application.
 *
 * @package MailWizz EMA
 * @author MailWizz Development Team <support@mailwizz.com>
 * @link https://www.mailwizz.com/
 * @copyright MailWizz EMA (https://www.mailwizz.com)
 * @license https://www.mailwizz.com/license/
 * @since 2.4.2
 */
jQuery(document).ready(function($){

    let chart;

    $(document).on('change', '.campaign-delivery-stats-wrapper #campaign_delivery_stats_ranges', function() {
        $('#campaign-delivery-stats-wrapper-content').hide();
        $('#campaign-delivery-stats-wrapper-loader').show();

        const $this = $(this);
        const data = $.extend({}, {
            range: $this.val()
        }, ajaxData);

        $.post($this.data('url'), data, function(json) {
            $('#campaign-delivery-stats-wrapper-loader').hide();
            $('#campaign-delivery-stats-wrapper-content').show();

            if (!json.chartData || !json.chartOptions) {
                return;
            }

            if (!chart) {
                chart = new Chart($('#campaign-delivery-stats-chart'), {
                    type: 'bar',
                    data: json.chartData,
                    options: json.chartOptions
                });
            }

            chart.data = json.chartData;
            chart.options = json.chartOptions;
            chart.update();
        }, 'json');
    });

    $('.campaign-delivery-stats-wrapper #campaign_delivery_stats_ranges').find('option:first').attr('selected', true);
    $('.campaign-delivery-stats-wrapper #campaign_delivery_stats_ranges').trigger('change');
});
