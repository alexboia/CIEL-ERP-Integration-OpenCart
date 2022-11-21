<?php
use CielIntegration\CielController;
use CielIntegration\Integration\Admin\WithCielIntegration;

class ControllerExtensionCielConnectionSettings extends CielController {
	use WithCielIntegration;
	
	public function index($data = array()) {
		$data = array_merge($data, 
			$this->_getConnectionSettingsFormData());

		$data = $this->_loadTexts($data, array(
			'text_connection_settings_form_heading',
			'lbl_connection_endpoint_url_field',
			'txt_placeholder_connection_endpoint_url_field',
			'lbl_connection_username_field',
			'txt_placeholder_connection_username_field',
			'lbl_connection_society_code_field',
			'txt_placeholder_connection_society_code_field',
			'lbl_connection_password_field',
			'txt_placeholder_connection_password_field',
			'lbl_connection_timeout_seconds_field',
			'txt_placeholder_connection_timeout_seconds_field'
		));

		$data['url_test_connection_action'] = $this->_createRouteUrl('extension/module/ciel/testConnection');
		$data['txt_test_connection_action'] = $this->_t('button_test_connection');

		return $this->_renderView('extension/ciel_connection_settings_form', 
			$data);
	}

	private function _getConnectionSettingsFormData() {
		$storeBinding = $this->_getStoreBinding();
		if ($storeBinding->hasConnectionInfo()) {
			$endpoint = $storeBinding->getEndpoint();
			$credentials = $storeBinding->getCredentials();
			$timeoutSeconds = $storeBinding->getTimeoutSeconds();

			$data = array(
				'has_connection' => true,
				'connection_endpoint_url' => $endpoint,
				'connection_username' => $credentials['username'],
				'connection_society_code' => $credentials['society'],
				'connection_timeout_seconds' => $timeoutSeconds
			);
		} else {
			$data = array(
				'has_connection' => false,
				'connection_endpoint_url' => '',
				'connection_username' => '',
				'connection_society_code' => '',
				'connection_timeout_seconds' => 10
			);
		}

		return $data;
	}
}