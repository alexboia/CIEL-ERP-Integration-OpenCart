<?php
namespace CielIntegration\Integration\Admin {

    use Ciel\Api\Integration\Articles\CielErpArticleIntegration;
    use Ciel\Api\Integration\Binding\CielErpToStoreBinding;
    use Ciel\Api\Integration\Orders\CielErpOrderIntegration;
    use Ciel\Api\Integration\Partners\CielErpPartnerIntegration;
    use CielIntegration\Integration\Admin\Article\OcRomaniaConnectedProductsProvider;
    use CielIntegration\Integration\Admin\Article\OpenCartCielErpLocalArticleAdapter;
    use CielIntegration\Integration\Admin\Binding\OpenCartCielErpToStoreBindingAdapter;
    use CielIntegration\Integration\Admin\Binding\OpenCartCielWorkflow;
    use CielIntegration\Integration\Admin\Order\OpenCartCielErpLocalOrderAdapter;
    use CielIntegration\Integration\Admin\Partner\OpenCartCielErpLocalPartnerAdapter;
    use Registry;

	class CielIntegrationFactory {
		/**
		 * @var CielErpToStoreBinding
		 */
		private $_storeBinding = null;

		/**
		 * @var CielErpArticleIntegration
		 */
		private $_articleIntegration = null;

		/**
		 * @var CielErpOrderIntegration
		 */
		private $_orderIntegration = null;

		/**
		 * @var CielErpPartnerIntegration
		 */
		private $_partnerIntegration = null;

		/**
		 * @var Registry
		 */
		private $_registry;

		/**
		 * @var OpenCartCielWorkflow
		 */
		private $_workflow;

		public function __construct(\Registry $registry) {
			$this->_registry = $registry;
		}

		public function getStoreBinding() {
			if ($this->_storeBinding === null) {
				$this->_storeBinding = new CielErpToStoreBinding(
					new OpenCartCielErpToStoreBindingAdapter(
						$this->_registry
					)
				);
			}
			return $this->_storeBinding;
		}

		public function getArticleIntegration() {
			if ($this->_articleIntegration === null) {
				$this->_articleIntegration = new CielErpArticleIntegration(
					$this->getStoreBinding(),
					new OpenCartCielErpLocalArticleAdapter(
						$this->_registry
					)
				);
			}
			return $this->_articleIntegration;
		}

		public function getPartnerIntegration() {
			if ($this->_partnerIntegration === null) {
				$this->_partnerIntegration = new CielErpPartnerIntegration(
					$this->getStoreBinding(),
					new OpenCartCielErpLocalPartnerAdapter(
						$this->_registry
					)
					);
			}
			return $this->_partnerIntegration;
		}

		public function getOrderIntegration() {
			if ($this->_orderIntegration === null) {
				$this->_orderIntegration = new CielErpOrderIntegration(
					$this->getStoreBinding(), 
					new OpenCartCielErpLocalOrderAdapter(
						$this->_registry
					)
				);
			}
			return $this->_orderIntegration;
		}

		public function getWorkflow() {
			if ($this->_workflow === null) {
				$this->_workflow = new OpenCartCielWorkflow($this->_registry);
			}
			return $this->_workflow;
		}

		public function getOcRomaniaConnectedProductsProvider() {
			return new OcRomaniaConnectedProductsProvider($this->_registry);
		}
	}
}