(function($) {
	"use strict";

	var $ctlCheckImportAllRemoteIds = null;
	var $ctlImportNewProductsForm = null;

	var _atLeastOneSucceeded = false;
	var _remotArticleIdsImportQueue = [];

	var _messages = {
		import_new_products_global_success_msg: null,
		import_new_products_global_error_msg: null
	};

	function _initState() {
		_messages = {
			import_new_products_global_success_msg: 
				window['myc_ciel_import_new_products_global_success_msg'],
			import_new_products_global_error_msg: 
				window['myc_ciel_import_new_products_global_error_msg']
		}	
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

	function _isAtLeastOneSucceeded() {
		return _atLeastOneSucceeded;
	}

	function _resetLastImportOperationResult() {
		_atLeastOneSucceeded = false;
	}

	function _hasMoreProductsToImport() {
		return ('.myc_import_remote_ids').size() > 0;
	}

	function _addImportOperationBatchResult(success, results) {
		_atLeastOneSucceeded = _atLeastOneSucceeded || success;
		if (!!results) {
			for (var remoteId in results) {
				if (results.hasOwnProperty(remoteId)) {
					var result = results[remoteId];
					if (!!result.success) {
						_removeSuccessfulImportedProductRow(remoteId);
					} else {
						_showImportProductErrorRowMessage(remoteId, result);
					}
				}
			}
		}
	}

	function _removeSuccessfulImportedProductRow(remoteId) {
		var $targetRow = _getImportedProductRow(remoteId);
		$targetRow.remove();
	}

	function _getImportedProductRow(remoteId) {
		return $('#myc_remote_product_row-' + remoteId);
	}

	function _showImportProductErrorRowMessage(remoteId, result) {
		var $targetRow = _getImportedProductRow(remoteId);
		var $errorRow = $(_constructImportProductErrorRowMessageHtml(remoteId, 
			result));

		$errorRow.insertAfter($targetRow);
	}

	function _constructImportProductErrorRowMessageHtml(remoteId, result) {
		return [
			'<tr id="myc_remote_product_row_error-' + remoteId + '" class="myc-row-error myc-import-product-error-row">',
				'<td colspan="5">',
					'<div class="alert alert-danger" role="alert">',
						result.message,
					'</div>',
				'</td>',
			'</tr>'
		].join('');
	}

	function _removeAllImportProductErrorRows() {
		$('.myc-import-product-error-row').remove();
	}

	function _getNewRemoteProductsCount() {
		return window['myc_new_remote_products_count'] != undefined
			? window['myc_new_remote_products_count']
			: 0;
	}

	function _getSelectedRemoteArticleElements() {
		return $('input[name="import_remote_ids[]"]:checked');
	}

	function _getSelectedRemoteArticleElementsCount() {
		return _getSelectedRemoteArticleElements().size();
	}

	function _getSelectedRemoteArticleIdsToImport() {
		var ids = [];
		var $elements = _getSelectedRemoteArticleElements();
		
		if ($elements.size()) {
			$elements.each(function(index, el) {
				var $el = $(el);
				var id = parseInt($el.val());
				ids.push(id);
			});
		}

		return ids;
	}

	function  _setRemotArticleIdsImportQueue(ids) {
		_remotArticleIdsImportQueue = ids;
	}

	function _getRemoteIdsFromQueue() {
		var removeCount = Math.min(3, _remotArticleIdsImportQueue.length);
		return _remotArticleIdsImportQueue.splice(0, removeCount);
	}

	function _importNewProductsBatch(remoteIds, onReady) {
		var postData = _serializeRemoteIds(remoteIds);
		var url = $ctlImportNewProductsForm
			.attr('action');

		$.ajax(url, {
			type: 'POST',
			dataType: 'json',
			cache: false,
			data: postData
		}).done(function(data, status, xhr) {
			if (data && data.success) {
				_addImportOperationBatchResult(true, data.results || []);
			} else {
				_addImportOperationBatchResult(false, data.results || []);
			}

			onReady();
		}).fail(function(xhr, status, error) {
			_addImportOperationBatchResult(false, null);
			onReady();
		});
	}

	function _serializeRemoteIds(remoteIds) {
		var parts = [];
		for (var i = 0; i < remoteIds.length; i ++) {
			parts.push('import_remote_ids[]=' + remoteIds[i]);
		}
		return parts.join('&');
	}

	function _processRemoteProductsQueue(onReady) {
		var remoteIds = _getRemoteIdsFromQueue();
		if (remoteIds.length > 0) {
			_importNewProductsBatch(remoteIds, function() {
				_processRemoteProductsQueue(onReady);
			});
		} else {
			onReady();
		}
	}

	function _startImportingRemoteProductsFromQueue() {
		$.showCielLoading();
		_resetLastImportOperationResult();
		_removeAllImportProductErrorRows();
		_processRemoteProductsQueue(function() {
			$.hideCielLoading();
			if (_isAtLeastOneSucceeded()) {
				_showSuccess(_messages.import_new_products_global_success_msg);
			} else {
				_showError(_messages.import_new_products_global_error_msg);
			}

			if (!_hasMoreProductsToImport()) {
				$ctlImportNewProductsForm.hide();
			}
		});
	}

	function _handleImportNewProductsStart() {
		var ids = _getSelectedRemoteArticleIdsToImport();
		if (ids.length > 0) {
			_setRemotArticleIdsImportQueue(ids);
			_startImportingRemoteProductsFromQueue();
		}
	}

	function _handleCheckImportAlLRemoteIdsChanged() {
		var checkAll = $ctlCheckImportAllRemoteIds
			.is(':checked');

		$('input[name="import_remote_ids[]"]')
			.each(function(idx, el) {
				var $el = $(el);
				if (checkAll) {
					$el.prop('checked', true);
				} else {
					$el.prop('checked', false);
				}
			});
	}

	function _handleRemoteArticleElementChanged() {
		if (_getSelectedRemoteArticleElementsCount() == _getNewRemoteProductsCount()) {
			$ctlCheckImportAllRemoteIds.prop('checked', true);
		} else {
			$ctlCheckImportAllRemoteIds.prop('checked', false);
		}
	}

	function _initControls() {
		$ctlImportNewProductsForm = $('#myc-importNewProducts-form');
		$ctlCheckImportAllRemoteIds = $('#myc_import_remote_ids_all');
	}

	function _initEvents() {
		$(document).on('click', 
			'#myc_import_remote_ids_all', 
			_handleCheckImportAlLRemoteIdsChanged);

		$(document).on('click', 
			'input[name="import_remote_ids[]"]', 
			_handleRemoteArticleElementChanged);

		$(document).on('click', 
			'#myc-import-products-start', 
			_handleImportNewProductsStart);
	}

	$(document).ready(function() {
		_initState();
		_initControls();
		_initEvents();
	});
})(jQuery);