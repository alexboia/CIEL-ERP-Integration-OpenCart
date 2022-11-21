(function($) {
	"use strict";

	var DEFAULT_MSG_CONNECT_PRODUCT_SUCCESS = 'The product has been successfully connected to NextUp ERP.';
	var DEFAULT_MSG_CONNECT_PRODUCT_ERROR = 'The product could not be connected to NextUp ERP.';

	function _disableBulkConnectBtn() {
		$('#ciel-connect-selected')
			.attr('disabled', 'disabled');
	}

	function _enableBulkConnectBtn() {
		$('#ciel-connect-selected')
			.removeAttr('disabled');
	}

	function _monitorSelection() {
		$(document).on('change', 
			'#form-product input[name="selected[]"]', 
			_handleProductSelectionChanged);
		$(document).on('change', 
			'#form-product thead td input[type=checkbox]', 
			_handleProductSelectionChanged);
	}

	function _handleProductSelectionChanged() {
		var selection = _getSelectedProducts();
		if (selection.length > 0) {
			_enableBulkConnectBtn();
		} else {
			_disableBulkConnectBtn();
		}
	}

	function _getSelectedProducts() {
		var selection = [];
		var $items = $('#form-product input[name="selected[]"]');

		$items.each(function() {
			var $me = $(this);
			var value = $me.val();
			if ($me.is(':checked')) {
				selection.push({
					id: parseInt(value),
					$row: $me.parent().parent()
				});
			}
		});

		return selection;
	}

	function _clearSelectedProducts() {
		$('#form-product input[name="selected[]"]')
			.removeAttr('checked');
	}

	function _initBulkConnectBtn() {
		_disableBulkConnectBtn();
		$(document).on('click', 
			'#ciel-connect-selected', 
			_handleBulkConnectBtnClicked);
	}

	function _handleBulkConnectBtnClicked() {
		var selection = _getSelectedProducts();
		if (selection && selection.length) {
			_removeCurrentOperationMessages();
			_processSelectedProducts(selection);
		}
	}

	function _processSelectedProducts(selection) {
		$.showCielLoading();
		_processNextSelectedProduct(selection, true);
	}

	function _processNextSelectedProduct(selection, allFailed) {
		var processItem = selection.shift();
		_processSelectedProduct(processItem, function(success) {
			if (selection.length > 0) {
				_processNextSelectedProduct(selection, allFailed || success);
			} else {
				$.hideCielLoading();
				if (!allFailed) {
					_delayedReloadPage(5);
				} else {
					_clearSelectedProducts();
				}
			}
		});
	}

	function _processSelectedProduct(selectionItem, onReady) {
		var productId = selectionItem.id;
		var $productRow = selectionItem.$row;
		var connectUrl = _getProductConnectUrl(productId);

		$.ajax(connectUrl, {
			type: 'POST',
			dataType: 'json',
			cache: false,
			data: {}
		}).done(function(data, status, xhr) {
			if (data && !!data.success) {
				_showSuccess($productRow, data.message || DEFAULT_MSG_CONNECT_PRODUCT_SUCCESS);
				onReady(true);
			} else {
				_showError($productRow, data.message || DEFAULT_MSG_CONNECT_PRODUCT_ERROR);	
				onReady(false);
			}
		}).fail(function(xhr, status, error) {
			_showError($productRow, DEFAULT_MSG_CONNECT_PRODUCT_ERROR);
			onReady(false);
		});
	}

	function _showSuccess($productRow, message) {
		var $html = $(_buildRowMessageHtml(true, message));
		$html.insertAfter($productRow);
	}

	function _buildRowMessageHtml(success, message) {
		var html = [];
		var columnCount =  _determineColumnCount();

		html.push('<tr class="ciel-bulk-connect-result-container">');
		html.push('<td colspan="' + columnCount + '">');

		if (success) {
			html.push('<div class="alert alert-success"><i class="fa fa-check-circle"></i>');
		} else {
			html.push('<div class="alert alert-danger"><i class="fa fa-exclamation-circle"></i>');
		}
		
		html.push(message);
		html.push('</div>');
		html.push('</td>');
		html.push('</tr>');
		
		return html.join('');
	}

	function _determineColumnCount() {
		return $('#form-product table tr:first td').size();
	}

	function _showError($productRow, message) {
		var $html = $(_buildRowMessageHtml(false, message));
		$html.insertAfter($productRow);
	}

	function _getProductConnectUrl(productId) {
		var connectUrl = _getProductConnectUrlTemplate()
			.replace('%product_id%', productId);
		return decodeURI(connectUrl);
	}

	function _getProductConnectUrlTemplate() {
		return (window['myc_ciel_erp_connect_action_url'] || '')
			.replace(/&amp;/g, '&');
	}

	function _removeCurrentOperationMessages() {
		$('#form-product table tr.ciel-bulk-connect-result-container').remove();
	}

	function _delayedReloadPage(timeoutSeconds) {
		$.delayedReloadCielPage(timeoutSeconds);
	}

	$(document).ready(function() {
		_initBulkConnectBtn();
		_monitorSelection();
	});
})(jQuery);