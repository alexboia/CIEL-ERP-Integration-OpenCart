(function($) {
	"use strict";

	var DEFAULT_MSG_LOG_CLEARED_SUCCESS = 'The log file has been successfully cleared.';
	var DEFAULT_MSG_LOG_CLEARED_ERROR = 'The log file could not be cleared. Please try again.';

	function _getClearLogActionUrl($target) {
		return $.getCielActionUrl($target);
	}

	function _hideLogOperationMessage($logStatusControl) {
		$logStatusControl.cielOperationStatus('hide');
	}

	function _showSuccess($logStatusControl, message) {
		$logStatusControl.cielOperationStatus('show', 
			true, 
			message);
	}

	function _showError($logStatusControl, message) {
		$logStatusControl.cielOperationStatus('show', 
			false, 
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

	function _handleDebugLogBtnClicked() {
		var $me = $(this);
		_handleClearLogButtonClicked($me, 
			$('#myc-download-debug-log-btn'), 
			$('#myc-debug-log-display'),
			$('#myc_debug_log_operation_status_message')
		);
	}

	function _handleErrorLogBtnClicked() {
		var $me = $(this);
		_handleClearLogButtonClicked($me, 
			$('#myc-download-error-log-btn'), 
			$('#myc-error-log-display'),
			$('#myc_error_log_operation_status_message')
		);
	}
	
	function _handleClearLogButtonClicked($logClearBtn, $logDownloadBtn, $logDisplayControl, $logStatusControl) {
		var actionUrl = _getClearLogActionUrl($logClearBtn);

		_hideLogOperationMessage($logStatusControl);
		_clearLog(actionUrl, function(success, message) {
			if (success) {
				_markLogCleared($logClearBtn, 
					$logDownloadBtn, 
					$logDisplayControl
				);

				_showSuccess($logStatusControl, 
					message || DEFAULT_MSG_LOG_CLEARED_SUCCESS);
			} else {
				_showError($logStatusControl, 
					message || DEFAULT_MSG_LOG_CLEARED_ERROR);
			}
		});
	}
	
	function _markLogCleared($logClearBtn, $logDownloadBtn, $logDisplayControl) {
		$logClearBtn.remove();
		$logDownloadBtn.remove();
		$logDisplayControl.val('');
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