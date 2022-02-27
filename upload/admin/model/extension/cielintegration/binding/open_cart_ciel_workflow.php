<?php
namespace CielIntegration\Integration\Admin\Binding {

    use CielIntegration\Integration\Admin\Binding\Model\IntegrationSettings;

	class OpenCartCielWorkflow {
		/**
		 * @var IntegrationSettings
		 */
		private $_bindingSettingsModel;

		public function __construct(\Registry $registry) {
			$this->_bindingSettingsModel = new IntegrationSettings($registry);
		}

		public function getOutOfStockStatusId() {
			$wfSettings = $this->_getWorkflowSettings();
			return isset($wfSettings['out_of_stock_status_id'])
				? intval($wfSettings['out_of_stock_status_id'])
				: 0;
		}

		private function _getWorkflowSettings() {
			return $this->_bindingSettingsModel
				->getWorkflowSettings();
		}

		public function getInStockStatusId() {
			$wfSettings = $this->_getWorkflowSettings();
			return isset($wfSettings['in_stock_status_id'])
				? intval($wfSettings['in_stock_status_id'])
				: 0;
		}

		public function saveProductStockStatuses($inStockStatusId, $outOfStockStatusId) {
			$this->_saveWorkflowSettigs(array(
				'in_stock_status_id' => $inStockStatusId,
				'out_of_stock_status_id' => $outOfStockStatusId
			));
		}

		private function _saveWorkflowSettigs($modifiedWfSettings) {
			$wfSettings = array_merge($this->_getWorkflowSettings(), 
				$modifiedWfSettings);
			$this->_bindingSettingsModel
				->saveWorkflowSettings($wfSettings);
		}

		public function getNewProductWeightClassId() {
			$wfSettings = $this->_getWorkflowSettings();
			return isset($wfSettings['new_product_weight_class_id'])
				? intval($wfSettings['new_product_weight_class_id'])
				: 0;
		}

		public function getNewProductLengthClassId() {
			$wfSettings = $this->_getWorkflowSettings();
			return isset($wfSettings['new_product_length_class_id'])
				? intval($wfSettings['new_product_length_class_id'])
				: 0;
		}

		public function saveNewProductOptions($weightClassId, $lengthClassId) {
			$this->_saveWorkflowSettigs(array(
				'new_product_weight_class_id' => $weightClassId,
				'new_product_length_class_id' => $lengthClassId
			));
		}

		public function getNewTaxRateCustomerGroupId() {
			$wfSettings = $this->_getWorkflowSettings();
			return isset($wfSettings['new_tax_rate_customer_group_id'])
				? intval($wfSettings['new_tax_rate_customer_group_id'])
				: 0;
		}

		public function getNewTaxRateGeoZoneId() {
			$wfSettings = $this->_getWorkflowSettings();
			return isset($wfSettings['new_tax_rate_geo_zone_id'])
				? intval($wfSettings['new_tax_rate_geo_zone_id'])
				: 0;
		}

		public function saveNewTaxRateOptions($geoZoneId, $customerGroupId) {
			$this->_saveWorkflowSettigs(array(
				'new_tax_rate_geo_zone_id' => $geoZoneId,
				'new_tax_rate_customer_group_id' => $customerGroupId
			));
		}
	}
}