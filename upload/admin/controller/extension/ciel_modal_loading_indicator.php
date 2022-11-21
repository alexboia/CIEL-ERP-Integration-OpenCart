<?php
use CielIntegration\CielController;

class ControllerExtensionCielModalLoadingIndicator extends CielController {
	public function index() {
		//TODO: maybe parameterize to include assets automatically here?
		return $this->_renderView('extension/ciel_modal_loading_indicator', array());
	}
}