<?php

use CielIntegration\CielController;
use CielIntegration\ListingContentsRewriter;
use CielIntegration\StaticDataSource;

class ControllerExtensionCielStatusProductColumn extends CielController {
	public function index(&$route, &$data, &$output) {
		$dataSource = new StaticDataSource(array(
			30 => array('ciel_connected' => 'yes'),
			42 => array('ciel_connected' => 'no')
		));

		$listingRewriter = new ListingContentsRewriter('#form-product', $dataSource);
		$listingRewriter->addColumn('ciel_connected', 'Connected to CIEL ERP');

		return $listingRewriter->rewrite($output);
	}
}