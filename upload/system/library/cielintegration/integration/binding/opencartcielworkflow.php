<?php
namespace CielIntegration\Integration\Binding {

    use CielIntegration\Integration\Binding\Model\IntegrationSettings;

	class OpenCartCielWorkflow {
		/**
		 * @var IntegrationSettings
		 */
		private $_bindingSettingsModel;

		public function __construct(\Registry $registry) {
			$this->_bindingSettingsModel = new IntegrationSettings($registry);
		}

		public function getOutOfStockStatusId() {
			$wfSettings = $this->_bindingSettingsModel
				->getWorkflowSettings();

			return isset($wfSettings['out_of_stock_status_id'])
				? intval($wfSettings['out_of_stock_status_id'])
				: 0;
		}

		public function getInStockStatusId() {
			$wfSettings = $this->_bindingSettingsModel
				->getWorkflowSettings();

			return isset($wfSettings['in_stock_status_id'])
				? intval($wfSettings['in_stock_status_id'])
				: 0;
		}

		public function setProductStockStatuses($inStockStatusId, $outOfStockStatusId) {
			$wfSettings = array(
				'in_stock_status_id' => $inStockStatusId,
				'out_of_stock_status_id' => $outOfStockStatusId
			);
			$this->_bindingSettingsModel
				->saveWorkflowSettings($wfSettings);
		}
	}
}