<?php
use CielIntegration\CielController;
use CielIntegration\CustomerDataCollector;
use CielIntegration\Integration\Admin\Partner\PartnerResolver;
use CielIntegration\ListingContentsRewriter;
use CielIntegration\StaticDataSource;

class ControllerExtensionCielStatusCustomerColumn extends CielController {
	public function index(&$route, &$data, &$output) {
		$dataSource = $this->_getColumnDataSource($data);

		$listingRewriter = new ListingContentsRewriter(
			'#form-customer', 
			'td input[name="selected[]"]', 
			$dataSource
		);

		$listingRewriter->addColumn(
			'ciel_customer_connected', 
			$this->_t('lbl_column_customer_connected_to_ciel_erp_header')
		);

		return $listingRewriter
			->rewrite($output);
	}

	private function _getColumnDataSource(&$customerListingData) {
		$customerIds = $this->_getCustomerIds($customerListingData);
		$cielCustomerStatuses = $this->_getCielErpIntegrationStatus($customerIds);

		$data = array();
		foreach ($cielCustomerStatuses as $id => $isConnected) {
			$data[$id] = array(
				'ciel_customer_connected' => myc_status_label($isConnected, 
					$this->_t('lbl_txt_yes'), 
					$this->_t('lbl_txt_no'))
			);
		}

		return new StaticDataSource(
			$data
		);
	}

	private function _getCustomerIds(&$customerListingData) {
		$customerDataCollector = new CustomerDataCollector($customerListingData);
		return $customerDataCollector->collectIds();
	}

	private function _getCielErpIntegrationStatus($productIds) {
		$resover = $this->_getPartnerResolver();
		return $resover->areConnectedToCielErp($productIds);
	}

	private function _getPartnerResolver() {
		return new PartnerResolver($this->registry);
	}
}