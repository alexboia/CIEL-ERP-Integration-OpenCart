<?php
use CielIntegration\CielController;
use CielIntegration\Integration\Admin\Order\OrderResolver;
use CielIntegration\SidebarStatsRewriter;

class ControllerExtensionCielIntegrationStatsEntries extends CielController {
	public function index(&$route, &$data, &$output) {
		$documentIssuedPercentage = $this
			->_getDocumentIssuedPercentage();
		
		$rewriter = new SidebarStatsRewriter('#stats ul');
		$rewriter->addStatsItem(
			'ciel_document_issued_percentage', 
			$this->_t('lbl_ciel_document_issued_percentage'), 
			$documentIssuedPercentage
		);

		return $rewriter->rewrite($output);
	}

	private function _getDocumentIssuedPercentage() {
		$remoteOrderStats = $this->_getRemoteOrderStats();
		return $remoteOrderStats['percentage'];
	}

	private function _getRemoteOrderStats() {
		return $this->_getOrderResolver()
			->getRemoteOrderStats();
	}

	private function _getOrderResolver() {
		return new OrderResolver($this->registry);
	}
}