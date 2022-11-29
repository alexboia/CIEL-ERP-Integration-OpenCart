(function($) {
	"use strict";

	var VAT_CODE_CHANGE_LOOKUP_TIMEOUT = 650;
	var VAT_CODE_CHANGE_LOOKUP_MIN_LENGTH = 2;

	function _getVatCodeLookupActionUrl() {
		return (window['myc_vat_code_lookup_action_url'] || '')
			.replace('&amp;', '&');
	}

	function _getCustomFieldsMapping() {
		return window['myc_custom_fields_mapping'] || null;
	}

	function _hasCustomFieldsMapping() {
		return _getCustomFieldsMapping() != null;
	}

	function _buildVatCodeLookupPostData(vatCode) {
		return 'myc_vat_code=' + vatCode;
	}

	function _createPlugin($target, opts) {
		var _vatCodeChangeLookupTimer = null;

		var $ctlVatCodeField = null;
		var $ctlCompanyNameField = null;
		var $ctlPostCodeField = null;
		var $ctlAddress1Field = null;
		var $ctlBtnSubmitAddressForm = null;

		function _lookupVatCode() {
			var vatCode = $ctlVatCodeField.val();
			if (_shouldLookupVatCode(vatCode)) {
				_disableAddressFormSubmit();
				$.ajax(_getVatCodeLookupActionUrl(), {
					type: 'POST',
					dataType: 'json',
					cache: false,
					data: _buildVatCodeLookupPostData(vatCode)
				}).done(function(data, status, xhr) {
					_enableAddressFormSubmit();
					if (data && data.success) {
						if (_wasLookupPerformed(data)) {
							if (_isVatCodeValid(data)) {
								_updateFieldsFromVatCodeLookupData(data);
							} else {
								_resetVatCodeField();
							}
						}
					} else {
						_resetVatCodeField();
					}
				}).fail(function(xhr, status, error) {
					_enableAddressFormSubmit();
					_resetVatCodeField();
				});
			}
		}

		function _shouldLookupVatCode(vatCode) {
			return !!vatCode && vatCode.length 
				> VAT_CODE_CHANGE_LOOKUP_MIN_LENGTH;
		}

		function _disableAddressFormSubmit() {
			if ($ctlBtnSubmitAddressForm != null) {
				$ctlBtnSubmitAddressForm.hide();
			}
		}

		function _enableAddressFormSubmit() {
			if ($ctlBtnSubmitAddressForm != null) {
				$ctlBtnSubmitAddressForm.show();
			}
		}

		function _isVatCodeValid(resultData) {
			return resultData.exists 
				&& !!resultData.info 
				&& !!resultData.info.person_name;
		}

		function _wasLookupPerformed(resultData) {
			return !!resultData.performed
		}

		function _updateFieldsFromVatCodeLookupData(resultData) {
			$ctlVatCodeField.val(resultData.info.vat_code_full || '');
			if (!$ctlCompanyNameField.val()) {
				$ctlCompanyNameField.val(resultData.info.person_name || '');
			}

			if (!$ctlPostCodeField.val()) {
				$ctlPostCodeField.val(resultData.info.postal_code);
			}
		}

		function _resetVatCodeField() {
			$ctlVatCodeField.val('');
		}

		function _handleVatCodeKeyUp() {
			if (_vatCodeChangeLookupTimer !== null) {
				window.clearTimeout(_vatCodeChangeLookupTimer);
				_vatCodeChangeLookupTimer = null;
			}

			_vatCodeChangeLookupTimer = 
				window.setTimeout(_lookupVatCode, 
					VAT_CODE_CHANGE_LOOKUP_TIMEOUT);
		}

		function _initControls($target, opts) {
			var mapping = _getCustomFieldsMapping();
			if (mapping.vat_code_field_id) {
				var selVatCodeInputFieldPrefix = opts.sel_vat_code_input_prefix 
					|| '#input-custom-field';

				$ctlVatCodeField = $target.find(selVatCodeInputFieldPrefix + mapping.vat_code_field_id);
				if ($ctlVatCodeField.size() == 0) {
					$ctlVatCodeField = $target.find('input[name="custom_field[' + mapping.vat_code_field_id + ']"]:first');
					if ($ctlVatCodeField.size() == 0) {
						$ctlVatCodeField = null;
					}
				}

				if ($ctlVatCodeField != null) {
					$ctlCompanyNameField = $target.find(opts.sel_input_company 
						|| '#input-company');
					$ctlPostCodeField = $target.find(opts.sel_input_post_code 
						|| '#input-postcode');
					$ctlAddress1Field = $target.find(opts.sel_input_address_1 
						|| '#input-address-1');
				}

				if (opts.sel_submit_address_form) {
					$ctlBtnSubmitAddressForm = $(opts.sel_submit_address_form);
					if ($ctlBtnSubmitAddressForm.size() == 0) {
						$ctlBtnSubmitAddressForm = null;
					}
				}
			}
		}

		function _initEvents() {
			if ($ctlVatCodeField != null) {
				$ctlVatCodeField.on('keyup', _handleVatCodeKeyUp);
			}
		}

		if (_hasCustomFieldsMapping()) {
			_initControls($target, opts);
			_initEvents(opts);
		}
	}

	$.fn.cielCatalogAnafData = function() {
		var $target = $(this);
		var opts = (arguments.length > 0 ? arguments[0] : null) 
			|| {};

		_createPlugin($target, 
			opts);
	}
})(jQuery);