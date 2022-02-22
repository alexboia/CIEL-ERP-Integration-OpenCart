<?php
use CielIntegration\CielController;

class ControllerExtensionCielModalLoadingIndicator extends CielController {
	public function index() {
		return $this->_renderView('extension/ciel_modal_loading_indicator', array());
	}
}