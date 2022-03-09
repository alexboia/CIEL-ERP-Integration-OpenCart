<?php
namespace CielIntegration\Integration\Admin {

    use Ciel\Api\CielConfig;
    use Ciel\Api\Data\DocumentStatusType;
    use Ciel\Api\Data\DocumentType;
    use Ciel\Api\Integration\Binding\CielErpToStoreBinding;
    use CielIntegration\Integration\Admin\Article\Model\RemoteArticle;
    use CielIntegration\Integration\Admin\Binding\OpenCartCielWorkflow;
    use CielIntegration\Integration\Admin\Order\Model\RemoteOrder;
    use CielIntegration\Integration\Admin\Partner\Model\RemotePartner;
    use Exception;

	/**
	 * @property \Registry $registry
	 */
	trait WithCielIntegration {
		/**
		 * @var CielIntegrationFactory
		 */
		private $_integrationFactory;

		/**
		 * @return CielIntegrationFactory 
		 */
		protected function _getIntegrationFactory() {
			if ($this->_integrationFactory == null) {
				$this->_integrationFactory = new CielIntegrationFactory($this->registry);
			}
			return $this->_integrationFactory;
		}

		protected function _getPartnerIntegration() {
			return $this->_getIntegrationFactory()
				->getPartnerIntegration();
		}
	
		protected function _getOrderIntegration() {
			return $this->_getIntegrationFactory()
				->getOrderIntegration();
		}
	
		protected function _getArticleIntegration() {
			return $this->_getIntegrationFactory()
				->getArticleIntegration();
		}

		protected function _issueDocumentEnabled() {
			//TODO: lift up to core CIEL integration API
			$issueDocumentType = $this->_getStoreBinding()
				->getIssueDocumentType();
	
			return $issueDocumentType == DocumentType::SaleInvoice 
				|| $issueDocumentType == DocumentType::SaleOrder;
		}

		/**
		 * @return CielErpToStoreBinding 
		 */
		protected function _getStoreBinding() {
			return $this->_getIntegrationFactory()
				->getStoreBinding();
		}

		/**
		 * @return OpenCartCielWorkflow 
		 */
		protected function _getWorkflow() {
			return $this->_getIntegrationFactory()
				->getWorkflow();
		}

		/**
		 * @return CielConfig 
		 */
		protected function _getConfig() {
			return $this->_getStoreBinding()
				->getConfig();
		}

		/**
		 * @return array|null
		 */
		protected function _getWarehousesForDropdown() {
			$dataSource = null;

			try {
				$dataSource = $this->_getStoreBinding()
					->getAvailableWarehouses();

				foreach ($dataSource as $id => $w) {
					if (!is_object($w['properties'])) {
						$w['properties'] = (object)$w['properties'];
						$dataSource[$id] = $w;
					}
				}
			} catch (Exception $exc) {
				//TODO: log errors
			}

			return $dataSource;
		}

		/**
		 * @return array|null
		 */
		protected function _getVatQuotasForDropdown() {
			$dataSource = null;

			try {
				$dataSource = $this->_getStoreBinding()
					->getAvailableVatQuotas();
			} catch (Exception $exc) {
				//TODO: log errors
			}

			return $dataSource;
		}

		protected function _issueDocumentAsValid() {
			return $this->_getStoreBinding()->getIssueDocumentWithStatus() 
				== DocumentStatusType::Valid;
		}

		protected function _issueSaleInvoice() {
			return $this->_getStoreBinding()->getIssueDocumentType() 
				== DocumentType::SaleInvoice;
		}

		protected function _shouldAddDocumentIssuedOrderNoteOnOrderAction() {
			return false;
		}

		protected function _shouldAddDocumentRemovedOrderNoteOnOrderAction() {
			return false;
		}

		/**
		 * @return RemoteArticle 
		 */
		protected function _getRemoteArticleModel() {
			return new RemoteArticle($this->registry);
		}

		/**
		 * @return RemotePartner
		 */
		protected function _getRemotePartnerModel() {
			return new RemotePartner($this->registry);
		}

		/**
		 * @return RemoteOrder
		 */
		protected function _getRemoteOrderModel() {
			return new RemoteOrder($this->registry);
		}
	}
}