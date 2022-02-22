<?php
use CielIntegration\CielController;

class ControllerExtensionCielRuntimeSettings extends CielController {
	public function index() {
		$data = array();
		$data['text_runtime_settings_form_heading'] = $this->_t('text_runtime_settings_form_heading');
		return $this->_renderView('extension/ciel_runtime_settings_form', $data);
	}
}