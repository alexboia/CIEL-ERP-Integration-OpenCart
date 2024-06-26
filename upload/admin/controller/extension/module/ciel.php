<?php

use Ciel\Api\CielConnectionTestResult;
use Ciel\Api\Data\DocumentStatusType;
use Ciel\Api\Data\DocumentType;
use Ciel\Api\Data\WarehouseType;
use CielIntegration\CielController;
use CielIntegration\Integration\Admin\Article\TaxService;
use CielIntegration\Integration\Admin\ShippingService;
use CielIntegration\Integration\Admin\WithLookupDataProvider;
use CielIntegration\Integration\Admin\StockUpdateMode;
use CielIntegration\Integration\Admin\WithCielIntegration;
use CielIntegration\WithAdminLayoutLoader;
use CielIntegration\WithLogging;

class ControllerExtensionModuleCiel extends CielController {
	use WithAdminLayoutLoader;
	use WithLookupDataProvider;
	use WithCielIntegration;
	use WithLogging;

	public function install() {
		$this->_getModel()
			->install();
	}

	public function uninstall() {
		$this->_getModel()
			->uninstall();
	}

	/**
	 * @return \ModelExtensionModuleCiel
	 */
	private function _getModel() {
		$this->load->model('extension/module/ciel');
		return $this->model_extension_module_ciel;
	}

	public function index() {
		//Prepare document assets
		$this->_setDocumentTitleLangKey('ciel_settings_page_title');
		$this->_includeCommonScript();
		$this->_includeLoadingIndicatorScript();
		$this->_includeOperationStatusScript();
		$this->_addHeaderScript('extension/module/ciel.js');

		$storeBinding = $this->_getStoreBinding();
		$hasConnectionInfo = $storeBinding->hasConnectionInfo();

		//Prepare data
		$data = $this->_loadAdminLayout();
		$data['token'] = $this->_getCurrentSessionToken();

		$data['ciel_settings_page_title'] = $this->_t('ciel_settings_page_title');
		$data['ciel_settings_connection_error'] = $this->_t('ciel_settings_connection_error');
		$data['msg_confirm_warehouse_change'] = $this->_t('msg_confirm_warehouse_change');

		$data['txt_save_action'] = $this->_t('button_save');
		$data['url_save_action'] = $this->_createRouteUrl('extension/module/ciel/saveSettings');

		$data['txt_cancel_action'] = $this->_t('button_cancel');
		$data['url_cancel_action'] = $this->_createRouteUrl('extension/extension');

		$data['html_loading_indicator'] = $this->_renderLoadingIndicator();
		$data['html_breadcrumbs'] = $this->_renderBreadcrumbs($this->_getBreadcrumbsData());

		$data['html_connection_settings_form'] = $this->_renderConnectionSettinsForm();
		$data['has_connection_info'] = $hasConnectionInfo;
		$data['has_connection_error'] = false;

		try {
			$data['html_runtime_settings_form'] = $this->_renderRuntimeSettingsForm();
			$data['html_workflow_settings_form'] = $this->_renderWorkflowSettingsForm();
		} catch (Exception $exc) {
			$this->_logError($exc, 'Error retrieving settings');
			$data['html_runtime_settings_form'] = '';
			$data['html_workflow_settings_form'] = '';
			$data['has_connection_error'] = true;
		}

		//Render view
		$this->_renderViewToResponseOutput('extension/module/ciel', 
			$data);
	}

	private function _getBreadcrumbsData() {
		$breadcrumbs = $this->_getBaseBreadcrumbs();
		$breadcrumbs[] = array(
			'text' => $this->_t('ciel_settings_page_title'),
			'href' => $this->_createRouteUrl('extension/module/ciel')
		);

		return array(
			'breadcrumbs' => $breadcrumbs
		);
	}

	private function _renderConnectionSettinsForm() {
		return $this->load->controller('extension/ciel_connection_settings', 
			array());
	}

	private function _renderRuntimeSettingsForm() {
		return $this->load->controller('extension/ciel_runtime_settings',
			array());
	}

	private function _renderWorkflowSettingsForm() {
		return $this->load->controller('extension/ciel_workflow_settings', 
			array());
	}

