<?php
namespace CielIntegration {
	use \Loader;

	/** @property \Loader $load */
	/** @property \Request $request */
	/** @property \Response $response */
	class CielController extends \Controller {
		use WithBootstrapper;
		use WithLanguage;

		public function __construct($registry) {
			parent::__construct($registry);
			$this->_bootstrap();
		}

		protected function _renderView($viewRoute, $viewData) {
			return $this->load->view($viewRoute, $viewData);
		}

		protected function _renderViewToResponseOutput($viewRoute, $viewData) {
			$contents = $this->_renderView($viewRoute, $viewData);
			$this->response->setOutput($contents);
		}
	}
}