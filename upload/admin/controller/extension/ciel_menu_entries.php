<?php
use CielIntegration\CielController;
use CielIntegration\ExtensionInfo;
use CielIntegration\Integration\Admin\WithCielIntegration;

/**
 * @property \Cart\User $user
 */
class ControllerExtensionCielMenuEntries extends CielController {
	use WithCielIntegration;

	public function index(&$route, &$data, &$output) {
		if (!empty($data['menus']) && is_array($data['menus'])) {
			if ($this->_isExtensionActive()) {
				$menuItem = $this->_getCielIntegrationMenuItem();
				if (!empty($menuItem)) {
					$data['menus'][] = $menuItem;
				}
			}
		}
	}

	private function _isExtensionActive() {
		$extensionModel = $this->_getExtensionModel();
		$installedModules = $extensionModel->getInstalled('module');
		return in_array(ExtensionInfo::NAME, $installedModules);
	}

	private function _getCielIntegrationMenuItem() {
		$entries = $this->_getCielIntegrationMenuEntries();
		return !empty($entries) 
			? array(
				'id' => 'menu-ciel-integration',
				'icon' => 'fa-plug',
				'name' => 'Integrare CIEL ERP',
				'href' => '',
				'children' => $entries
			)
			: null;
	}

	private function _getCielIntegrationMenuEntries() {
		$items = array();
		
		if ($this->user->hasPermission('modify', 'extension/module/ciel')) {
			$items[] = array(
				'name' => 'Configurare',
				'href' => $this->_createRouteUrl('extension/module/ciel'),
				'children' => array()
			);
		}
		
		if ($this->_isStoreBound()) {
			if ($this->user->hasPermission('modify', 'extension/ciel_sync_products')) {
				$items[] = array(
					'name' => 'Sincronizare produse',
					'href' => $this->_createRouteUrl('extension/ciel_sync_products'),
					'children' => array()
				);
			}

			if ($this->user->hasPermission('modify', 'extension/ciel_import_oc_romania')) {
				$items[] = array(
					'name' => 'Migrare OC Romania',
					'href' => $this->_createRouteUrl('extension/ciel_import_oc_romania'),
					'children' => array()
				);
			}

			if ($this->user->hasPermission('access', 'extension/ciel_status')) {
				$items[] = array(
					'name' => 'Status',
					'href' => $this->_createRouteUrl('extension/ciel_status'),
					'children' => array()
				);
			}
		}

		return $items;
	}

	private function _isStoreBound() {
		return $this->_getStoreBinding()
			->isBound();
	}

	/**
	 * @return \ModelExtensionExtension
	 */
	private function _getExtensionModel() {
		$this->load->model('extension/extension');
		return $this->model_extension_extension;
	}
}