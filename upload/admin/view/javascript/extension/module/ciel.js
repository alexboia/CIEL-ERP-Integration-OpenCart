(function($) {
	"use strict";

	function _getConnectionTestUrl() {
		return $('#myc_test_ciel_erp_connection')
			.attr('data-test-connection-url');
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
				.val()
		};
	}

	function _testConnection(data) {
		$.showCielLoading();
		$.ajax(_getConnectionTestUrl(), {
			type: 'POST',
			dataType: 'json',
			cache: false,
			data: data
		}).done(function(data, status, xhr) {
			$.hideCielLoading();
		}).fail(function(xhr, status, error) {
			$.hideCielLoading();
		});
	}

	function _handleTestConnectionButtonClicked(event) {
		event.preventDefault();
		var data = _getConnectionTestInputData();
		_testConnection(data);
	}

	function _handleMainSaveButtonClicked(event) {
		event.preventDefault();
		$.showCielLoading();
	}

	function _initListeners() {
		$('#myc_test_ciel_erp_connection').on('click', _handleTestConnectionButtonClicked);
		$('#myc_ciel_settings_save').on('click', _handleMainSaveButtonClicked);
	}

	$(document).ready(function() {
		_initListeners();
	});
})(jQuery);