(function($) {
	"use strict";

	var $ctlCheckImportAllRemoteIds = null;
	var $ctlImportNewProductsForm = null;

	var _atLeastOneSucceeded = false;
	var _remotArticleIdsImportQueue = [];

	function _isAtLeastOneSucceeded() {
		return _atLeastOneSucceeded;
	}

	function _resetLastImportOperationResult() {
		_atLeastOneSucceeded = false;
	}

	function _addImportOperationBatchResult(success, results) {
		_atLeastOneSucceeded = _atLeastOneSucceeded || success;
		if (success) {

		}
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
				_addImportOperationBatchResult(true, data.results);
			} else {
				_addImportOperationBatchResult(false, null);
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
		_processRemoteProductsQueue(function() {
			$.hideCielLoading();
			if (_isAtLeastOneSucceeded()) {

			} else {

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
		_initControls();
		_initEvents();
	});
})(jQuery);