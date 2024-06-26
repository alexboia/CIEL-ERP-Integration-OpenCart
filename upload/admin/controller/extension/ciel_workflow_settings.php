<?php
use CielIntegration\CielController;
use CielIntegration\Integration\Admin\WithCielIntegration;
use CielIntegration\Integration\Admin\WithLookupDataProvider;

class ControllerExtensionCielWorkflowSettings extends CielController {
	use WithLookupDataProvider;
	use WithCielIntegration;

	public function index($data = array()) {
		$data = array_merge($data, 
			$this->_getWorkflowSettingsFormData());

		$data = $this->_loadTexts($data, array(
			'text_workflow_settings_form_heading',
			'lbl_txt_none',
			'lbl_add_vat_on_payment_to_document_field',
			'lbl_add_shipping_to_document_field',
			'lbl_disable_anaf_vat_code_lookup_field',
			'lbl_in_stock_status_id_field',
			'lbl_out_of_stock_status_id_field',
			'lbl_pf_customer_group_id_field',
			'lbl_pj_customer_group_id_field',
			'lbl_vat_code_custom_field_id_field',
			'lbl_reg_com_number_custom_field_id_field',
			'lbl_bank_account_custom_field_id_field',
			'lbl_bank_name_custom_field_id_field',
			'lbl_new_tax_rate_customer_group_id_field',
			'lbl_new_tax_rate_geo_zone_id_field',
			'lbl_new_product_weight_class_id_field',
			'lbl_new_product_length_class_id_field'
		));

		return $this->_renderView('extension/ciel_workflow_settings_form', 
			$data);
	}

	private function _getWorkflowSettingsFormData() {
		$workflow = $this->_getWorkflow();
		$storeBinding = $this->_getStoreBinding();

		$data = array(
			'has_connection' => false,

			'wf_add_shipping_to_document' => false,
			'wf_add_vat_on_payment_to_document' => false,
			'wf_disable_anaf_vat_code_lookup' => false,

			'wf_in_stock_status_id' => 0,
			'wf_out_of_stock_status_id' => 0,

			'wf_pf_customer_group_id' => 0,
			'wf_pj_customer_group_id' => 0,
			
			'wf_vat_code_custom_field_id' => 0,
			'wf_reg_com_number_custom_field_id' => 0,
			'wf_bank_account_custom_field_id' => 0,
			'wf_bank_name_custom_field_id' => 0,

			'wf_new_tax_rate_customer_group_id' => 0,
			'wf_new_tax_rate_geo_zone_id' => 0,
			'wf_new_product_weight_class_id' => 0,
			'wf_new_product_length_class_id' => 0,

			'stock_statuses' => $this->_getOpenCartStockStatuses(),
			'customer_groups' => $this->_getOpenCartCustomerGroups(),
			'customer_custom_fields' => $this->_getOpenCartCustomerCustomFields(),
			'geo_zones' => $this->_getOpenCartGeoZones(),
			'weight_classes' => $this->_getOpenCartWeightClasses(),
			'length_classes' => $this->_getOpenCartLengthClasses()
		);

		if ($storeBinding->hasConnectionInfo()) {
			$data = array_merge($data, array(
				'has_connection' => true,

				'wf_add_shipping_to_document' => $workflow->getAddShippingToDocument(),
				'wf_add_vat_on_payment_to_document' => $workflow->getAddVatOnPaymentToDocument(),
				'wf_disable_anaf_vat_code_lookup' => $workflow->getDisableAnafVatCodeLookup(),

				'wf_in_stock_status_id' => $workflow->getInStockStatusId(),
				'wf_out_of_stock_status_id' => $workflow->getOutOfStockStatusId(),

				'wf_pf_customer_group_id' => $workflow->getPFPersonTypeCustomerGroupId(),
				'wf_pj_customer_group_id' => $workflow->getPJPersonTypeCustomerGroupId(),

				'wf_vat_code_custom_field_id' => $workflow->getVatCodeCustomFieldId(),
				'wf_reg_com_number_custom_field_id' => $workflow->getRegComNumberCustomFieldId(),
				'wf_bank_account_custom_field_id' => $workflow->getBankAccountCustomFieldId(),
				'wf_bank_name_custom_field_id' => $workflow->getBankNameCustomFieldId(),

				'wf_new_tax_rate_customer_group_id' => $workflow->getNewTaxRateCustomerGroupId(),
				'wf_new_tax_rate_geo_zone_id' => $workflow->getNewTaxRateGeoZoneId(),
				'wf_new_product_weight_class_id' => $workflow->getNewProductWeightClassId(),
				'wf_new_product_length_class_id' => $workflow->getNewProductLengthClassId()
			));
		}

		return $data;
	}
}