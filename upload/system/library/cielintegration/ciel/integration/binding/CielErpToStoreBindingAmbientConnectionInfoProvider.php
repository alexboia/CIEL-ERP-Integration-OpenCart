<?php
namespace Ciel\Api\Integration\Binding {

    use Ciel\Api\CielClientAmbientConnectionInfoProvider;
    use Ciel\Api\CielClientConnectionInfo;

	class CielErpToStoreBindingAmbientConnectionInfoProvider implements CielClientAmbientConnectionInfoProvider {
		/**
		 * @var CielErpToStoreBinding
		 */
		private $_storeBinding;

		public function __construct(CielErpToStoreBinding $storeBinding) {
			$this->_storeBinding = $storeBinding;
		}

		public function getAmbientConnectionInfo() { 
			$options = new CielClientConnectionInfo($this->_storeBinding->getEndpoint(), 
				$this->_storeBinding->getUserName(),
				$this->_storeBinding->getPassword(), 
				$this->_storeBinding->getSociety(),
				$this->_storeBinding->getTimeoutSeconds());

			return $options;
		}
	}
}