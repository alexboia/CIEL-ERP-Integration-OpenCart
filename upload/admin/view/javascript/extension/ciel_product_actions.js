(function($) {
	"use strict";

	var DEFAULT_MSG_CONNECT_PRODUCT_SUCCESS = 'The product has been successfully connected to NextUp ERP. This page will automatically reload in 5 seconds.';
	var DEFAULT_MSG_CONNECT_PRODUCT_ERROR = 'The product could not be connected to NextUp ERP.';

	var DEFAULT_MSG_UPDATE_PRODUCT_SUCCESS = 'The product has been successfully updated. This page will automatically reload in 5 seconds.';
	var DEFAULT_MSG_UPDATE_PRODUCT_ERROR = 'The product could not be updated.';

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

	function _doAction(actionUrl, defaultSuccessMessage, defaultErrorMessage) {
		$.showCielLoading();

		$.ajax(actionUrl, {
			type: 'POST',
			dataType: 'json',
			cache: false,
			data: {}
		}).done(function(data, status, xhr) {
			$.hideCielLoading();
			if (data && !!data.success) {
				_showSuccess(data.message || defaultSuccessMessage);
				_delayedReloadPage(5);
			} else {
				_showError(data.message || defaultErrorMessage);	
			}
		}).fail(function(xhr, status, error) {
			$.hideCielLoading();
			_showError(defaultErrorMessage);
		});
	}

	function _connectProductToCielErp() {
		var $me = $(this);
		var actionUrl = _getActionUrl($me);
		_doAction(actionUrl,
			DEFAULT_MSG_CONNECT_PRODUCT_SUCCESS,
			DEFAULT_MSG_CONNECT_PRODUCT_ERROR
		);
	}

	function _syncProductInformation() {
		var $me = $(this);
		var actionUrl = _getActionUrl($me);
		_doAction(actionUrl,
			DEFAULT_MSG_UPDATE_PRODUCT_SUCCESS,
			DEFAULT_MSG_UPDATE_PRODUCT_ERROR
		);
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