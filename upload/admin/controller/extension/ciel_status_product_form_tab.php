<?php

use CielIntegration\CielController;
use CielIntegration\TabPanelContentsRewriter;

class ControllerExtensionCielStatusProductFormTab extends CielController {
	public function index(&$route, &$data, &$output) {
		$tabPanelRewriter = new TabPanelContentsRewriter('#form-product');

		$tabPanelRewriter->addTab('tab-ciel-status', 
			'CIEL Integration', 
			'<b>Test Ciel Integration Contents</b>');

		return $tabPanelRewriter
			->rewrite($output);
	}
}