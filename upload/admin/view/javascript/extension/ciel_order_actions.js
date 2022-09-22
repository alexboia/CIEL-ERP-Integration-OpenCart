(function($) {
	"use strict";

	var _isCielDocumentIssued = false;
	var _cielErpCheckDocumentIssuedAction = null;

	function _initState() {
		_isCielDocumentIssued = !!window['myc_is_ciel_document_issued'];
		_cielErpCheckDocumentIssuedAction = (window['myc_ciel_erp_check_document_issued_action'] || '')
			.replace(/&amp;/g, '&');
	}

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

	function _isOrderHistoryAjaxRequest(opts) {
		return !!opts.url 
			&& opts.url.indexOf('index.php?route=api/order/history') >= 0
			&& !!opts.type 
			&& opts.type.toLowerCase() == 'post';
	}

	function _handleAjaxRequestCompleted(event, xhr, opts) {
		if (_isOrderHistoryAjaxRequest(opts)) {
			_checkCielDocumentIssued();
		}
	}

	function _checkCielDocumentIssued() {
		$.ajax(_cielErpCheckDocumentIssuedAction, {
			type: 'GET',
			dataType: 'json',
			cache: false,
			data: {}
		}).done(function(data, status, xhr) {
			if (_isSuccessfulCielDocumentCheckResponse(data)) {
				var successMessage = null;
				var isCielDocumentIssued = !!data.isCielDocumentIssued;

				if (isCielDocumentIssued != _isCielDocumentIssued) {
					_isCielDocumentIssued = isCielDocumentIssued;
					if (_isCielDocumentIssued) {
						successMessage = 'Documentul a fost emis cu succes. Pagina se va reincarca in 5 secunde.';
					} else {
						successMessage = 'Documentul a fost sters cu succes. Pagina se va reincarca in 5 secunde.';
					}

					_showSuccess(successMessage);
					_delayedReloadPage(5);
				}
			} else {
				//do nothing
			}
		}).fail(function(xhr, status, error) {
			//do nothing
		});
	}

	function _isSuccessfulCielDocumentCheckResponse(data) {
		return data 
			&& !!data.success 
			&& data.isCielDocumentIssued != undefined 
			&& data.isCielDocumentIssued !== null;
	}

	function _initEvents() {
		$(document).on('click', 
			'#myc_connect_issue_ciel_erp_document', 
			_issueCielErpDocument);

		$(document).on('click', 
			'#myc_connect_remove_ciel_erp_document', 
			_deleteCielErpDocument);

		$(document).ajaxComplete(_handleAjaxRequestCompleted);
	}

	$(document).ready(function() {
		_initState();
		_initEvents();
	});
})(jQuery);