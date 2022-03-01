<?php
namespace CielIntegration\Integration\Admin\Order {

    use CielIntegration\Integration\Admin\IntegrationService;

	class OrderUpdateService extends IntegrationService {
		/**
		 * @var int
		 */
		private $_orderId;

		public function __construct($orderId, \Registry $registry) {
			parent::__construct($registry);
			$this->_orderId = $orderId;
		}

		public function setRemoteDocumentData($documentId, $documentType) {
			$remoteOrderModel = $this->_getRemoteOrderModel();
			$remoteOrderModel->setRemoteDocumentData($this->_orderId, 
				$documentId, 
				$documentType);
		}

		public function clearRemoteDocumentData() {
			$remoteOrderModel = $this->_getRemoteOrderModel();
			$remoteOrderModel->clearRemoteDocumentData($this->_orderId);
		}
	}
}