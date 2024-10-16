/**
 * This file is part of the MailWizz EMA application.
 *
 * @package MailWizz EMA
 * @author MailWizz Development Team <support@mailwizz.com>
 * @link https://www.mailwizz.com/
 * @copyright MailWizz EMA (https://www.mailwizz.com)
 * @license https://www.mailwizz.com/license/
 * @since 1.3.5.5
 */
jQuery(document).ready(function($){

	var ajaxData = {};
	if ($('meta[name=csrf-token-name]').length && $('meta[name=csrf-token-value]').length) {
			var csrfTokenName = $('meta[name=csrf-token-name]').attr('content');
			var csrfTokenValue = $('meta[name=csrf-token-value]').attr('content');
			ajaxData[csrfTokenName] = csrfTokenValue;
	}

	$(document).on('click', 'a.pause-sending, a.unpause-sending', function() {
		if (!confirm($(this).data('message'))) {
			return false;
		}
		$.post($(this).attr('href'), ajaxData, function(){
			window.location.reload();
		});
		return false;
	});

    $(document).on('click', 'a.approve', function() {
        if (!confirm($(this).data('message'))) {
            return false;
        }
        $.post($(this).attr('href'), ajaxData, function(){
            window.location.reload();
        });
        return false;
    });

	(function() {

		var $modal = $('#disapprove-campaign-modal');
		$modal.on('hide.bs.modal', function(){
			$modal.find('textarea[name=disapprove-message]').val('');
			$modal.data('url', '');
		});

		$(document).on('click', 'a.disapprove', function() {
			if (!confirm($(this).data('message'))) {
				return false;
			}
			$modal.data('url', $(this).attr('href'));
			$modal.modal('show');

			return false;
		});

		var resetTextareaErrors = function() {
			var $textarea = $('#disapprove-campaign-modal textarea[name=disapprove-message]');
			$textarea.removeClass('error');
			$textarea.closest('div').find('.errorMessage').hide();
		};

		$(document).on('focus', '#disapprove-campaign-modal textarea[name=disapprove-message]', resetTextareaErrors);

		$(document).on('click', '#disapprove-campaign-modal .btn-disapprove-campaign', function() {
			var $this = $(this);
			if ($this.data('running')) {
				return false;
			}
			$this.data('running', true);

			var $textarea = $('#disapprove-campaign-modal textarea[name=disapprove-message]');
			resetTextareaErrors();

			var message = $textarea.val();
			if (!message || message.length < 5) {
				$textarea.addClass('error');
				$textarea.closest('div').find('.errorMessage').show();
				$this.data('running', false);
				return false;
			}

			$textarea.attr('disabled');
			$this.find('i').removeAttr('class').addClass('fa fa-spinner fa-spin');

			var data = $.extend({}, ajaxData, {
				message: message
			});

			$.post($modal.data('url'), data, function(){
				$modal.modal('hide');
				window.location.reload();
			});

			return false;
		});
	})();

	$(document).on('click', 'a.block-sending, a.unblock-sending', function() {
		if (!confirm($(this).data('message'))) {
			return false;
		}
		$.post($(this).attr('href'), ajaxData, function(){
			window.location.reload();
		});
		return false;
	});

    $(document).on('click', 'a.resume-campaign-sending', function() {
        if (!confirm($(this).data('message'))) {
			return false;
		}
		$.post($(this).attr('href'), ajaxData, function(){
			window.location.reload();
		});
		return false;
	});

    $(document).on('click', 'a.mark-campaign-as-sent', function() {
        if (!confirm($(this).data('message'))) {
			return false;
		}
		$.post($(this).attr('href'), ajaxData, function(){
			window.location.reload();
		});
		return false;
	});

	$(document).on('click', 'a.resend-campaign-giveups', function() {
		var $this = $(this);
		if (!confirm($this.data('message'))) {
			return false;
		}
		$.post($(this).attr('href'), ajaxData, function(json){
			if (json.result === 'success') {
				notify.addSuccess(json.message);
				$this.remove();
			} else {
				notify.addError(json.message);
			}
			$('html, body').animate({scrollTop: 0}, 500);
			notify.show();
		});
		return false;
	});

    $(document).on('click', '.toggle-filters-form', function(){
        $('#filters-form').toggle();
        return false;
    });

	$(document).on('click', '#btn-run-bulk-action', function(e) {
		if ($('#bulk_action').val() === 'compare-campaigns') {
			$('#campaigns-compare-form')
				.append($('.checkbox-column input[type=checkbox]:checked').clone())
				.submit();
			$('#campaigns-compare-modal').modal('show');
			return false;
		}
	});

	(function() {
		const $els = [
			$('#campaign-overview-index-wrapper'),
			$('#campaign-overview-counter-boxes-wrapper'),
			$('#campaign-overview-rate-boxes-wrapper'),
			$('#campaign-overview-daily-performance-wrapper'),
			$('#campaign-overview-top-domains-opens-clicks-graph-wrapper'),
			$('#campaign-overview-geo-opens-wrapper'),
			$('#campaign-overview-open-user-agents-wrapper'),
			$('#campaign-overview-tracking-top-clicked-links-wrapper'),
			$('#campaign-overview-tracking-latest-clicked-links-wrapper'),
			$('#campaign-overview-tracking-latest-opens-wrapper'),
			$('#campaign-overview-tracking-subscribers-with-most-opens-wrapper'),
			$('#campaign-overview-delivery-stats-wrapper'),
		];
		$els.map(function($el) {
			if (!$el.length) {
				return;
			}

			$.get($el.data('url'), {}, function(json){
				$el.html(json.html);
			}, 'json');
		})
	})();

	// since 2.2.2
	(function(){
		window.handleCampaignGridViewRows = function() {
			const columns = ['status', 'delivered', 'opens', 'clicks', 'bounces', 'unsubs'];
			const requests = [];

			$('#Campaign-grid table tbody tr').each(function() {
				const $this = $(this);
				const campaignId = $this.data('campaign-id');
				const statsUrl   = $this.data('stats-url');
				if (!campaignId || !statsUrl) {
					return;
				}

				let exists = false;
				for (let i in columns) {
					const column = columns[i];
					if ($this.find(`.campaign-grid-view-${column}-column`).length) {
						exists = true;
						break;
					}
				}
				if (!exists) {
					return;
				}

				requests.push({
					el: $this,
					url: statsUrl,
					send: function(el, url) {
						return $.get(url, {}, function(json){
							for (let i in columns) {
								const column = columns[i];
								if (!json.hasOwnProperty(column)) {
									continue;
								}
								if (!el.find(`.campaign-grid-view-${column}-column`).length) {
									continue;
								}
								el.find(`.campaign-grid-view-${column}-column`).html(json[column]);
							}
						}, 'json');
					}
				});
			});

			if (!requests.length) {
				return;
			}

			const runInBatchesUntilDone = function() {
				if (!requests.length) {
					return;
				}
				$.when(...requests.splice(0, 10).map(function(request) {
					return request.send(request.el, request.url);
				})).done(runInBatchesUntilDone);
			};
			runInBatchesUntilDone();
		};
		if ($('#Campaign-grid table tbody tr').length) {
			handleCampaignGridViewRows();
		}
	})();
});
