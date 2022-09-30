<?php
namespace CielIntegration\Integration\Admin\Order {

    use Ciel\Api\Integration\Orders\DocumentPreRequisiteStatus;
    use Ciel\Api\Integration\Orders\DocumentPreRequisiteStatusItem;
    use CielIntegration\Integration\Admin\Article\ProductResolver;
    use CielIntegration\Integration\Admin\IntegrationService;

	class OrderDocumentPreRequisitesStatusProvider extends IntegrationService {
		/**
		 * @var DocumentPreRequisiteStatus[]
		 */
		private $_cachedOrderDocumentPreRequisitesStatus = array();

		/**
		 * @var OrderResolver
		 */
		private $_orderResovler = null;

		/**
		 * @var ProductResolver
		 */
		private $_productResolver = null;

		public function __construct(\Registry $registry) {
			parent::__construct($registry);
			$this->_orderResovler = new OrderResolver($registry);
			$this->_productResolver = new ProductResolver($registry);
		}

		public function determineOrderDocumentPreRequisitesStatus($orderId) {
			$status = $this->_getCachedOrderDocumentPreRequisitesStatus($orderId);
			if ($status === null) {
				$products = $this->_getOrderProducts($orderId);
				$status = new DocumentPreRequisiteStatus();
				foreach ($products as $productItem) {
					$statusItem = $this->_createOrderDocumentPreRequisitesStatusItem($productItem);
					$status->addStatusItem($statusItem);
				}

				$this->_cacheOrderDocumentPreRequisitesStatus($orderId, $status);
			}

			return $status;
		}
		
		/**
		 * @param int $orderId 
		 * @return DocumentPreRequisiteStatus|null
		 */
		private function _getCachedOrderDocumentPreRequisitesStatus($orderId) {
			return isset($this->_cachedOrderDocumentPreRequisitesStatus[$orderId])
				? $this->_cachedOrderDocumentPreRequisitesStatus[$orderId]
				: null;
		}

		private function _getOrderProducts($orderId) {
			$orderProducts = $this->_orderResovler
				->getOrderProducts($orderId);

			if ($orderProducts == null) {
				$orderProducts = array();
			}

			return $orderProducts;
		}

		private function _createOrderDocumentPreRequisitesStatusItem($productItem) {
			$productId = intval($productItem['product_id']);
			$statusItem = new DocumentPreRequisiteStatusItem(
				$productId, 
				$this->_getProductCode($productId),
				$this->_hasRemoteId($productId), 
				$this->_isBatchTrackingEnabled($productId)
			);
			return $statusItem;
		}

		private function _getProductCode($productId) {
			return $this->_productResolver
				->lookupProductSku($productId);
		}

		private function _hasRemoteId($productId) {
			return $this->_productResolver
				->isConnectedToCielErp($productId);
		}
		
		private function _isBatchTrackingEnabled($productId) {
			return $this->_productResolver
				->getRemoteBatchTrackingStatus($productId) == 1;
		}

		private function _cacheOrderDocumentPreRequisitesStatus($orderId, 
			DocumentPreRequisiteStatus $status) {
			$this->_cachedOrderDocumentPreRequisitesStatus[$orderId] = $status;
		}
	}
}