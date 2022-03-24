<?php

use CielIntegration\AnafApiClient;
use CielIntegration\CielController;
use CielIntegration\Integration\Admin\Partner\CustomerCustomFieldsMappingProvider;
use CielIntegration\WithInputSanitization;

/**
 * @property \Session $session
 */
class ControllerExtensionCielCatalogAnafData extends CielController {
	use WithInputSanitization;

	public function assets() {
		$this->_addHeaderScript('extension/ciel_catalog_anaf_data.js', 
			true);
	}

	public function index(&$route, &$data, &$output) {
		$viewData = array();
		$viewData['myc_custom_fields_mapping'] = $this->_getCustomFieldsMapping();
		$viewData['myc_vat_code_lookup_action_url'] = $this->_createRouteUrlRaw('extension/ciel_catalog_anaf_data/lookup', 
			array(
				'myc_nonce' => $this->_generateCsrfToken()
			)
		);

		$viewContents = $this->_renderView('extension/ciel_catalog_anaf_data', 
			$viewData);

		return str_ireplace('</body>', 
			$viewContents . '</body>', 
			$output);
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
	
	public function lookup() {
		if (!$this->_isHttpPost() || !$this->_validateCsrfToken()) {
			die;
		}

		$vatCode = $this->_getVatCodeToLookupFromHttpPost();
		if (empty($vatCode)) {
			die;
		}

		$anafApiClient = $this->_getAnafApiClient();
		$response = $this->_createEmptyLookupAjaxResponse();

		$vatPayerData = $anafApiClient->getVatInfoByVatCode($vatCode);

		$response->success = true;
		if ($vatPayerData != null) {
			$response->exists = true;
			$response->info = $vatPayerData->toArray();
		}

		$this->_renderJsonToResponseOutput($response);
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