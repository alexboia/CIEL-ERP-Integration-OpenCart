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

		//Prepare document assets
		$this->_includeLoadingIndicatorScript();
		$this->_addHeaderScript('extension/module/ciel.js');
		$this->_setDocumentTitleLangKey('ciel_settings_page_title');

		//Prepare data
		$data = $this->_loadAdminLayout();
		$data['token'] = $this->_getCurrentSessionToken();

		$data['ciel_settings_page_title'] = $this->_t('ciel_settings_page_title');

		$data['txt_save_action'] = $this->_t('button_save');
		$data['url_save_action'] = $this->_createRouteUrl('extension/module/ciel/saveSettings');

		$data['txt_cancel_action'] = $this->_t('button_cancel');
		$data['url_cancel_action'] = $this->_createRouteUrl('extension/module');

		$data['html_loading_indicator'] = $this->_renderLoadingIndicator();
		$data['html_breadcrumbs'] = $this->_renderBreadcrumbs($this->_getIndexBreadcrumbsData());

		$data['html_connection_settings_form'] = $this->_renderConnectionSettinsForm();

		if ($storeBinding->hasConnectionInfo()) {
			$data['html_runtime_settings_form'] = $this->_renderRuntimeSettingsForm();
		} else {
			$data['html_runtime_settings_form'] = '';
		}

		//Render view
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

	private function _renderConnectionSettinsForm() {
		return $this->load->controller('extension/ciel_connection_settings', 
			array());
	}

	private function _renderRuntimeSettingsForm() {
		return $this->load->controller('extension/ciel_runtime_settings',
			array());
	}

	public function saveSettings() {
		if ($this->_isHttpPost()) {
			$result = new \stdClass();
			$this->_renderJsonToResponseOutput($result);
		} else {
			die;
		}
	}

	public function testConnection() {
		if ($this->_isHttpPost()) {
			$bindingEndpoint = isset($this->request->post['myc_connection_endpoint_url']) 
				? $this->_sanitizeUrl($this->request->post['myc_connection_endpoint_url'])
				: '';
			$bindingUsername = isset($this->request->post['myc_connection_username'])
				? $this->_sanitizeTextInput($this->request->post['myc_connection_username'])
				: '';
			$bindingPassword = isset($this->request->post['myc_connection_password'])
				? $this->_sanitizeTextInput($this->request->post['myc_connection_password'])
				: '';
			$bindingSociety = isset($this->request->post['myc_connection_society_code'])
				? $this->_sanitizeTextInput($this->request->post['myc_connection_society_code'])
				: '';

			$response = $this->_createAjaxResponse();
			$storeBinding = $this->_getStoreBinding();

			if (empty($bindingPassword) && $storeBinding->hasConnectionInfo()) {
				$bindingPassword = $storeBinding->getPassword();
			}

			if (!empty($bindingEndpoint) 
				&& !empty($bindingUsername) 
				&& !empty($bindingPassword) 
				&& !empty($bindingSociety)) {
				try {
					$this->_processTestCielWebServiceConnection($bindingEndpoint, 
						$bindingUsername, 
						$bindingPassword, 
						$bindingSociety);
	
					$response->message = $this->_t('msg_connection_test_ok');
					$response->success = true;
				} catch (Exception $exc) {
					$response->message = $this->_t('msg_connection_test_failed');
				}
			} else {
				$response->message = $this->_t('msg_err_fill_in_connection_properties');
			}

			$this->_renderJsonToResponseOutput($response);
		} else {
			die;
		}
	}

	private function _processTestCielWebServiceConnection($endpoint, 
		$userName, 
		$password, 
		$society ) {

		$storeBinding = $this->_getStoreBinding();
		$client = $storeBinding->createCielClient($endpoint);

		$client->logon($userName, 
			$password, 
			$society);
			
		$client->logout();
	}
}