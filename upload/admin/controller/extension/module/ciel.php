<?php
use CielIntegration\CielController;
use CielIntegration\WithAdminLayoutLoader;

class ControllerExtensionModuleCiel extends CielController {
	use WithAdminLayoutLoader;

	public function install() {
		$this->_getModel()
			->install();
	}

	public function uninstall() {
		$this->_getModel()
			->uninstall();
	}

	/**
	 * @return \ModelExtensionModuleCiel
	 */
	private function _getModel() {
		$this->load->model('extension/module/ciel');
		return $this->model_extension_module_ciel;
	}

	public function index() {
		$storeBinding = $this->_getStoreBinding();
		$hasConnectionInfo = $storeBinding->hasConnectionInfo();

		$this->_addHeaderScript('extension/ciel_loading_indicator.js');
		$this->_addHeaderScript('extension/module/ciel.js');
		$this->_setDocumentTitleLangKey('ciel_settings_page_title');

		$data = $this->_loadAdminLayout();
		$data['token'] = $this->_getCurrentSessionToken();

		$data['ciel_settings_page_title'] = $this->_t('ciel_settings_page_title');

		$data['txt_save_action'] = $this->_t('button_save');
		$data['url_save_action'] = $this->_createRouteUrl('extension/module/ciel');

		$data['txt_cancel_action'] = $this->_t('button_cancel');
		$data['url_cancel_action'] = $this->_createRouteUrl('extension/module');

		$data['html_loading_indicator'] = $this->_renderLoadingIndicator();
		$data['html_breadcrumbs'] = $this->_renderBreadcrumbs($this->_getIndexBreadcrumbsData());

		$data['html_connection_settings_form'] = $this->load->controller('extension/ciel_connection_settings', 
			$this->_getConnectionSettingsFormData());

		if ($hasConnectionInfo) {
			$data['html_runtime_settings_form'] = $this->load->controller('extension/ciel_runtime_settings',
				 $this->_getRuntimeSettingsFormData());
		} else {
			$data['html_runtime_settings_form'] = '';
		}

		$this->_renderViewToResponseOutput('extension/module/ciel', 
			$data);
	}

	private function _getIndexBreadcrumbsData() {
		$breadcrumbs = $this->_getBaseBreadcrumbs();
		$breadcrumbs[] = array(
			'text' => $this->_t('ciel_settings_page_title'),
			'href' => $this->_createRouteUrl('extension/module/ciel')
		);

		return array(
			'breadcrumbs' => $breadcrumbs
		);
	}

	private function _getConnectionSettingsFormData() {
		$storeBinding = $this->_getStoreBinding();
		return array(

		);
	}

	private function _getRuntimeSettingsFormData() {
		$storeBinding = $this->_getStoreBinding();
		return array(

		);
	}

	public function testConnection() {
		if ($this->_isHttpPost()) {
			$result = new \stdClass();
			$this->_renderJsonToResponseOutput($result);
		} else {
			die;
		}
	}
}