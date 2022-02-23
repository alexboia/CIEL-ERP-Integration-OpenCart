<?php
use CielIntegration\CielController;

class ControllerExtensionCielConnectionSettings extends CielController {
	public function index($data = array()) {
		$data = array_merge($data, 
			$this->_getConnectionSettingsFormData());
			
		$data = array_merge($data, array(
			'text_connection_settings_form_heading' 
				=> $this->_t('text_connection_settings_form_heading'),
			
			'txt_test_connection_action' 
				=> $this->_t('button_test_connection'),
			'url_test_connection_action' 
				=> $this->_createRouteUrl('extension/module/ciel/testConnection')
		));

		return $this->_renderView('extension/ciel_connection_settings_form', 
			$data);
	}

	private function _getConnectionSettingsFormData() {
		$storeBinding = $this->_getStoreBinding();
		if ($storeBinding->hasConnectionInfo()) {
			$endpoint = $storeBinding->getEndpoint();
			$credentials = $storeBinding->getCredentials();

			$data = array(
				'connection_endpoint_url' => $endpoint,
				'connection_username' => $credentials['username'],
				'connection_society_code' => $credentials['society']
			);
		} else {
			$data = array(
				'connection_endpoint_url' => '',
				'connection_username' => '',
				'connection_society_code' => ''
			);
		}

		return $data;
	}
}