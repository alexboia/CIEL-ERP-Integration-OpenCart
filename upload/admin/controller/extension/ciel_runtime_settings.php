<?php
use CielIntegration\CielController;

class ControllerExtensionCielRuntimeSettings extends CielController {
	public function index() {
		$data = array();
		return $this->_renderView('extension/ciel_runtime_settings_form', $data);
	}
}