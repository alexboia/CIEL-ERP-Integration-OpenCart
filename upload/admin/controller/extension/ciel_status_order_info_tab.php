<?php
use CielIntegration\CielController;
use CielIntegration\ContentsAppender;
use phpDocumentor\Reflection\DocBlock\Tags\Var_;

class ControllerExtensionCielStatusOrderInfoTab extends CielController {
	public function assets() {
		if ($this->_isOrderViewPage()) {
			$this->_includeLoadingIndicatorScript();
			$this->_includeOperationStatusScript();
			$this->_addHeaderScript('extension/ciel_order_actions.js');
		}
	}

	private function _isOrderViewPage() {
		return $this->_isRoute('sale/order/info');
	}

	public function index(&$route, &$data, &$output) {
		$orderId = $this->_getOrderIdFromUrl();
		if (empty($orderId)) {
			return $output;
		}

		$viewData = array();
		$viewContents = $this->_renderView('extension/ciel_status_order_info_tab_contents', 
			$viewData);

		$contentsAppender = new ContentsAppender('#content > div.container-fluid');
		$contentsAppender->addContent($viewContents);

		return $contentsAppender
			->rewrite($output);
	}

	private function _getOrderIdFromUrl() {
		return isset($this->request->get['order_id'])
			? intval($this->request->get['order_id'])
			: 0;
	}
}