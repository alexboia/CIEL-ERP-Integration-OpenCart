(function($) {
	"use strict";

	var $ctlMigrateInfoStats = null;
	var $ctlTotalEligibleCount = null;
	var $ctlTotalUpdatedCount = null;
	var $ctlTotalNotFoundCount = null;
	var $ctlBtnDownloadNotFound = null;
	var $ctlNotFoundIdsField = null;

	function _getMigrateInformationUrl($target) {
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
		$ctlMigrateInfoStats.hide();
		$ctlBtnDownloadNotFound.hide();
	}

	function _showInfoStats(results) {
		$ctlTotalEligibleCount.text(results.eligible);
		$ctlTotalUpdatedCount.text(results.updated);
		$ctlTotalNotFoundCount.text(results.notFound);
		$ctlMigrateInfoStats.show();
		if (results.notFound > 0) {
			$ctlBtnDownloadNotFound.show();
		}
	}

	function _storeDetailedInformation(connected, notFound) {
		var idsTxt = _buildNotFoundIdsTxt(notFound);
		$ctlNotFoundIdsField.val(idsTxt);
	}

	function _resetDetailedInformation() {
		$ctlNotFoundIdsField.val('');
	}

	function _buildNotFoundIdsTxt(notFound) {
		var items = [];
		for (var i = 0; i < notFound.length; i ++) {
			var item = notFound[i];
			items.push(item.id);
		}
		return items.join(';');
	}

	function _migrateProductInformation() {
		_hideInfoStats();
		_resetDetailedInformation();
		$.showCielLoading();
		$.ajax(_getMigrateInformationUrl($(this)), {
			type: 'POST',
			dataType: 'json',
			cache: false,
			data: {}
		}).done(function(data, status, xhr) {
			$.hideCielLoading();
			if (data && data.success) {
				_showInfoStats(data.result);
				_storeDetailedInformation(data.connected, data.notFound);
				_showSuccess(data.message || 'Product information successfully migrated.');
			} else {
				_showError(data.message || 'Product information could not be migrated.');
			}
		}).fail(function(xhr, status, error) {
			$.hideCielLoading();
			_showError('Product information could not be migrated.');
		});
	}

	function _initControls() {
		$ctlMigrateInfoStats = $('#myc-migrate-info-stats');
		$ctlTotalEligibleCount = $('#myc-total-eligible');
		$ctlTotalUpdatedCount = $('#myc-total-updated');
		$ctlTotalNotFoundCount = $('#myc-total-notFound');
		$ctlBtnDownloadNotFound = $('#myc-download-notFound');
		$ctlNotFoundIdsField = $('#myc-notFound-ids');
	}

	function _initEvents() {
		$(document).on('click', 
			'#myc-migrate-start', 
			_migrateProductInformation);
	}

	$(document).ready(function() {
		_initControls();
		_initEvents();
	});
})(jQuery);