	public function saveSettings() {
		if ($this->_isHttpPost()) {
			$workflow = $this->_getWorkflow();
			$storeBinding = $this->_getStoreBinding();
			$response = $this->_createAjaxResponse(array(
				'warehouses' => null,
				'customersReset' => false,
				'articlesReset' => false
			));

			$articlesReset = false;
			$customersReset = false;
			$hadConnectionInfo = $storeBinding
				->hasConnectionInfo();

			$hadConnectionError = isset($this->request->post['had_connection_error'])
				? $this->request->post['had_connection_error'] === 'true'
				: false;

			//Connection information
			$bindingEndpoint = isset($this->request->post['myc_connection_endpoint_url']) 
				? $this->_sanitizeUrl($this->request->post['myc_connection_endpoint_url'])
				: '';
			$bindingUsername = isset($this->request->post['myc_connection_username'])
				? $this->_sanitizeTextInput($this->request->post['myc_connection_username'])
				: '';
			$bindingPassword = isset($this->request->post['myc_connection_password'])
				? $this->_sanitizeTextInput($this->request->post['myc_connection_password'])
				: '';
			$bindingSociety = isset($this->request->post['myc_connection_society_code'])
				? $this->_sanitizeTextInput($this->request->post['myc_connection_society_code'])
				: '';
			$bindingTimeoutSeconds = isset($this->request->post['myc_connection_timeout_seconds'])
				? max(0, intval($this->request->post['myc_connection_timeout_seconds']))
				: 10;

			//Warehouse information
			$bindingWarehouse = isset($this->request->post['myc_runtime_warehouse'])
				? $this->_sanitizeTextInput($this->request->post['myc_runtime_warehouse'])
				: '';
			$bindingWarehouseCode = isset($this->request->post['myc_runtime_warehouse_code'])
				? $this->_sanitizeTextInput($this->request->post['myc_runtime_warehouse_code'])
				: '';
			$bindingWarehouseType = isset($this->request->post['myc_runtime_warehouse_type'])
				? $this->_sanitizeTextInput($this->request->post['myc_runtime_warehouse_type'])
				: '';

			//What document to issue and when
			$bindingDocumentType = isset($this->request->post['myc_runtime_issue_doctype'])
				? $this->_sanitizeTextInput($this->request->post['myc_runtime_issue_doctype'])
				: '';
			$bindingDocumentIssueForStatuses = isset($this->request->post['myc_runtime_issue_auto_order_status']) 
					&& is_array($this->request->post['myc_runtime_issue_auto_order_status'])
				? $this->_sanitizeTextInputArray($this->request->post['myc_runtime_issue_auto_order_status'])
				: array();
			$bindingDocumentCancelForStatuses = isset($this->request->post['myc_runtime_remove_auto_order_status']) 
					&& is_array($this->request->post['myc_runtime_remove_auto_order_status'])
				? $this->_sanitizeTextInputArray($this->request->post['myc_runtime_remove_auto_order_status'])
				: array();

			//Issued document properties
			$bindingIssueDocumentWithStatus = isset($this->request->post['myc_runtime_issue_doc_status'])
				? intval($this->request->post['myc_runtime_issue_doc_status'])
				: DocumentStatusType::Temporary;
			$bindingIssueDocumentWithSeries = isset($this->request->post['myc_runtime_issue_doc_series'])
				? $this->_sanitizeTextInput($this->request->post['myc_runtime_issue_doc_series'])
				: null;
			$bindingIssueDocumentWithDueDays = isset($this->request->post['myc_runtime_issue_doc_due_days'])
				? intval($this->request->post['myc_runtime_issue_doc_due_days'])
				: null;

			//Customer options
			$bindingUseExtendedCompanyBillingFields = isset($this->request->post['myc_runtime_use_company_billing_fields'])
				? $this->request->post['myc_runtime_use_company_billing_fields'] == '1'
				: true;

			//Shipping configuration
			$bindingShippingVatQuotaName = isset($this->request->post['myc_runtime_shipping_vat_quota_name']) 
				? $this->_sanitizeTextInput($this->request->post['myc_runtime_shipping_vat_quota_name'])
				: '';
			$bindingShippingVatQuotaValue = isset($this->request->post['myc_runtime_shipping_vat_quota_value'])
				? floatval($this->request->post['myc_runtime_shipping_vat_quota_value'])
				: 0;

			//Misc behaviour
			$bindingMatchVariations = isset($this->request->post['myc_runtime_match_variations'])
				? $this->request->post['myc_runtime_match_variations'] == '1'
				: false;

			$bindingStockUpdateMode = isset($this->request->post['myc_runtime_stock_update_mode'])
				? $this->_sanitizeTextInput($this->request->post['myc_runtime_stock_update_mode'])
				: StockUpdateMode::Manual;

			//Workflow settings
			$wfAddVatOnPaymentToDocument = isset($this->request->post['myc_wf_add_vat_on_payment_to_document'])
				? $this->request->post['myc_wf_add_vat_on_payment_to_document'] === 'yes'
				: false;
			$wfAddShippingToDocument = isset($this->request->post['myc_wf_add_shipping_to_document'])
				? $this->request->post['myc_wf_add_shipping_to_document'] === 'yes'
				: false;
			$wfDisableAnafVatCodeLookup = isset($this->request->post['myc_wf_disable_anaf_vat_code_lookup'])
				? $this->request->post['myc_wf_disable_anaf_vat_code_lookup'] === 'yes'
				: false;

			$wfInStockStatusId = isset($this->request->post['myc_wf_in_stock_status_id'])
				? intval($this->request->post['myc_wf_in_stock_status_id'])
				: 0;
			$wfOutOfStockStatusId = isset($this->request->post['myc_wf_out_of_stock_status_id'])
				? intval($this->request->post['myc_wf_out_of_stock_status_id'])
				: 0;

			$wfPfCustomerGroupId = isset($this->request->post['myc_wf_pf_customer_group_id'])
				? intval($this->request->post['myc_wf_pf_customer_group_id'])
				: 0;
			$wfPjCustomerGroupId = isset($this->request->post['myc_wf_pj_customer_group_id'])
				? intval($this->request->post['myc_wf_pj_customer_group_id'])
				: 0;

			$wfVatCodeCustomFieldId = isset($this->request->post['myc_wf_vat_code_custom_field_id'])
				? intval($this->request->post['myc_wf_vat_code_custom_field_id'])
				: 0;
			$wfRegComNumberCustomFieldId = isset($this->request->post['myc_wf_reg_com_number_custom_field_id'])
				? intval($this->request->post['myc_wf_reg_com_number_custom_field_id'])
				: 0;
			$wfBankAccountCustomFieldId = isset($this->request->post['myc_wf_bank_account_custom_field_id'])
				? intval($this->request->post['myc_wf_bank_account_custom_field_id'])
				: 0;
			$wfBankNameCustomFieldId = isset($this->request->post['myc_wf_bank_name_custom_field_id'])
				? intval($this->request->post['myc_wf_bank_name_custom_field_id'])
				: 0;

			$wfNewTaxRateCustomerGroupId = isset($this->request->post['myc_wf_new_tax_rate_customer_group_id'])
				? intval($this->request->post['myc_wf_new_tax_rate_customer_group_id'])
				: 0;
			$wfNewTaxRateGeoZoneId = isset($this->request->post['myc_wf_new_tax_rate_geo_zone_id'])
				? intval($this->request->post['myc_wf_new_tax_rate_geo_zone_id'])
				: 0;

			$wfNewProductWeightClassId = isset($this->request->post['myc_wf_new_product_weight_class_id'])
				? intval($this->request->post['myc_wf_new_product_weight_class_id'])
				: 0;
			$wfNewProductLengthClassId = isset($this->request->post['myc_wf_new_product_length_class_id'])
				? intval($this->request->post['myc_wf_new_product_length_class_id'])
				: 0;

			if (empty($bindingPassword) && $hadConnectionInfo) {
				$bindingPassword = $storeBinding->getPassword();
			}

			if ($bindingTimeoutSeconds <= 0) {
				$bindingTimeoutSeconds = 10;
			}

			if (empty($bindingEndpoint) 
				|| empty($bindingUsername) 
				|| empty($bindingPassword) 
				|| empty($bindingSociety)) {
				$response->message = $this->_t('msg_err_fill_in_connection_properties');
				$this->_renderJsonToResponseOutput($response);
				return;	
			}

			try {
				$storeBinding->setEndpoint($bindingEndpoint);
				$storeBinding->setCredentials($bindingUsername, 
					$bindingPassword, 
					$bindingSociety);
				$storeBinding
					->setTimeoutSeconds($bindingTimeoutSeconds);				
				$storeBinding
					->save();

				if ($hadConnectionInfo && !$hadConnectionError) {
					if (empty($bindingWarehouse) 
						|| empty($bindingWarehouseCode) 
						|| !WarehouseType::isTypeIdSupported($bindingWarehouseType)) {
						$response->message = $this->_t('mgs_err_fill_in_valid_warehouse');
						$this->_renderJsonToResponseOutput($response);
						return;
					}

					if (!empty($bindingDocumentType) 
						&& !DocumentType::isTypeIdSupported($bindingDocumentType)) {
						$response->message = $this->_t('mgs_err_fill_in_valid_document_type');
						$this->_renderJsonToResponseOutput($response);
						return;
					}

					if (empty($bindingStockUpdateMode) 
						|| !StockUpdateMode::isTypeIdSupported($bindingStockUpdateMode)) {
						$response->message = $this->_t('msg_err_fill_in_valid_stock_update_mode');
						$this->_renderJsonToResponseOutput($response);
						return;
					}

					if (empty($bindingIssueDocumentWithStatus)
						|| !DocumentStatusType::isDocumentStatusTypeSupported($bindingIssueDocumentWithStatus)) {
						$response->message = $this->_t('msg_err_fill_in_valid_document_status_type');
						$this->_renderJsonToResponseOutput($response);
						return;
					}

					if ($bindingIssueDocumentWithDueDays <= 0) {
						$response->message = $this->_t('msg_err_fill_in_valid_document_due_days');
						$this->_renderJsonToResponseOutput($response);
						return;
					}

					if (empty($bindingShippingVatQuotaName) || empty($bindingShippingVatQuotaValue)) {
						$response->message = $this->_t('msg_err_fill_in_valid_shipping_vat_quota');
						$this->_renderJsonToResponseOutput($response);
						return;
					}

					if ($wfInStockStatusId <= 0) {
						$response->message = $this->_t('msg_err_fill_in_valid_in_stock_status_id');
						$this->_renderJsonToResponseOutput($response);
						return;
					}

					if ($wfOutOfStockStatusId <= 0) {
						$response->message = $this->_t('msg_err_fill_in_valid_out_of_stock_status_id');
						$this->_renderJsonToResponseOutput($response);
						return;
					}

					if ($wfNewTaxRateCustomerGroupId <= 0) {
						$response->message = $this->_t('msg_err_fill_in_valid_customer_group_id');
						$this->_renderJsonToResponseOutput($response);
						return;
					}

					if ($wfNewTaxRateGeoZoneId <= 0) {
						$response->message = $this->_t('msg_err_fill_in_valid_geo_zone_id');
						$this->_renderJsonToResponseOutput($response);
						return;
					}

					if ($wfNewProductWeightClassId <= 0) {
						$response->message = $this->_t('msg_err_fill_in_valid_weight_class_id');
						$this->_renderJsonToResponseOutput($response);
						return;
					}

					if ($wfNewProductLengthClassId <= 0) {
						$response->message = $this->_t('msg_err_fill_in_valid_length_class_id');
						$this->_renderJsonToResponseOutput($response);
						return;
					}

					$articlesReset = $this->_resetProductsIfBindingPropertiesChanged($bindingWarehouseCode, 
						$bindingMatchVariations);

					$customersReset = $this->_resetCustomersIfBindingPropertiesChanged($bindingUseExtendedCompanyBillingFields);

					$storeBinding->setWarehouse($bindingWarehouse, 
						$bindingWarehouseCode,  
						$bindingWarehouseType);

					$storeBinding->setOrderPolicy($bindingDocumentType, 
						$bindingDocumentIssueForStatuses, 
						$bindingDocumentCancelForStatuses,
						$bindingIssueDocumentWithStatus,
						$bindingIssueDocumentWithSeries,
						$bindingIssueDocumentWithDueDays,
						$bindingUseExtendedCompanyBillingFields,
						$bindingShippingVatQuotaValue, 
						$bindingShippingVatQuotaName);

					$storeBinding->setArticlePolicy($bindingMatchVariations);
					$storeBinding->setStockPolicy($bindingStockUpdateMode);

					//Save store binding
					$storeBinding->save();
					$this->_reconfigureStoreForBindingConfiguration();

					//Save workflow
					$workflow->saveAdditionalDocumentOptions($wfAddVatOnPaymentToDocument);
					$workflow->saveShippingSettings($wfAddShippingToDocument);
					$workflow->saveAnafIntegrationOptions($wfDisableAnafVatCodeLookup);
					$workflow->saveProductStockStatuses($wfInStockStatusId, 
						$wfOutOfStockStatusId);
					$workflow->savePersonTypeCustomerGroupMapping($wfPfCustomerGroupId, 
						$wfPjCustomerGroupId);
					$workflow->saveCustomFieldsMapping($wfVatCodeCustomFieldId, 
						$wfRegComNumberCustomFieldId, 
						$wfBankAccountCustomFieldId, 
						$wfBankNameCustomFieldId);
					$workflow->saveNewTaxRateOptions($wfNewTaxRateGeoZoneId, 
						$wfNewTaxRateCustomerGroupId);
					$workflow->saveNewProductOptions($wfNewProductWeightClassId, 
						$wfNewProductLengthClassId);

					$response->articlesReset = $articlesReset;
					$response->customersReset = $customersReset;
					$response->message = $this->_t('msg_settings_saved_ok');
					$response->success = true;
				} else {
					$response->warehouses = $this->_getWarehousesForDropdown();
					$response->vatQuotas = $this->_getVatQuotasForDropdown();
					$response->articlesReset = false;
					$response->message = $hadConnectionError 
						? $this->_t('msg_settings_saved_ok_needs_reload')
						: $this->_t('msg_settings_saved_ok');
					$response->success = true;
				}
			} catch (Exception $exc) {
				$response->message = $this->_t('msg_settings_save_failed');
				$this->_logError($exc);
			}

			$this->_renderJsonToResponseOutput($response);
		} else {
			die;
		}
	}

