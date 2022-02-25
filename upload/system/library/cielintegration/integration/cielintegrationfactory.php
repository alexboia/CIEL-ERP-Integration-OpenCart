<?php
namespace CielIntegration\Integration {

    use Ciel\Api\Integration\Binding\CielErpToStoreBinding;
    use CielIntegration\Integration\Binding\OpenCartCielErpToStoreBindingAdapter;
    use CielIntegration\Integration\Binding\OpenCartCielWorkflow;
    use Registry;

	class CielIntegrationFactory {
		/**
		 * @var CielErpToStoreBinding
		 */
		private $_storeBinding;

		private $_articleIntegration;

		private $_orderIntegration;

		private $_partnerIntegration;

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

		public function getWorkflow() {
			if ($this->_workflow === null) {
				$this->_workflow = new OpenCartCielWorkflow($this->_registry);
			}
			return $this->_workflow;
		}
	}
}