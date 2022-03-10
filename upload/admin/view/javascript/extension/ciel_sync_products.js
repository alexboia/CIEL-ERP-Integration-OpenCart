(function($) {
	"use strict";

	var $ctlSyncInfoStats = null;
	var $ctlTotalEligibleCount = null;
	var $ctlTotalUpdatedCount = null;

	function _getSyncInformationUrl($target) {
		return $.getCielActionUrl($target);
	}

	function _showError(message) {
		$('#myc_operation_status_message')
			.cielOperationStatus('show', 
				false, 
				message);
	}

	function _showSuccess(message) {
		$('#myc_operation_status_message')
			.cielOperationStatus('show', 
				true, 
				message);
	}

	function _hideInfoStats() {
		$ctlSyncInfoStats.hide();
	}

	function _showInfoStats(results) {
		$ctlTotalEligibleCount.text(results.eligible);
		$ctlTotalUpdatedCount.text(results.updated);
		$ctlSyncInfoStats.show();
	}

	function _syncInformation() {
		var $target = $(this);
		_hideInfoStats();
		$.showCielLoading();
		$.ajax(_getSyncInformationUrl($target), {
			type: 'POST',
			dataType: 'json',
			cache: false,
			data: {}
		}).done(function(data, status, xhr) {
			$.hideCielLoading();
			if (data && data.success) {
				_showInfoStats(data.result);
				_showSuccess(data.message || 'Product information successfully updated.');
			} else {
				_showError(data.message || 'Product information could not be updated.');
			}
		}).fail(function(xhr, status, error) {
			$.hideCielLoading();
			_showError('Product information could not be updated.');
		});
	}

	function _initTooltips() {
		$('[data-toggle=\'tooltip\']').tooltip({
			container: 'body',
			html: true
		});
	}

	function _initControls() {
		$ctlSyncInfoStats = $('#myc-sync-info-stats');
		$ctlTotalEligibleCount = $('#myc-total-eligible');
		$ctlTotalUpdatedCount = $('#myc-total-updated');
	}

	function _initEvents() {
		$(document).on('click', 
			'#myc-sync-start', 
			_syncInformation);
	}

	$(document).ready(function() {
		_initControls();
		_initTooltips();
		_initEvents();
	});
})(jQuery);