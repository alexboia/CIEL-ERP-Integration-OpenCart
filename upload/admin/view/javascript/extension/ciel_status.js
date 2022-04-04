(function($) {
	"use strict";

	var DEFAULT_MSG_LOG_CLEARED_SUCCESS = 'The log file has been successfully cleared.';
	var DEFAULT_MSG_LOG_CLEARED_ERROR = 'The log file could not be cleared. Please try again.';

	function _getClearLogActionUrl($target) {
		return $.getCielActionUrl($target);
	}

	function _showDebugLogOperationError(message) {
		$('#myc_debug_log_operation_status_message')
			.cielOperationStatus('show', 
				false, 
				message);
	}

	function _showDebugLogOperationSuccess(message) {
		$('#myc_debug_log_operation_status_message')
			.cielOperationStatus('show', 
				true, 
				message);
	}

	function _showErrorLogOperationError(message) {
		$('#myc_error_log_operation_status_message')
			.cielOperationStatus('show', 
				false, 
				message);
	}

	function _showErrorLogOperationSuccess(message) {
		$('#myc_error_log_operation_status_message')
			.cielOperationStatus('show', 
				true, 
				message);
	}

	function _clearLog(actionUrl, onReady) {
		$.showCielLoading();
		$.ajax(actionUrl, {
			type: 'POST',
			dataType: 'json',
			cache: false,
			data: {}
		}).done(function(data, status, xhr) {
			$.hideCielLoading();
			if (data && data.success) {
				onReady(true, data.message || null);
			} else {
				onReady(false, data.message || null);
			}
		}).fail(function(xhr, status, error) {
			$.hideCielLoading();
			onReady(false, null);
		});
	}

	function _markLogCleared($logClearBtn, $logDownloadBtn, $logDisplayControl) {
		$logClearBtn.remove();
		$logDownloadBtn.remove();
		$logDisplayControl.val('');
	}

	function _handleDebugLogBtnClicked() {
		var $me = $(this);
		var actionUrl = _getClearLogActionUrl($me);
		_clearLog(actionUrl, function(success, message) {
			if (success) {
				_markLogCleared($me, 
					$('#myc-download-debug-log-btn'), 
					$('#myc-debug-log-display')
				);

				_showDebugLogOperationSuccess(message 
					|| DEFAULT_MSG_LOG_CLEARED_SUCCESS);
			} else {
				_showDebugLogOperationError(message 
					|| DEFAULT_MSG_LOG_CLEARED_ERROR);
			}
		});
	}

	function _handleErrorLogBtnClicked() {
		var $me = $(this);
		var actionUrl = _getClearLogActionUrl($me);
		_clearLog(actionUrl, function(success, message) {
			if (success) {
				_markLogCleared($me, 
					$('#myc-download-error-log-btn'), 
					$('#myc-error-log-display')
				);

				_showErrorLogOperationSuccess(message 
					|| DEFAULT_MSG_LOG_CLEARED_SUCCESS);
			} else {
				_showErrorLogOperationError(message 
					|| DEFAULT_MSG_LOG_CLEARED_ERROR);
			}
		});
	}

	function _initEvents() {
		$('#myc-clear-debug-log-btn').on('click', 
			_handleDebugLogBtnClicked);
		$('#myc-clear-error-log-btn').on('click', 
			_handleErrorLogBtnClicked);
	}

	$(document).ready(function() {
		_initEvents();
	});
})(jQuery);