	private function _shouldResetProducts($warehouseCode, $matchVariations) {
		$storeBinding = $this->_getStoreBinding();
		return $warehouseCode != $storeBinding->getWarehouseCode() ||
			$matchVariations != $storeBinding->getMatchArticleVariations();
	}

	private function _resetProductsIfBindingPropertiesChanged($warehouseCode, $matchVariations) {
		$articlesReset = false;

		if ($this->_shouldResetProducts($warehouseCode, $matchVariations)) {
			$this->_disconnectAllArticles();
			$articlesReset = true;
		} else {
			$this->_logDebug('Products reset not required.');
		}

		return $articlesReset;
	}

	private function _disconnectAllArticles() {
		$this->_getArticleIntegration()
			->disconnectAllArticles();
	}

	private function _shouldResetCustomers($useExtendedCompanyBillingFields) {
		return !$useExtendedCompanyBillingFields;
	}

	private function _resetCustomersIfBindingPropertiesChanged($useExtendedCompanyBillingFields) {
		$customersReset = false;
		
		if ($this->_shouldResetCustomers($useExtendedCompanyBillingFields)) {
			$this->_removeCustomAddressBillingDataForAllConnectedPartners();
			$customersReset = true;
		} else {
			$this->_logDebug('Customers billing data reset not required.');
		}

		return $customersReset;
	}

