<?php
namespace CielIntegration {

    use CielIntegration\Integration\CielIntegrationFactory;
    use Exception;
    use \Loader;
	use \Request;

	/** 
	 * @property \Loader $load 
	 * @property \Request $request
	 * @property \Response $response
	 * @property \Session $session
	 * @property \Url $url
	 * @property \Document $document
	 * */
	class CielController extends \Controller {
		use WithBootstrapper;
		use WithLanguage;
		use WithInputSanitization;

		/**
		 * @var CielIntegrationFactory
		 */
		private $_integrationFactory;

		/**
		 * @var LookupDataProvider
		 */
		private $_lookupDataProvider;

		public function __construct(\Registry $registry) {
			parent::__construct($registry);
			$this->_bootstrap();
			$this->_initIntegration();
		}

		private function _initIntegration() {
			$this->_integrationFactory = new CielIntegrationFactory($this->registry);
		}

		protected function _getStoreBinding() {
			return $this->_integrationFactory
				->getStoreBinding();
		}

		protected function _getRequestMethod() {
			return strtoupper($this->request->server['REQUEST_METHOD']);
		}

		protected function _isHttpPost() {
			return $this->_getRequestMethod() == 'POST';
		}

		protected function _setDocumentTitle($title) {
			$this->document->setTitle($title);
		}

		protected function _setDocumentTitleLangKey($titleLangKey) {
			$this->_setDocumentTitle(
				$this->_t($titleLangKey)
			);
		}

		protected function _addHeaderScript($scriptHref) {
			$fullScriptHref = 'view/javascript/' . $scriptHref;
			$this->document->addScript($fullScriptHref);
		}

		protected function _renderView($viewRoute, $viewData) {
			return $this->load->view($viewRoute, $viewData);
		}

		protected function _renderViewToResponseOutput($viewRoute, $viewData) {
			$contents = $this->_renderView($viewRoute, $viewData);
			$this->response->setOutput($contents);
		}

		protected function _getBaseBreadcrumbs() {
			$data = array();
			$data[] = array(
				'text' => $this->_t('text_home'),
				'href' => $this->_createRouteUrl('common/dashboard')
			);
	
			$data[] = array(
				'text' => $this->_t('text_module'),
				'href' => $this->_createRouteUrl('extension/module')
			);

			return $data;
		}

		protected function _createRouteUrl($route) {
			return $this->url->link($route, 'token=' . $this->_getCurrentSessionToken(), true);
		}

		protected function _getCurrentSessionToken() {
			return $this->session
				->data['token'];
		}

		protected function _renderJsonToResponseOutput($result) {
			$this->response->addHeader('Content-Type: application/json');
			$this->response->setOutput(json_encode($result));
		}

		protected function _renderBreadcrumbs($data) {
			return $this->load->controller('extension/ciel_heading_breadcrumbs', 
				$data);
		}

		protected function _includeLoadingIndicatorScript() {
			$this->_addHeaderScript('extension/ciel_loading_indicator.js');
		}

		protected function _renderLoadingIndicator() {
			return $this->load->controller('extension/ciel_modal_loading_indicator');
		}

		protected function _includeOperationStatusScript() {
			$this->_addHeaderScript('extension/ciel_operation_status.js');
		}

		protected function _getWarehousesForDropdown() {
			$dataSource = null;

			try {
				$dataSource = $this->_getStoreBinding()
					->getAvailableWarehouses();

				foreach ($dataSource as $id => $w) {
					if (!is_object($w['properties'])) {
						$w['properties'] = (object)$w['properties'];
						$dataSource[$id] = $w;
					}
				}
			} catch (Exception $exc) {
				//TODO: log errors
			}

			return $dataSource;
		}

		protected function _getVatQuotasForDropdown() {
			$dataSource = null;

			try {
				$dataSource = $this->_getStoreBinding()
					->getAvailableVatQuotas();
			} catch (Exception $exc) {
				//TODO: log errors
			}

			return $dataSource;
		}

		protected function _createAjaxResponse(array $additionalProps = array()) {
			$response = new \stdClass();
			$response->success = false;
			$response->message = null;

			foreach ($additionalProps as $key => $value) {
				$response->$key = $value;
			}

			return $response;
		}

		protected function _getDocumentTypeName($typeId) {
			return $this->_getLookupDataProvider()
				->getDocumentTypeName($typeId);
		}

		protected function _getSupportedDocumentTypes() {
			return $this->_getLookupDataProvider()
				->getSupportedDocumentTypes();
		}

		protected function _getSupportedStockUpdateModes() {
			return $this->_getLookupDataProvider()
				->getSupportedStockUpdateModes();
		}

		protected function _getSupportedDocumentStatusTypes() {
			return $this->_getLookupDataProvider()
				->getSupportedDocumentStatusTypes();
		}

		protected function _getOpenCartOrderStatuses() {
			return $this->_getLookupDataProvider()
				->getOpenCartOrderStatuses();
		}

		private function _getLookupDataProvider() {
			if ($this->_lookupDataProvider === null) {
				$this->_lookupDataProvider = new LookupDataProvider($this->registry);
			}
			return $this->_lookupDataProvider;
		}
	}
}