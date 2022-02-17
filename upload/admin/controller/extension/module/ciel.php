<?php
use CielIntegration\CielController;
use CielIntegration\WithAdminLayoutLoader;

class ControllerExtensionModuleCiel extends CielController {
	use WithAdminLayoutLoader;

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
		$data = $this->_loadAdminLayout();
		$data['ciel_title'] = $this->_t('ciel_title');

		$this->_renderViewToResponseOutput('extension/module/ciel', 
			$data);
	}
}