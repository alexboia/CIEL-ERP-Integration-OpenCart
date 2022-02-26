<?php
namespace CielIntegration\Integration\Admin {

    use Ciel\Api\Integration\Binding\CielErpToStoreBinding;
    use CielIntegration\Integration\Admin\Binding\OpenCartCielWorkflow;
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
	}
}