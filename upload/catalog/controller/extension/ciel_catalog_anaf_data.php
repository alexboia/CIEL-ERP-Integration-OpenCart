<?php

use CielIntegration\AnafApiClient;
use CielIntegration\CielController;
use CielIntegration\Integration\Admin\Partner\CustomerCustomFieldsMappingProvider;
use CielIntegration\Integration\Admin\WithCielIntegration;
use CielIntegration\WithInputSanitization;
use CielIntegration\WithLogging;

/**
 * @property \Session $session
 */
class ControllerExtensionCielCatalogAnafData extends CielController {
	use WithLogging;
	use WithInputSanitization;
	use WithCielIntegration;

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
		if ($this->_vatCodeSeemsInvalid($vatCode)) {
			$this->_logDebug('Vat code format is invalid. Will not lookup using ANAF server!');
			return $response;
		}

		if (!$this->_shouldPerformAnafVatCodeLookup()) {
			$this->_logDebug('Vat code lookup disabled by configuration. Will not lookup using ANAF server!');
			$response->success = true;
			return $response;
		}

		try {
			if (!$this->_maybePersonNumericalCode($vatCode)) {
				$anafApiClient = $this->_getAnafApiClient();
				$vatPayerData = $anafApiClient->getVatInfoByVatCode($vatCode);
	
				if ($vatPayerData != null) {
					$response->exists = true;
					$response->info = $vatPayerData->toArray();
				}
			} else {
				$response->exists = true;
				$response->info = array();
			}
			
			$response->performed = true;
			$response->success = true;
			$response->message = null;
		} catch (Exception $exc) {
			$this->_logError($exc, 'Failed to lookup vat code via ANAF.');
		}

		return $response;
	}

	private function _createEmptyLookupAjaxResponse() {
		return $this->_createAjaxResponse(array(
			'performed' => false,
			'exists' => false,
			'info' => null
		));
	}

	private function _vatCodeSeemsInvalid($vatCode) {
		return !$this->_maybeCompanyVatCode($vatCode) 
			&& !$this->_maybePersonNumericalCode($vatCode);
	}

	private function _maybeCompanyVatCode($vatCode) {
		return preg_match('/^([A-Z]{2})?([0-9]{1,9})([0-9]{1})$/', $vatCode) === 1;
	}

	private function _maybePersonNumericalCode($vatCode) {
		return preg_match('/^([0-9]{13})$/', $vatCode) === 1;
	}

	private function _shouldPerformAnafVatCodeLookup() {
		return !$this->_getWorkflow()->getDisableAnafVatCodeLookup();
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
