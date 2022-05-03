<?php

use CielIntegration\AnafApiClient;
use CielIntegration\CielController;
use CielIntegration\Integration\Admin\Partner\CustomerCustomFieldsMappingProvider;
use CielIntegration\WithInputSanitization;
use CielIntegration\WithLogging;

/**
 * @property \Session $session
 */
class ControllerExtensionCielCatalogAnafData extends CielController {
	use WithLogging;
	use WithInputSanitization;

	public function assets() {
		$this->_addHeaderScript('extension/ciel_catalog_anaf_data.js', 
			true);
	}

	public function index(&$route, &$data, &$output) {
		$viewData = $this->_getViewData();
		$viewContents = $this->_renderView('extension/ciel_catalog_anaf_data_address_form', 
			$viewData);

		return str_ireplace('</body>', 
			$viewContents . '</body>', 
			$output);
	}
	
	private function _getViewData() {
		$viewData = array();
		$viewData['myc_custom_fields_mapping'] = $this->_getCustomFieldsMapping();
		$viewData['myc_vat_code_lookup_action_url'] = $this->_createRouteUrlRaw('extension/ciel_catalog_anaf_data/lookup', 
			array(
				'myc_nonce' => $this->_generateCsrfToken()
			)
		);

		return $viewData;
	}

	private function _getCustomFieldsMapping() {
		return $this->_getCustomFieldsMappingProvider()
			->getCustomFieldsMapping();
	}

	private function _getCustomFieldsMappingProvider() {
		return new CustomerCustomFieldsMappingProvider($this->registry);
	}

	private function _generateCsrfToken() {
		$token = uniqid('myc_', true);
		$this->session->data['myc_csrf_token'] = $token;
		return $token;
	}

	public function checkout(&$route, &$data, &$output) {
		$viewData = $this->_getViewData();
		$viewContents = $this->_renderView('extension/ciel_catalog_anaf_data_checkout', 
			$viewData);

		return str_ireplace('</body>', 
			$viewContents . '</body>', 
			$output);
	}

	public function checkoutPayment(&$route, &$data, &$output) {
		$viewContents = $this->_renderView('extension/ciel_catalog_anaf_data_checkout_payment', 
			array());
		return $output 
			. $viewContents;
	}

	public function quickCheckoutPayment(&$route, &$data, &$output) {
		$viewData = $this->_getViewData();
		$viewContents = $this->_renderView('extension/ciel_catalog_anaf_data_quick_checkout_payment', 
			$viewData);
		return $output 
			. $viewContents;
	}
	
	public function lookup() {
		if (!$this->_isHttpPost() || !$this->_validateCsrfToken()) {
			die;
		}

		$vatCode = $this->_getVatCodeToLookupFromHttpPost();
		if (empty($vatCode)) {
			die;
		}

		$response = $this->_processLookupVatCode($vatCode);
		$this->_renderJsonToResponseOutput($response);
	}

	private function _processLookupVatCode($vatCode) {
		$response = $this->_createEmptyLookupAjaxResponse();

		try {
			$anafApiClient = $this->_getAnafApiClient();
			$vatPayerData = $anafApiClient->getVatInfoByVatCode($vatCode);

			if ($vatPayerData != null) {
				$response->exists = true;
				$response->info = $vatPayerData->toArray();
			}

			$response->success = true;
			$response->message = null;
		} catch (Exception $exc) {
			$this->_logError($exc, 'Failed to lookup vat code via ANAF.');
		}

		return $response;
	}

	private function _createEmptyLookupAjaxResponse() {
		return $this->_createAjaxResponse(array(
			'exists' => false,
			'info' => null
		));
	}

	private function _getAnafApiClient() {
		return new AnafApiClient();
	}

	private function _validateCsrfToken() {
		return !empty($this->request->get['myc_nonce'])
			&& !empty($this->session->data['myc_csrf_token'])
			&& ($this->request->get['myc_nonce'] 
				== $this->session->data['myc_csrf_token']);
	}

	private function _getVatCodeToLookupFromHttpPost() {
		$vatCode = !empty($this->request->post['myc_vat_code'])
			? $this->request->post['myc_vat_code']
			: null;

		return $this->_sanitizeTextInput($vatCode);
	}
}