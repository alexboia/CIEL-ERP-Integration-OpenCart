(function($) {
	"use strict";

	var DEFAULT_MSG_CONNECTION_TEST_FAILED = 'The connection test failed';
	var DEFAULT_MSG_SETTINGS_SAVED_OK = 'The settings have been successfully saved.';
	var DEFAULT_MSG_SETTINGS_SAVED_OK_NEEDS_RELOAD = 'The settings have been successfully saved. The page will automatically reload in 5 seconds...';
	var DEFAULT_MSG_SETTINGS_SAVE_FAILED = 'The settings could not be saved.';
	var DEFAULT_MSG_CONFIRM_WAREHOUSE_CHANGE = 'Please confirm warehouse change! When saved, this will disconnect all your products from their corresponding NextUp ERP articles.';

	var _context = {
		hasConnectionInfo: false,
		hasConnectionError: false
	};

	var $ctlSettingsForm = null;
	var $ctlBindingWarehousesSelect = null;
	var $ctlBindingDocumentTypeSelect = null;
	var $ctlBindingShippingQuotaNameSelect = null;
	var $ctlBindingSettingsContainer = null;
	var $ctlWorkflowSettingsContainer = null;

	function _updateContext(newVals) {
		_context = $.extend({}, _context, newVals);
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

	function _clearStatusMessage() {
		$('#myc_operation_status_message')
			.cielOperationStatus('hide');
	}

	function _getConnectionTestUrl() {
		return $('#myc_test_ciel_erp_connection')
			.attr('data-test-connection-url');
	}

	function _delayedReloadPage(timeoutSeconds) {
		$.delayedReloadCielPage(timeoutSeconds);
	}

	function _getConnectionTestInputData() {
		return {
			myc_connection_endpoint_url: $('#myc_connection_endpoint_url')
				.val(),
			myc_connection_username: $('#myc_connection_username')
				.val(),
			myc_connection_password: $('#myc_connection_password')
				.val(),
			myc_connection_society_code: $('#myc_connection_society_code')
				.val(),
			myc_connection_timeout_seconds: $('#myc_connection_timeout_seconds')
				.val()
		};
	}

	function _testConnection(data) {
		$.showCielLoading();
		_clearStatusMessage();
		$.ajax(_getConnectionTestUrl(), {
			type: 'POST',
			dataType: 'json',
			cache: false,
			data: data
		}).done(function(data, status, xhr) {
			$.hideCielLoading();
			if (data && data.success) {
				_showSuccess(data.message);
			} else {
				_showError(data.message || DEFAULT_MSG_CONNECTION_TEST_FAILED);
			}
		}).fail(function(xhr, status, error) {
			$.hideCielLoading();
			_showError(DEFAULT_MSG_CONNECTION_TEST_FAILED);
		});
	}

	function _handleTestConnectionButtonClicked(event) {
		event.preventDefault();
		var data = _getConnectionTestInputData();
		_testConnection(data);
	}

	function _buildWarehouseDropdownOption(id, warehouse) {
		return [
			'<option value="' + id 
					+ '" data-code="' + warehouse.code
					+ '" data-type="' + warehouse.type 
					+ '" data-supports-sale-order="' + warehouse.properties.supportsSaleOder.toString() 
					+ '" data-supports-sale-invoice="' + warehouse.properties.supportsSaleInvoice.toString() 
				+ '">', 
				warehouse.displayLabel,
			'</option>'
		].join('');
	}

	function _buildWarehouseDropdownOptions(warehouses) {
		var options = [];
		for (var id in warehouses) {
			if (warehouses.hasOwnProperty(id)) {
				var warehouse = warehouses[id];
				options.push(_buildWarehouseDropdownOption(id, warehouse));
			}
		}
		return $(options.join(''));
	}

	function _buildShippingQuotaDropdownOption(name, value) {
		return [
			'<option value="' + name + '" data-quota-value="' + value + '">', 
				name, 
			'</option>'
		].join('');
	}

	function _buildShippingQuotaDropdownOptions(vatQuotas) {
		var options = [];
		for (var name in vatQuotas) {
			if (vatQuotas.hasOwnProperty(name)) {
				options.push(_buildShippingQuotaDropdownOption(name, vatQuotas[name]));
			}
		}
		return $(options.join(''));
	}

	function _initBindingSettingsSection(data) {
		var vatQuotas = data.vatQuotas || [];
		var warehouses = data.warehouses || [];
		
		var warehousesOptions = _buildWarehouseDropdownOptions(warehouses);
		var shippingVatQuotasOptions = _buildShippingQuotaDropdownOptions(vatQuotas);
		
		$ctlBindingWarehousesSelect.html(warehousesOptions);
		$ctlBindingShippingQuotaNameSelect.html(shippingVatQuotasOptions);
		$ctlBindingSettingsContainer.show();
		$ctlWorkflowSettingsContainer.show();

		_updateContext({
			hasConnectionInfo: true
		});
	}

	function _getSettingsInputData() {
		return $ctlSettingsForm.serialize();
	}

	function _getSettingsFormSaveUrl() {
		return $('#myc_ciel_settings_save')
			.attr('data-save-settings-url');
	}

	function _saveSettings() {
		$.showCielLoading();
		_clearStatusMessage();
		$.ajax(_getSettingsFormSaveUrl(), {
			type: 'POST',
			dataType: 'json',
			cache: false,
			data: _getSettingsInputData()
		}).done(function(data, status, xhr) {
			$.hideCielLoading();
			if (data && data.success) {
				if (!_context.hasConnectionInfo) {
					_initBindingSettingsSection(data);
					_initialSync();
				}

				if (!_context.hasConnectionError) {
					_showSuccess(data.message || DEFAULT_MSG_SETTINGS_SAVED_OK);
					_storeInitialControlValues();
				} else {
					_showSuccess(data.message || DEFAULT_MSG_SETTINGS_SAVED_OK_NEEDS_RELOAD);
					_delayedReloadPage(5);
				}
			} else {
				_showError(data.message || DEFAULT_MSG_SETTINGS_SAVE_FAILED);
			}
		}).fail(function(xhr, status, error) {
			$.hideCielLoading();
			_showError(DEFAULT_MSG_SETTINGS_SAVE_FAILED);
		});
	}

	function _handleMainSaveButtonClicked(event) {
		event.preventDefault();
		_saveSettings();
	}

	function _syncShippingQuotaProperties() {
		var $option = $ctlBindingShippingQuotaNameSelect
			.find('option:selected');

		if ($option.size()) {
			var shippingQuotaValue = $option
				.attr('data-quota-value');
			$('#myc_runtime_shipping_vat_quota_value')
				.val(shippingQuotaValue);
		}
	}

	function _handleShippingQuotaNameChanged() {
		_syncShippingQuotaProperties();
	}

	function _handleWarehouseChanged() {
		var currentValue = $ctlBindingWarehousesSelect.val();
		var initialValue = $ctlBindingWarehousesSelect.data('savedValue');
		
		if ((currentValue != initialValue) 
			&& (!!initialValue) 
			&& !_confirmWarehouseChange()) {
			$ctlBindingWarehousesSelect.val(initialValue);
		} else {
			_syncBindingWarehouseWithBindingProperties();
		}
	}

	function _syncBindingWarehouseWithBindingProperties() {
		var $warehouseOption = $ctlBindingWarehousesSelect
			.find('option:selected');
		
		if ($warehouseOption.size()) {
			$('#myc_runtime_warehouse_type').val($warehouseOption
				.attr('data-type'));
			$('#myc_runtime_warehouse_code').val($warehouseOption
				.attr('data-code'));
		}

		var supportsSaleOrder = $warehouseOption
			.attr('data-supports-sale-order') 
				== 'true';
		var supportsSaleInvoice = $warehouseOption
			.attr('data-supports-sale-invoice') 
				== 'true';

		var $saleOrderOption = $ctlBindingDocumentTypeSelect
			.find('option[value=sale-order]');
		var $saleInvoiceOption = $ctlBindingDocumentTypeSelect
			.find('option[value=sale-invoice]');

		$saleOrderOption.removeAttr('disabled');
		if (!supportsSaleOrder) {
			if ($saleOrderOption.is(':selected')) {
				$ctlBindingDocumentTypeSelect.val('none');
			}
			$saleOrderOption.attr('disabled', 'disabled');
		}

		$saleInvoiceOption.removeAttr('disabled');
		if (!supportsSaleInvoice) {
			$saleInvoiceOption.attr('disabled', 'disabled');
		}
	}

	function _confirmWarehouseChange() {
		return confirm(window['myc_msgConfirmWarehouseChange'] 
			|| DEFAULT_MSG_CONFIRM_WAREHOUSE_CHANGE);
	}

	function _initControls() {
		$ctlSettingsForm = $('#myc_ciel_settings_form');
		$ctlBindingWarehousesSelect = $('#myc_runtime_warehouse');
		$ctlBindingDocumentTypeSelect = $('#myc_runtime_issue_doctype');
		$ctlBindingShippingQuotaNameSelect = $('#myc_runtime_shipping_vat_quota_name');
		$ctlBindingSettingsContainer = $('#myc_binding_runtime_settings_container');
		$ctlWorkflowSettingsContainer = $('#myc_workflow_settings_container');
	}

	function _initListeners() {
		$ctlBindingWarehousesSelect.on('change', 
			_handleWarehouseChanged);
		$ctlBindingShippingQuotaNameSelect.on('change', 
			_handleShippingQuotaNameChanged);

		$('#myc_test_ciel_erp_connection').on('click', 
			_handleTestConnectionButtonClicked);
		$('#myc_ciel_settings_save').on('click', 
			_handleMainSaveButtonClicked);
	}

	function _storeInitialControlValues() {
		$ctlBindingWarehousesSelect.data('savedValue',
			$ctlBindingWarehousesSelect.val());
	}

	function _initialSync() {
		if (_context.hasConnectionInfo) {
			_syncBindingWarehouseWithBindingProperties();
			_syncShippingQuotaProperties();
		}
	}

	function _initContext() {
		_updateContext({
			hasConnectionInfo: !!window['myc_hasConnectionInfo'],
			hasConnectionError: !!window['myc_hasConnectionError']
		});
	}

	$(document).ready(function() {
		_initContext();
		_initControls();
		_storeInitialControlValues();
		_initialSync();
		_initListeners();
	});
})(jQuery);