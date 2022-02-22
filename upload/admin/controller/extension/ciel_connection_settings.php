<?php
use CielIntegration\CielController;

class ControllerExtensionCielConnectionSettings extends CielController {
	public function index($data = array()) {
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
}