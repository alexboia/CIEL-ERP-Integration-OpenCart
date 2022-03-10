<?php
use CielIntegration\CielController;
use CielIntegration\Integration\Admin\WithCielIntegration;

class ControllerExtensionCielMenuEntries extends CielController {
	use WithCielIntegration;

	public function index(&$route, &$data, &$output) {
		if (!empty($data['menus']) && is_array($data['menus'])) {
			$data['menus'][] = $this->_getCielIntegrationMenuItem();
		}
	}

	private function _getCielIntegrationMenuItem() {
		return array(
			'id' => 'menu-ciel-integration',
			'icon' => 'fa-plug',
			'name' => 'Integrare CIEL ERP',
			'href' => '',
			'children' => $this->_getCielIntegrationMenuEntries()
		);
	}

	private function _getCielIntegrationMenuEntries() {
		$items = array(
			array(
				'name' => 'Configurare',
				'href' => $this->_createRouteUrl('extension/module/ciel'),
				'children' => array()
			)
		);

		if ($this->_isStoreBound()) {
			$items[] = array(
				'name' => 'Sincronizare produse',
				'href' => $this->_createRouteUrl('extension/ciel_sync_products'),
				'children' => array()
			);

			$items[] = array(
				'name' => 'Migrare OC Romania',
				'href' => $this->_createRouteUrl('extension/ciel_import_oc_romania'),
				'children' => array()
			);

			$items[] = array(
				'name' => 'Status',
				'href' => $this->_createRouteUrl('extension/ciel_status'),
				'children' => array()
			);
		}

		return $items;
	}

	private function _isStoreBound() {
		return $this->_getStoreBinding()
			->isBound();
	}
}