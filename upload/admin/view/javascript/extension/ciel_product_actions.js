(function($) {
	"use strict";

	function _getActionUrl($target) {
		return $.getCielActionUrl($target);
	}

	function _showError(message) {
		$('#myc_product_operation_status_message')
			.cielOperationStatus('show', 
				false, 
				message);
	}

	function _showSuccess(message) {
		$('#myc_product_operation_status_message')
			.cielOperationStatus('show', 
				true, 
				message);
	}

	function _delayedReloadPage(timeoutSeconds) {
		$.delayedReloadCielPage(timeoutSeconds);
	}

	function _doAction(actionUrl, successMessage, errorMessage) {
		$.showCielLoading();

		$.ajax(actionUrl, {
			type: 'POST',
			dataType: 'json',
			cache: false,
			data: {}
		}).done(function(data, status, xhr) {
			$.hideCielLoading();
			if (data && !!data.success) {
				_showSuccess(successMessage);
				_delayedReloadPage(5);
			} else {
				_showError(data.message || errorMessage);	
			}
		}).fail(function(xhr, status, error) {
			$.hideCielLoading();
			_showError(errorMessage);
		});
	}

	function _connectProductToCielErp() {
		var $me = $(this);
		var actionUrl = _getActionUrl($me);
		_doAction(actionUrl,
			'Produsul a fost conectat cu succes la CIEL ERP. Pagina se va reincarca in 5 secunde.',
			'Produsul nu a putut fi conectat la CIEL ERP.');
	}

	function _syncProductInformation() {
		var $me = $(this);
		var actionUrl = _getActionUrl($me);
		_doAction(actionUrl,
			'Produsul a fost actualizat cu succes. Pagina se va reincarca in 5 secunde.',
			'Produsul nu a putut fi actualizat.');
	}

	$(document).ready(function() {
		$(document).on('click', 
			'#myc_connect_product_to_ciel_erp', 
			_connectProductToCielErp);

		$(document).on('click', 
			'#myc_sync_all_product_information', 
			_syncProductInformation);
		$(document).on('click', 
			'#myc_only_stock_product_information', 
			_syncProductInformation);
	});
})(jQuery);