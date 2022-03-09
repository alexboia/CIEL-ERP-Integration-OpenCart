(function($) {
	"use strict";

	function _getActionUrl($target) {
		return $.getCielActionUrl($target);
	}

	function _showError(message) {
		$('#myc_order_operation_status_message')
			.cielOperationStatus('show', 
				false, 
				message);
	}

	function _showSuccess(message) {
		$('#myc_order_operation_status_message')
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
			console.log(data);
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

	function _issueCielErpDocument() {
		var $me = $(this);
		var actionUrl = _getActionUrl($me);
		_doAction(actionUrl,
			'Documentul a fost emis cu succes. Pagina se va reincarca in 5 secunde.',
			'Document nu a putut fi emis.');
	}

	function _deleteCielErpDocument() {
		var $me = $(this);
		var actionUrl = _getActionUrl($me);
		_doAction(actionUrl,
			'Documentul a fost sters cu succes. Pagina se va reincarca in 5 secunde.',
			'Document nu a putut fi sters.');
	}

	$(document).ready(function() {
		$(document).on('click', 
			'#myc_connect_issue_ciel_erp_document', 
			_issueCielErpDocument);

		$(document).on('click', 
			'#myc_connect_remove_ciel_erp_document', 
			_deleteCielErpDocument);
	});
})(jQuery);