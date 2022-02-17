<?php
use CielIntegration\CielModel;

class ModelExtensionModuleCiel extends CielModel {
	public function install() {
		$this->_installDb();
		$this->_installPermissions();
		$this->_installEvents();
	}

	private function _installPermissions() {
		$userGroupModel = $this->_getUserGroupModel();
		$userGroups = $userGroupModel->getUserGroups();

		$adminUserGroupId = null;
		foreach ($userGroups as $uGroup) {
			if ($uGroup['name'] === 'Administrator') {
				$adminUserGroupId = $uGroup['user_group_id'];
				break;
			}
		}

		if (!is_null($adminUserGroupId)) {
			$userGroupModel->addPermission(
				$adminUserGroupId,
				"access",
				"extension/ciel_connect_products"
			);

			$userGroupModel->addPermission(
				$adminUserGroupId,
				"modify",
				"extension/ciel_connect_products"
			);
		}
	}

	private function _installEvents() {
		$eventModel = $this->_getEventModel();
		$eventModel->addEvent('ciel_add_status_product_column', 
			'admin/view/catalog/product_list/after', 
			'extension/ciel_status_product_column');
	}

	/**
	 * @return ModelExtensionEvent
	 */
	private function _getEventModel() {
		$this->load->model('extension/event');
		return $this->model_extension_event;
	}

	private function _installDb() {

	}

	public function uninstall() {
		$this->_uninstallEvents();
	}

	private function _uninstallEvents() {
		$eventModel = $this->_getEventModel();
		$eventModel->deleteEvent('ciel_add_status_product_column');
	}

	/**
	 * @return \ModelUserUserGroup
	 */
	private function _getUserGroupModel() {
		$this->load->model('user/user_group');
		return $this->model_user_user_group;
	}
}