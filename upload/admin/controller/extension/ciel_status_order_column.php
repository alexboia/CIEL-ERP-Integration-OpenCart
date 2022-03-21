<?php

use CielIntegration\CielController;
use CielIntegration\Integration\Admin\Order\OrderResolver;
use CielIntegration\ListingContentsRewriter;
use CielIntegration\OrderDataCollector;
use CielIntegration\StaticDataSource;

class ControllerExtensionCielStatusOrderColumn extends CielController {
	public function index(&$route, &$data, &$output) {
		$dataSource = $this->_getColumnDataSource($data);

		$listingRewriter = new ListingContentsRewriter('#form-order', 
			'td input[name="selected[]"]', 
			$dataSource);

		$listingRewriter->addColumn(
			'ciel_document_issued', 
			$this->_t('lbl_column_order_connected_to_ciel_erp_header')
		);

		return $listingRewriter
			->rewrite($output);
	}

	private function _getColumnDataSource(&$orderListingData) {
		$orderIds = $this->_getOrderIds($orderListingData);
		$cielErpDocumentStatuses = $this->_areCielErpDocumentsIssued($orderIds);

		$data = array();
		foreach ($cielErpDocumentStatuses as $id => $isDocumentIssued) {
			$data[$id] = array(
				'ciel_document_issued' => myc_status_label($isDocumentIssued, 
					$this->_t('lbl_txt_yes'), 
					$this->_t('lbl_txt_no'))
			);
		}

		return new StaticDataSource(
			$data
		);
	}

	private function _getOrderIds(&$orderListingData) {
		$orderDataCollector = new OrderDataCollector($orderListingData);
		return $orderDataCollector->collectIds();
	}

	private function _areCielErpDocumentsIssued($orderIds) {
		$orderResolver = $this->_getOrderResolver();
		return $orderResolver->areRemoteDocumentsIssuedForOrders($orderIds);
	}

	private function _getOrderResolver() {
		return new OrderResolver($this->registry);
	}
}