	private function _removeCustomAddressBillingDataForAllConnectedPartners() {
		$this->_getPartnerIntegration()
			->removeCustomAddressBillingDataForAllConnectedPartners();
	}

	private function _reconfigureStoreForBindingConfiguration() {
		$this->_reconfigureShippingIfNeeded();
		$this->_reconfigureStockUpdateScheduling();
	}

	private function _reconfigureShippingIfNeeded() {
		$storeBinding = $this->_getStoreBinding();
		$storeBinding->setupShippingIfNeeded();
		$this->_updateStoreShippingTaxClass();
	}

	private function _updateStoreShippingTaxClass() {
		$storeBinding = $this->_getStoreBinding();
		$shippingService = $this->_getShippingService();
		$taxService = $this->_getTaxService();

		$taxClass = $taxService->getOrCreateTaxClass('Livrare Taxabila', 
			$storeBinding->getShippingVatQuotaName(), 
			$storeBinding->getShippingVatQuotaValue());

		$taxClassId = intval($taxClass['tax_class_id']);
		$shippingService->setTaxClassIdForActiveShippingMethods($taxClassId);
	}

	private function _reconfigureStockUpdateScheduling() {
		return;
	}

	public function testConnection() {
		if ($this->_isHttpPost()) {
			$response = $this->_createAjaxResponse();
			$storeBinding = $this->_getStoreBinding();

			$bindingEndpoint = isset($this->request->post['myc_connection_endpoint_url']) 
				? $this->_sanitizeUrl($this->request->post['myc_connection_endpoint_url'])
				: '';
			$bindingUsername = isset($this->request->post['myc_connection_username'])
				? $this->_sanitizeTextInput($this->request->post['myc_connection_username'])
				: '';
			$bindingPassword = isset($this->request->post['myc_connection_password'])
				? $this->_sanitizeTextInput($this->request->post['myc_connection_password'])
				: '';
			$bindingSociety = isset($this->request->post['myc_connection_society_code'])
				? $this->_sanitizeTextInput($this->request->post['myc_connection_society_code'])
				: '';
			$bindingTimeoutSeconds = isset($this->request->post['myc_connection_timeout_seconds'])
				? intval($this->request->post['myc_connection_timeout_seconds'])
				: 30;

			if (empty($bindingPassword) && $storeBinding->hasConnectionInfo()) {
				$bindingPassword = $storeBinding->getPassword();
			}

			if ($bindingTimeoutSeconds <= 0) {
				$bindingTimeoutSeconds = 10;
			}

			if (!empty($bindingEndpoint) 
				&& !empty($bindingUsername) 
				&& !empty($bindingPassword) 
				&& !empty($bindingSociety)) {
				$this->_logDebug('Begin testing NextUp ERP connection...');

				try {
					$result = $this->_processTestCielWebServiceConnection($bindingEndpoint, 
						$bindingUsername, 
						$bindingPassword, 
						$bindingSociety,
						$bindingTimeoutSeconds);

					$message = null;
					$success = false;

					switch ($result) {
						case CielConnectionTestResult::ERR_CONNECTION_TEST_OK:
							$message = $this->_t('msg_connection_test_ok');
							$success = true;
							break;
						default:
							$message = $this->_t('msg_connection_test_failed');
							break;
					}

					$this->_logDebug(sprintf('NextUp ERP connection test result: %s.', 
						$message));
	
					$response->message = $message;
					$response->success = $success;
				} catch (Exception $exc) {
					$this->_logError($exc);
					$response->message = $this->_t('msg_connection_test_failed');
				}
			} else {
				$response->message = $this->_t('msg_err_fill_in_connection_properties');
			}

			$this->_logDebug('Done testing NextUp ERP connection.');
			$this->_renderJsonToResponseOutput($response);
		} else {
			die;
		}
	}

	private function _processTestCielWebServiceConnection($endpoint, 
			$userName, 
			$password, 
			$society,
			$timeoutSeconds) {
		$storeBinding = $this->_getStoreBinding();
		$testerService = $storeBinding->createConnectionTesterService();
		
		$result = $testerService->testConnection($endpoint, 
			$userName, 
			$password, 
			$society, 
			$timeoutSeconds);

		return $result;
	}

	private function _getShippingService() {
		return new ShippingService($this->registry);
	}

	private function _getTaxService() {
		return new TaxService($this->registry);
	}
}