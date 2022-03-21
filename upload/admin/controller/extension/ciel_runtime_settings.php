<?php
use CielIntegration\CielController;
use CielIntegration\Integration\Admin\WithCielIntegration;
use CielIntegration\Integration\Admin\WithLookupDataProvider;

class ControllerExtensionCielRuntimeSettings extends CielController {
	use WithLookupDataProvider;
	use WithCielIntegration;

	public function index($data = array()) {
		$data = array_merge($data, 
			$this->_getRuntimeSettingsFormData());

		$data = array_merge($data, array(
			'text_runtime_settings_form_heading' 
				=> $this->_t('text_runtime_settings_form_heading'),
			'lbl_runtime_warehouse_field'
				=> $this->_t('lbl_runtime_warehouse_field'),
			'txt_placeholder_runtime_warehouse_field'
				=> $this->_t('txt_placeholder_runtime_warehouse_field'),
			'lbl_runtime_issue_doctype_field'
				=> $this->_t('lbl_runtime_issue_doctype_field'),
			'txt_placeholder_runtime_issue_doctype_field'
				=> $this->_t('txt_placeholder_runtime_issue_doctype_field'),
			'lbl_runtime_issue_auto_order_status_field'
				=> $this->_t('lbl_runtime_issue_auto_order_status_field'),
			'lbl_runtime_remove_auto_order_status_field'
				=> $this->_t('lbl_runtime_remove_auto_order_status_field'),
			'lbl_runtime_issue_doc_status_field'
				=> $this->_t('lbl_runtime_issue_doc_status_field'),
			'txt_placeholder_runtime_issue_doc_status_field'
				=> $this->_t('txt_placeholder_runtime_issue_doc_status_field'),
			'lbl_runtime_issue_doc_due_days_field'
				=> $this->_t('lbl_runtime_issue_doc_due_days_field'),
			'txt_placeholder_runtime_issue_doc_due_days_field'
				=> $this->_t('txt_placeholder_runtime_issue_doc_due_days_field'),
			'lbl_runtime_use_company_billing_fields_field'
				=> $this->_t('lbl_runtime_use_company_billing_fields_field'),
			'lbl_runtime_shipping_vat_quota_field'
				=> $this->_t('lbl_runtime_shipping_vat_quota_field'),
			'txt_placeholder_runtime_shipping_vat_quota_field'
				=> $this->_t('txt_placeholder_runtime_shipping_vat_quota_field'),
			'lbl_runtime_stock_update_mode_field'
				=> $this->_t('lbl_runtime_stock_update_mode_field')
		));

		return $this->_renderView('extension/ciel_runtime_settings_form', 
			$data);
	}

	private function _getRuntimeSettingsFormData() {
		$storeBinding = $this->_getStoreBinding();
		$data = array(
			'has_connection' => false,

			'runtime_warehouse' => null,
			'runtime_warehouse_code' => null,
			'runtime_warehouse_type' => null,
			'runtime_issue_doc_status' => null,
			'runtime_issue_doc_due_days' => 15,
			'runtime_use_company_billing_fields' => false,

			'runtime_issue_doctype' => null,
			'runtime_issue_auto_order_status' => array(),
			'runtime_remove_auto_order_status' => array(),

			'runtime_shipping_vat_quota_name' => null,
			'runtime_shipping_vat_quota_value' => null,

			'runtime_stock_update_mode' => null,

			'data_warehouses' => array(),
			'data_vat_quotas' => array(),

			'data_available_order_statuses' => $this->_getOpenCartOrderStatuses(),
			'data_available_document_types' => $this->_getSupportedDocumentTypes(),
			'data_available_stock_update_modes' => $this->_getSupportedStockUpdateModes(),
			'data_available_document_status_types' => $this->_getSupportedDocumentStatusTypes()
		);

		if ($storeBinding->hasConnectionInfo()) {
			$warehouse = $storeBinding->getWarehouse();
			$data = array_merge($data, array(
				'has_connection' => true,

				'runtime_warehouse' => $warehouse['id'],
				'runtime_warehouse_code' => $warehouse['code'],
				'runtime_warehouse_type' => $warehouse['type'],

				'runtime_issue_doctype' => $storeBinding->getIssueDocumentType(),
				'runtime_issue_auto_order_status' => $storeBinding->getIssueDocumentWhen(),
				'runtime_remove_auto_order_status' => $storeBinding->getCancelDocumentWhen(),

				'runtime_issue_doc_status' => $storeBinding->getIssueDocumentWithStatus(),
				'runtime_issue_doc_due_days' => $storeBinding->getIssueDocumentWithDueDays(),
				'runtime_use_company_billing_fields' => $storeBinding->getUseExtendedCompanyBillingFields(),

				'runtime_shipping_vat_quota_name' => $storeBinding->getShippingVatQuotaName(),
				'runtime_shipping_vat_quota_value' => $storeBinding->getShippingVatQuotaValue(),

				'runtime_stock_update_mode' => $storeBinding->getStockUpdateMode(),

				'data_warehouses' => $this->_getWarehousesForDropdown(),
				'data_vat_quotas' => $this->_getVatQuotasForDropdown()
			));
		}

		return $data;
	}
}