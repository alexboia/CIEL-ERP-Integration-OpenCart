(function($) {
	"use strict";

	function _getActionUrl($target) {
		return $target.attr('data-action-url');
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

	function _connectProductToCielErp() {
		var $me = $(this);
		var actionUrl = _getActionUrl($me);

		$.showCielLoading();

		$.ajax(actionUrl, {
			type: 'POST',
			dataType: 'json',
			cache: false,
			data: {}
		}).done(function(data, status, xhr) {
			$.hideCielLoading();
			if (data && !!data.success) {
				_showSuccess('Produsul as fost conectat cu succes la CIEL ERP.');
			} else {
				_showError(data.message || 'Produsul nu a putut fi conectat la CIEL ERP.');	
			}
		}).fail(function(xhr, status, error) {
			$.hideCielLoading();
			_showError('Produsul nu a putut fi conectat la CIEL ERP.');
		});
	}

	$(document).ready(function() {
		$(document).on('click', '#myc_connect_product_to_ciel_erp', _connectProductToCielErp);
	});
})(jQuery);