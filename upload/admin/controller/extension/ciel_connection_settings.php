<?php
use CielIntegration\CielController;

class ControllerExtensionCielConnectionSettings extends CielController {
	public function index() {
		$data = array();
		return $this->_renderView('extension/ciel_connection_settings_form', $data);
	}
}