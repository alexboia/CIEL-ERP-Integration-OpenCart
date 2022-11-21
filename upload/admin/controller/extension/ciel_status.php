<?php
use CielIntegration\CielController;
use CielIntegration\ExtensionInfo;
use CielIntegration\Integration\Admin\WithCielIntegration;
use CielIntegration\LogFileDownloader;
use CielIntegration\LogFileManager;
use CielIntegration\WithAdminLayoutLoader;
use CielIntegration\WithLogging;

class ControllerExtensionCielStatus extends CielController {
	use WithCielIntegration;
	use WithAdminLayoutLoader;
	use WithLogging;

	const CIEL_DEBUG_LOG_KEY = 'debug';

	const CIEL_ERROR_LOG_KEY = 'error';

	public function __construct(\Registry $registry) {
		parent::__construct($registry);
		$this->_setTextDomain('extension/ciel_status');
	}

	private function _getDebugLogActionParams() {
		return array(
			'log' => self::CIEL_DEBUG_LOG_KEY
		);
	}

	private function _getErrorLogActionParams() {
		return array(
			'log' => self::CIEL_ERROR_LOG_KEY
		);
	}

	public function index () {
		//Prepare document assets
		$this->_setDocumentTitleLangKey('ciel_status_title');
		$this->_includeCommonStylesheet();

		$this->_includeLoadingIndicatorScript();
		$this->_includeOperationStatusScript();
		$this->_includeCommonScript();
		$this->_addHeaderScript('extension/ciel_status.js');

		//Prepare data
		$debugLogActionsParams = $this->_getDebugLogActionParams();
		$errorLogActionParams = $this->_getErrorLogActionParams();

		$data = $this->_loadAdminLayout();

		$data = $this->_loadTexts($data, array(
			'ciel_status_title',
			'ciel_status_box_heading',
			'ciel_status_box_debug_log',
			'ciel_status_box_error_log',

			'lbl_module_version',
			'lbl_module_configured',
			'lbl_php_version',
			'lbl_opencart_version'
		));

		$data['download_log_btn_text'] = $this->_t('download_log_btn_text');
		$data['download_debug_log_btn_action'] = $this->_createRouteUrl('extension/ciel_status/downloadLog', 
			$debugLogActionsParams);
		$data['download_error_log_btn_action'] = $this->_createRouteUrl('extension/ciel_status/downloadLog', 
			$errorLogActionParams);
		
		$data['clear_log_btn_text'] = $this->_t('clear_log_btn_text');
		$data['clear_debug_log_btn_action'] = $this->_createRouteUrl('extension/ciel_status/clearLog', 
			$debugLogActionsParams);
		$data['clear_error_log_btn_action'] = $this->_createRouteUrl('extension/ciel_status/clearLog', 
			$errorLogActionParams);
		
		$data['view_config_link_text'] = $this->_t('view_config_link_text');
		$data['view_config_link_action'] = $this->_createRouteUrl('extension/module/ciel');

		$data['status'] = $this->_getStatus();
		$data['debug_log_status'] = $this->_getDebugLogStatus();
		$data['error_log_status'] = $this->_getErrorLogStatus();

		$data['txt_cancel_action'] = $this->_t('button_cancel');
		$data['url_cancel_action'] = $this->_createRouteUrl('common/dashboard');

		$data['html_loading_indicator'] = $this->_renderLoadingIndicator();
		$data['html_breadcrumbs'] = $this->_renderBreadcrumbsToIndex();

		//Render view
		$this->_renderViewToResponseOutput('extension/ciel_status', 
			$data);
	}

	private function _renderBreadcrumbsToIndex() {
		return $this->_renderBreadcrumbs($this->_getBreadcrumbsData());
	}

	private function _getBreadcrumbsData() {
		$breadcrumbs = $this->_getBaseBreadcrumbs();
		$breadcrumbs[] = array(
			'text' => $this->_t('ciel_status_title'),
			'href' => $this->_createRouteUrl('extension/ciel_status')
		);

		return array(
			'breadcrumbs' => $breadcrumbs
		);
	}

