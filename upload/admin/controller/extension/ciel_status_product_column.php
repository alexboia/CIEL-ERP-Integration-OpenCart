<?php
use CielIntegration\CielController;
use CielIntegration\Integration\Admin\Article\ProductResolver;
use CielIntegration\ListingContentsRewriter;
use CielIntegration\ProductDataCollector;
use CielIntegration\StaticDataSource;

class ControllerExtensionCielStatusProductColumn extends CielController {
	public function index(&$route, &$data, &$output) {
		$dataSource = $this->_getColumnDataSource($data);

		$listingRewriter = new ListingContentsRewriter('#form-product', 
			'td input[name="selected[]"]', 
			$dataSource);

		$listingRewriter->addColumn('ciel_connected', 
			'Connected to CIEL ERP');

		return $listingRewriter
			->rewrite($output);
	}

	private function _getColumnDataSource(&$productListingData) {
		$productIds = $this->_getProductIds($productListingData);
		$cielErpConnectionStatuses = $this->_getCielErpIntegrationStatus($productIds);

		$data = array();
		foreach ($cielErpConnectionStatuses as $id => $isConnected) {
			$data[$id] = array(
				'ciel_connected' => $isConnected 
					? '<label class="label label-success">yes</label>' 
					: '<label class="label label-danger">no</label>'
			);
		}

		return new StaticDataSource(
			$data
		);
	}

	private function _getProductIds(&$productListingData) {
		$productDataCollector = new ProductDataCollector($productListingData);
		return $productDataCollector->collectIds();
	}

	private function _getCielErpIntegrationStatus($productIds) {
		$resover = $this->_getProductResolver();
		return $resover->areConnectedToCielErp($productIds);
	}

	private function _getProductResolver() {
		return new ProductResolver($this->registry);
	}
}