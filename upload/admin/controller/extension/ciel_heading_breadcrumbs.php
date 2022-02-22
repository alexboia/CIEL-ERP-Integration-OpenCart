<?php
use CielIntegration\CielController;
class ControllerExtensionCielHeadingBreadcrumbs extends CielController {
	public function index($data = array()) {
		$data['breadcrumbs'] = isset($data['breadcrumbs']) 
			? $data['breadcrumbs'] 
			: array();
			
		return $this->_renderView('extension/ciel_heading_breadcrumbs', 
			$data);
	}
}