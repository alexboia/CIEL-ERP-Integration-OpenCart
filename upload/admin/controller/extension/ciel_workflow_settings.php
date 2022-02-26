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

		$data = array_merge($data, array(
			'text_workflow_settings_form_heading' => $this->_t('text_workflow_settings_form_heading')
		));

		return $this->_renderView('extension/ciel_workflow_settings_form', 
			$data);
	}

	private function _getWorkflowSettingsFormData() {
		$workflow = $this->_getWorkflow();
		$storeBinding = $this->_getStoreBinding();

		$data = array(
			'has_connection' => false,

			'wf_in_stock_status_id' => 0,
			'wf_out_of_stock_status_id' => 0,
			'wf_new_tax_rate_customer_group_id' => 0,
			'wf_new_tax_rate_geo_zone_id' => 0,
			'wf_new_product_weight_class_id' => 0,
			'wf_new_product_length_class_id' => 0,

			'stock_statuses' => $this->_getOpenCartStockStatuses(),
			'customer_grouops' => $this->_getOpenCartCustomerGroups(),
			'geo_zones' => $this->_getOpenCartGeoZones(),
			'weight_classes' => $this->_getOpenCartWeightClasses(),
			'length_classes' => $this->_getOpenCartLengthClasses()
		);

		if ($storeBinding->hasConnectionInfo()) {
			$data = array_merge($data, array(
				'has_connection' => true,
				'wf_in_stock_status_id' => $workflow->getInStockStatusId(),
				'wf_out_of_stock_status_id' => $workflow->getOutOfStockStatusId(),
				'wf_new_tax_rate_customer_group_id' => $workflow->getNewTaxRateCustomerGroupId(),
				'wf_new_tax_rate_geo_zone_id' => $workflow->getNewTaxRateGeoZoneId(),
				'wf_new_product_weight_class_id' => $workflow->getNewProductWeightClassId(),
				'wf_new_product_length_class_id' => $workflow->getNewProductLengthClassId()
			));
		}

		return $data;
	}
}