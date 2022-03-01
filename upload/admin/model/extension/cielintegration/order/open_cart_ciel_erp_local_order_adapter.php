<?php
namespace CielIntegration\Integration\Admin\Order {

    use Ciel\Api\Exception\LocalOrderNotFoundException;
    use Ciel\Api\Integration\Orders\Providers\CielErpLocalOrderAdapter;
    use CielIntegration\Integration\Admin\Article\ProductResolver;
    use CielIntegration\Integration\Admin\IntegrationService;
    use CielIntegration\Integration\Admin\PriceFormatter;
    use InvalidArgumentException;

	class OpenCartCielErpLocalOrderAdapter 
		extends IntegrationService 
		implements CielErpLocalOrderAdapter {

		/**
		 * @var OrderUpdateServiceFactory
		 */
		private $_orderUpdateServiceFactory;

		/**
		 * @var OrderResolver
		 */
		private $_orderResolver;

		/**
		 * @var OrderPartnerResolver
		 */
		private $_orderPartnerResolver;

		/**
		 * @var ProductResolver
		 */
		private $_productResolver;

		/**
		 * @var PriceFormatter
		 */
		private $_priceFormatter;

		/**
		 * @var LocalOrderToRemoteDocumentMarshallerFactory
		 */
		private $_localToRemoteDocumentMarshallerFactory;

		public function __construct(\Registry $registry) {
			parent::__construct($registry);
			$this->_localToRemoteDocumentMarshallerFactory =
				new LocalOrderToRemoteDocumentMarshallerFactory($registry);
			$this->_orderUpdateServiceFactory = 
				new OrderUpdateServiceFactory($registry);
			$this->_productResolver = 
				new ProductResolver($registry);
			$this->_orderResolver = 
				new OrderResolver($registry);
			$this->_orderPartnerResolver = 
				new OrderPartnerResolver($registry);
			$this->_priceFormatter = 
				new PriceFormatter($registry);
		}

		public function setDocumentRemovedForOrder($localId) { 
			if (empty($localId)) {
				throw new InvalidArgumentException('Order Id must not be empty');
			}

			if ($this->_orderExists($localId)) {
				$this->_clearRemoteDocumentData($localId);	
			} else {
				throw new LocalOrderNotFoundException('id', $localId);
			}		
		}

		private function _orderExists($orderId) {
			return $this->_orderResolver
				->orderExists($orderId);
		}

		private function _clearRemoteDocumentData($orderId) {
			$this->_createOrderUpdateService($orderId)
				->clearRemoteDocumentData();
		}

		private function _createOrderUpdateService($orderId) {
			return $this->_orderUpdateServiceFactory
				->createForOrder($orderId);
		}

		public function isDocumentIssuedForOrder($localId) { 
			if (empty($localId)) {
				throw new InvalidArgumentException('Order Id must not be empty');
			}

			if ($this->_orderExists($localId)) {
				return $this->_isRemoteDocumentIssuedForOrder($localId);	
			} else {
				return false;
			}		
		}

		private function _isRemoteDocumentIssuedForOrder($orderId) {
			return $this->_orderResolver
				->isRemoteDocumentIssuedForOrder($orderId);
		}

		public function canDocumentBeIssuedForOrder($localId) { 
			if (empty($localId)) {
				throw new InvalidArgumentException('Order Id must not be empty');
			}

			if ($this->_orderExists($localId)) {
				return $this->_isOrderCustomerConnectedToCielErp($localId)
					&& $this->_canOrderItemsBeAddedToDocument($localId);
			} else {
				return false;
			}
		}

		private function _isOrderCustomerConnectedToCielErp($orderId) {
			return $this->_orderPartnerResolver
				->isOrderCustomerConnectedToCielErp($orderId);
		}

		private function _canOrderItemsBeAddedToDocument($orderId) {
			$status = $this->_determineOrderDocumentPreRequisitesStatus($orderId);
			return $status->canOrderItemsBeAddedToDocument();
		}

		private function _determineOrderDocumentPreRequisitesStatus($orderId) {
			$status = $this->_documentPreRequisitesStatusProvider
				->determineOrderDocumentPreRequisitesStatus($orderId);

			$status = $status
				->setIsBatchTrackingPossible($this->_isBatchTrackingPossible())
				->setIsBatchTrackingSupported($this->isBatchDischargeSupported());

			return $status;
		}

		private function _isBatchTrackingPossible() {
			return $this->_getStoreBinding()
				->isBoundToEnGrossWarehouse();
		}

		public function canOrderItemsBeAddedToDocument($localId) { 
			if (empty($localId)) {
				throw new InvalidArgumentException('Order Id must not be empty');
			}

			if ($this->_orderExists($localId)) {
				return $this->_canOrderItemsBeAddedToDocument($localId);				
			} else {
				return false;
			}
		}

		public function setDocumentIssuedForOrder($localId, $documentId, $documentType) { 
			if (empty($localId)) {
				throw new InvalidArgumentException('Order Id must not be empty');
			}

			if ($this->_orderExists($localId)) {
				$this->_setRemoteDocumentData($localId, 
					$documentId, 
					$documentType);				
			} else {
				throw new LocalOrderNotFoundException('id', $localId);
			}
		}

		private function _setRemoteDocumentData($orderId, $documentId, $documentType) {
			$this->_createOrderUpdateService($orderId)
				->setRemoteDocumentData($documentId, $documentType);
		}

		public function lookupRemoteDocumentDataForOrder($localId) { 
			if (empty($localId)) {
				throw new InvalidArgumentException('Order Id must not be empty');
			}

			if ($this->_orderExists($localId)) {
				return $this->_lookupRemoteDocumentData($localId);				
			} else {
				return null;
			}
		}

		private function _lookupRemoteDocumentData($orderId) {
			return $this->_orderResolver
				->lookupRemoteDocumentData($orderId);
		}

		public function getOrderData($localId) { 
			if (empty($localId)) {
				throw new InvalidArgumentException('Order Id must not be empty');
			}

			if ($this->_orderExists($localId)) {
				return $this->_buildRemoteDocumentData($localId);				
			} else {
				throw new LocalOrderNotFoundException('id', $localId);
			}
		}

		private function _buildRemoteDocumentData($orderId) {
			return $this->_createOrderMarshaller($orderId)
				->buildRemoteDocumentData();
		}

		private function _createOrderMarshaller($orderId) {
			return $this->_localToRemoteDocumentMarshallerFactory
				->createForOrder($orderId);
		}

		public function determineOrderDocumentPreRequisitesStatus($localId) { 
			if (empty($localId)) {
				throw new InvalidArgumentException('Order Id must not be empty');
			}

			if ($this->_orderExists($localId)) {
				return $this->_determineOrderDocumentPreRequisitesStatus($localId);	
			} else {
				return null;
			}
		}

		public function isBatchDischargeSupported() { 
			return false;
		}
	}
}