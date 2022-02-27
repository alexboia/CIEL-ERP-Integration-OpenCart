<?php
namespace CielIntegration\Integration\Admin {

    use Ciel\Api\CielConfig;
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
		private function _getIntegrationFactory() {
			if ($this->_integrationFactory == null) {
				$this->_integrationFactory = new CielIntegrationFactory($this->registry);
			}
			return $this->_integrationFactory;
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