	private function _getStatus() {
		return array(
			'php_version' => $this->_getPhpVersionString(),
			'opencart_version' => $this->_getOpenCartVersionString(),
			'module_version' => $this->_getVersionString(),
			'module_configured' => $this->_getIsConfiguredDescription()
		);
	}

	private function _getPhpVersionString() {
		return function_exists('phpversion') 
			? phpversion() 
			: 'n/a';
	}

	private function _getOpenCartVersionString() {
		return defined('VERSION') 
			? VERSION 
			: 'n/a';
	}

	private function _getVersionString() {
		return ExtensionInfo::VERSION;
	}

	private function _getIsConfiguredDescription() {
		return $this->_isStoreBound() 
			? $this->_t('lbl_txt_yes') 
			: $this->_t('lbl_txt_no');
	}

	private function _isStoreBound() {
		return $this->_getStoreBinding()
			->isBound();
	}

	private function _getDebugLogStatus() {
		return $this->_getLogStatus($this->_getDebugLogFileManager());
	}

	private function _getLogStatus(LogFileManager $logFileManager) {
		$logFileMessage = null;

		if ($logFileManager->exists()) {
			if (!$logFileManager->isWitinReadableThreshold()) {
				$logFileMessage = sprintf($this->_t('log_file_large_warning'), 
					$logFileManager->getFileName(),
					$logFileManager->getSizeDescription());
			}
		} else {
			$logFileMessage = sprintf($this->_t('log_file_missing_info'), 
				$logFileManager->getFileName());
		}

		return array(
			'log_file_exists' => $logFileManager->exists(),
			'log_file_contents' => $logFileManager->getReadableContents(),
			'log_file_message' => $logFileMessage
		);
	}

	private function _getErrorLogStatus() {
		return $this->_getLogStatus($this->_getErrorLogFileManager());
	}

	public function downloadLog() {
		$logType = $this->_getLogFileTypeFromUrl();
		if (!$this->_isLogFileTypeValid($logType)) {
			die;
		}

		$logFileManager = $this->_getLogFileManager($logType);
		if ($logFileManager->exists()) {
			$fileName = $logFileManager->getFileName();
			$fileContents = $logFileManager->getEntireContents();

			$downloader = $this->_getLogFileDownloader();
			$downloader->sendLogFile($fileName, 
				$fileContents);
		} else {
			die;
		}
	}

	private function _getLogFileTypeFromUrl() {
		return !empty($this->request->get['log'])
			? $this->request->get['log']
			: null;
	}

	private function _isLogFileTypeValid($logType) {
		return $logType == self::CIEL_DEBUG_LOG_KEY
			|| $logType == self::CIEL_ERROR_LOG_KEY;
	}

	private function _getLogFileManager($logType) {
		if ($logType == self::CIEL_DEBUG_LOG_KEY) {
			return $this->_getDebugLogFileManager();
		} else {
			return $this->_getErrorLogFileManager();
		}
	}

	private function _getLogFileDownloader() {
		return new LogFileDownloader($this->response);
	}

	public function clearLog() {
		$logType = $this->_getLogFileTypeFromUrl();
		if (!$this->_isLogFileTypeValid($logType)) {
			die;
		}

		$response = $this->_createAjaxResponse();

		try {
			$this->_processClearLog($logType);
			$response->success = true;
			$response->message = $this->_t('ciel_log_file_cleared_succes_msg');
		} catch (Exception $exc) {
			$this->_logError($exc, 'Error clearing log file.');
			$response->message = $this->_t('ciel_log_file_cleared_error_msg');
		}

		$this->_renderJsonToResponseOutput($response);
	}

	private function _processClearLog($logType) {
		$logFileManager = $this->_getLogFileManager($logType);
		if ($logFileManager->exists()) {
			$logFileManager->clear();
		}
	}
}