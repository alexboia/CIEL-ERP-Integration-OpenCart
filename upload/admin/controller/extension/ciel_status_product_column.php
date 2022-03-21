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

		$listingRewriter->addColumn(
			'ciel_connected', 
			$this->_t('lbl_column_product_connected_to_ciel_erp_header')
		);

		return $listingRewriter
			->rewrite($output);
	}

	private function _getColumnDataSource(&$productListingData) {
		$productIds = $this->_getProductIds($productListingData);
		$cielErpConnectionStatuses = $this->_getCielErpIntegrationStatus($productIds);

		$data = array();
		foreach ($cielErpConnectionStatuses as $id => $isConnected) {
			$data[$id] = array(
				'ciel_connected' => myc_status_label($isConnected, 
					$this->_t('lbl_txt_yes'), 
					$this->_t('lbl_txt_no'))
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