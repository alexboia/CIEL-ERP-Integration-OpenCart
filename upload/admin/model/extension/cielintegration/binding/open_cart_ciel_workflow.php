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

		public function getAddShippingToDocument() {
			$wfSettings = $this->_getWorkflowSettings();
			return isset($wfSettings['add_shipping_to_document']) 
				? $wfSettings['add_shipping_to_document'] == true
				: true;
		}

		public function saveShippingSettings($addShippingToDocument) {
			$this->_saveWorkflowSettigs(array(
				'add_shipping_to_document' => ($addShippingToDocument == true)
			));
		}

		public function getOutOfStockStatusId() {
			$wfSettings = $this->_getWorkflowSettings();
			return isset($wfSettings['out_of_stock_status_id'])
				? intval($wfSettings['out_of_stock_status_id'])
				: 0;
		}

		private function _getWorkflowSettings() {
			$wfSettings = $this->_bindingSettingsModel
				->getWorkflowSettings();

			if ($wfSettings == null) {
				$wfSettings = array();
			}

			return $wfSettings;
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

		public function getPFPersonTypeCustomerGroupId() {
			$wfSettings = $this->_getWorkflowSettings();
			return isset($wfSettings['pf_customer_group_id'])
				? intval($wfSettings['pf_customer_group_id'])
				: 0;
		}

		public function getPJPersonTypeCustomerGroupId() {
			$wfSettings = $this->_getWorkflowSettings();
			return isset($wfSettings['pj_customer_group_id'])
				? intval($wfSettings['pj_customer_group_id'])
				: 0;
		}

		public function savePersonTypeCustomerGroupMapping($pfCustomerGroupId, $pjCustomerGroupId) {
			$this->_saveWorkflowSettigs(array(
				'pf_customer_group_id' => $pfCustomerGroupId,
				'pj_customer_group_id' => $pjCustomerGroupId
			));
		}

		public function getVatCodeCustomFieldId() {
			$wfSettings = $this->_getWorkflowSettings();
			return isset($wfSettings['vat_code_custom_field_id'])
				? intval($wfSettings['vat_code_custom_field_id'])
				: 0;
		}

		public function getRegComNumberCustomFieldId() {
			$wfSettings = $this->_getWorkflowSettings();
			return isset($wfSettings['reg_com_number_custom_field_id'])
				? intval($wfSettings['reg_com_number_custom_field_id'])
				: 0;
		}

		public function getBankAccountCustomFieldId() {
			$wfSettings = $this->_getWorkflowSettings();
			return isset($wfSettings['bank_account_custom_field_id'])
				? intval($wfSettings['bank_account_custom_field_id'])
				: 0;
		}

		public function getBankNameCustomFieldId() {
			$wfSettings = $this->_getWorkflowSettings();
			return isset($wfSettings['bank_name_custom_field_id'])
				? intval($wfSettings['bank_name_custom_field_id'])
				: 0;
		}

		public function saveCustomFieldsMapping($vatCodeCustomFieldId, 
			$regComNumberCustomFieldId, 
			$bankAccountCustomFieldId, 
			$bankNameCustomFieldId) {
			$this->_saveWorkflowSettigs(array(
				'vat_code_custom_field_id' => $vatCodeCustomFieldId,
				'reg_com_number_custom_field_id' => $regComNumberCustomFieldId,
				'bank_account_custom_field_id' => $bankAccountCustomFieldId,
				'bank_name_custom_field_id' => $bankNameCustomFieldId
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