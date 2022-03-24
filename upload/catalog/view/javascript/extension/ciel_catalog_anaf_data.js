(function($) {
	"use strict";

	var VAT_CODE_CHANGE_LOOKUP_TIMEOUT = 250;
	var VAT_CODE_CHANGE_LOOKUP_MIN_LENGTH = 2;

	var _vatCodeChangeLookupTimer = null;

	var $ctlVatCodeField = null;
	var $ctlCompanyNameField = null;
	var $ctlPostCodeField = null;
	var $ctlAddress1Field = null;

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

	function _lookupVatCode() {
		var vatCode = $ctlVatCodeField.val();
		if (_shouldLookupVatCode(vatCode)) {
			$.ajax(_getVatCodeLookupActionUrl(), {
				type: 'POST',
				dataType: 'json',
				cache: false,
				data: _buildVatCodeLookupPostData(vatCode)
			}).done(function(data, status, xhr) {
				if (data && data.success) {
					if (_isVatCodeValid(data)) {
						$ctlVatCodeField.val(data.info.vat_code_full || '');
						if (!$ctlCompanyNameField.val()) {
							$ctlCompanyNameField.val(data.info.person_name || '');
						}

						if (!$ctlPostCodeField.val()) {
							$ctlPostCodeField.val(data.info.postal_code);
						}
					} else {
						$ctlVatCodeField.val('');
					}
				}
			}).fail(function(xhr, status, error) {
				//nothing to be done
			});
		}
	}

	function _shouldLookupVatCode(vatCode) {
		return !!vatCode && vatCode.length 
			> VAT_CODE_CHANGE_LOOKUP_MIN_LENGTH;
	}

	function _isVatCodeValid(resultData) {
		return resultData.exists 
			&& !!resultData.info 
			&& !!resultData.info.person_name;
	}

	function _buildVatCodeLookupPostData(vatCode) {
		return 'myc_vat_code=' + vatCode;
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
		}
	}

	function _initEvents() {
		if ($ctlVatCodeField != null) {
			$ctlVatCodeField.on('keyup', _handleVatCodeKeyUp);
		}
	}

	$.fn.cielCatalogAnafData = function() {
		var $target = $(this);
		var opts = (arguments.length > 0 ? arguments[0] : null) 
			|| {};

		if (_hasCustomFieldsMapping()) {
			_initControls($target, opts);
			_initEvents(opts);
		}
	}
})(jQuery);