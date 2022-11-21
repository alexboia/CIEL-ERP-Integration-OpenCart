<?php
namespace CielIntegration {
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
	abstract class CielController extends \Controller {
		use WithBootstrapper;
		use WithLanguage;
		use WithInputSanitization;
		use WithRouteUrl;

		public function __construct(\Registry $registry) {
			parent::__construct($registry);
			$this->_bootstrap();
		}

		protected function _isRoute($route) {
			return isset($this->request->get['route'])
				&& $this->request->get['route'] == $route;
		}

		protected function _getRequestMethod() {
			return strtoupper($this->request->server['REQUEST_METHOD']);
		}

		protected function _isHttpPost() {
			return $this->_getRequestMethod() == 'POST';
		}

		protected function _isHttpGet() {
			return $this->_getRequestMethod() == 'GET';
		}

		protected function _setDocumentTitle($title) {
			$this->document->setTitle($title);
		}

		protected function _setDocumentTitleLangKey($titleLangKey) {
			$this->_setDocumentTitle(
				$this->_t($titleLangKey)
			);
		}

		protected function _addHeaderScript($scriptHref, $catalog = false) {
			$fullScriptHref = 'view/javascript/' . $scriptHref;
			if ($catalog) { 
				$fullScriptHref = 'catalog/' . $fullScriptHref;
			}
			
			$this->document->addScript(
				$this->_addVersionToAssetHref($fullScriptHref)
			);
		}

		private function _addVersionToAssetHref($assetHref) {
			if (stripos($assetHref, '?') === false) {
				$assetHref .= '?';
			} else {
				$assetHref .= '&';
			}

			$assetHref .= '_version=' . ExtensionInfo::VERSION;
			return $assetHref;
		}

		protected function _addStylesheet($stylesheetHref, $catalog = false) {
			$fullStylesheetHref = 'view/stylesheet/' . $stylesheetHref;
			if ($catalog) { 
				$fullStylesheetHref = 'catalog/' . $fullStylesheetHref;
			}

			$this->document->addStyle(
				$this->_addVersionToAssetHref($fullStylesheetHref)
			);
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
				'href' => $this->_createRouteUrl('extension/extension')
			);

			return $data;
		}

		protected function _renderJsonToResponseOutput($result) {
			$this->response->addHeader('Content-Type: application/json');
			$this->response->setOutput(json_encode($result));
		}

		protected function _renderBreadcrumbs(array $data) {
			if (isset($data['add_base']) && $data['add_base'] === true) {
				$breadcrumbsWithBase = $this->_getBaseBreadcrumbs();
				foreach ($data['breadcrumbs'] as $b) {
					$breadcrumbsWithBase[] = $b;
				}
				$data['breadcrumbs'] = $breadcrumbsWithBase;
			}

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

		protected function _includeCommonScript() {
			$this->_addHeaderScript('extension/ciel_common.js');
		}

		protected function _includeCommonStylesheet() {
			$this->_addStylesheet('extension/ciel_common.css');
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
	}
}