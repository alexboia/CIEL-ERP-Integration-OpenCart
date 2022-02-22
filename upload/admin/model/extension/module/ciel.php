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

		$eventModel->addEvent('ciel_add_status_product_editor_tab', 
			'admin/view/catalog/product_form/after', 
			'extension/ciel_status_product_form_tab');
	}

	private function _getEvents() {
		return array(

		);
	}

	/**
	 * @return ModelExtensionEvent
	 */
	private function _getEventModel() {
		$this->load->model('extension/event');
		return $this->model_extension_event;
	}

	private function _installDb() {
		$db = $this->_getDb();
		$tablesSql = $this->_getTablesSql();
		
		foreach ($tablesSql as $tblSql) {
			$db->query($tblSql);
		}
	}

	private function _getTablesSql() {
		$tablesSql = array();

		$tablesSql[] = "CREATE TABLE IF NOT EXISTS `" . DB_PREFIX . "mycciel_oc_binding_settings` (
				`settings_key` VARCHAR(50) NOT NULL COLLATE 'utf8mb4_general_ci',
				`settings_values` TEXT NULL DEFAULT NULL COLLATE 'utf8mb4_general_ci',
				PRIMARY KEY (`settings_key`) USING BTREE
			)
			COLLATE='utf8mb4_general_ci'
			ENGINE=InnoDB;";

		$tablesSql[] = "CREATE TABLE IF NOT EXISTS `" . DB_PREFIX . "mycciel_oc_remote_customer` (
				`mycciel_oc_customer_id` INT(11) NOT NULL,
				`mycciel_oc_remote_partner_code` VARCHAR(255) NULL DEFAULT NULL COLLATE 'utf8_general_ci',
				`mycciel_oc_remote_partner_addr_worksite_id` INT(11) NULL DEFAULT NULL,
				`mycciel_oc_billing_type` VARCHAR(255) NULL DEFAULT NULL COLLATE 'utf8_general_ci',
				`mycciel_oc_billing_company_tax_attribute` VARCHAR(255) NULL DEFAULT NULL COLLATE 'utf8_general_ci',
				`mycciel_oc_billing_company_tax_code` VARCHAR(255) NULL DEFAULT NULL COLLATE 'utf8_general_ci',
				`mycciel_oc_billing_company_bank` VARCHAR(255) NULL DEFAULT NULL COLLATE 'utf8_general_ci',
				`mycciel_oc_billing_company_iban` VARCHAR(255) NULL DEFAULT NULL COLLATE 'utf8_general_ci',
				`mycciel_oc_billing_company_trade_register_number` VARCHAR(255) NULL DEFAULT NULL COLLATE 'utf8_general_ci',
				PRIMARY KEY (`mycciel_oc_customer_id`) USING BTREE
			)
			COLLATE='utf8_general_ci'
			ENGINE=InnoDB;";

		$tablesSql[] = "CREATE TABLE IF NOT EXISTS `" . DB_PREFIX . "mycciel_oc_remote_order` (
				`mycciel_oc_order_id` INT(11) NOT NULL,
				`mycciel_oc_remote_document_id` INT(11) NULL DEFAULT NULL,
				`mycciel_oc_remote_document_type` VARCHAR(255) NULL DEFAULT NULL COLLATE 'utf8_general_ci',
				`mycciel_oc_customer_id` INT(11) NULL DEFAULT NULL,
				`mycciel_oc_remote_partner_code` VARCHAR(255) NULL DEFAULT NULL COLLATE 'utf8_general_ci',
				`mycciel_oc_remote_partner_addr_worksite_id` INT(11) NULL DEFAULT NULL,
				`mycciel_oc_billing_type` VARCHAR(255) NULL DEFAULT NULL COLLATE 'utf8_general_ci',
				`mycciel_oc_billing_company_tax_attribute` VARCHAR(255) NULL DEFAULT NULL COLLATE 'utf8_general_ci',
				`mycciel_oc_billing_company_tax_code` VARCHAR(255) NULL DEFAULT NULL COLLATE 'utf8_general_ci',
				`mycciel_oc_billing_company_bank` VARCHAR(255) NULL DEFAULT NULL COLLATE 'utf8_general_ci',
				`mycciel_oc_billing_company_iban` VARCHAR(255) NULL DEFAULT NULL COLLATE 'utf8_general_ci',
				`mycciel_oc_billing_company_trade_register_number` VARCHAR(255) NULL DEFAULT NULL COLLATE 'utf8_general_ci',
				PRIMARY KEY (`mycciel_oc_order_id`) USING BTREE
			)
			COLLATE='utf8_general_ci'
			ENGINE=InnoDB;";

		$tablesSql[] = "CREATE TABLE IF NOT EXISTS `" . DB_PREFIX . "mycciel_oc_remote_product` (
				`mycciel_oc_product_id` INT(11) NOT NULL,
				`mycciel_oc_remote_id` BIGINT(20) NOT NULL,
				`mycciel_oc_remote_measurement_unit` VARCHAR(255) NULL DEFAULT NULL COLLATE 'utf8_general_ci',
				`mycciel_oc_remote_price_vat_quota_value` FLOAT(10,0) NULL DEFAULT NULL,
				`mycciel_oc_remote_price_vat_option_name` VARCHAR(255) NULL DEFAULT NULL COLLATE 'utf8_general_ci',
				PRIMARY KEY (`mycciel_oc_product_id`) USING BTREE
			)
			COLLATE='utf8_general_ci'
			ENGINE=InnoDB;";

		return $tablesSql;
	}

	public function uninstall() {
		$this->_uninstallPermissions();
		$this->_uninstallEvents();
		$this->_uninstallDb();
	}

	private function _uninstallPermissions() {

	}

	private function _uninstallEvents() {
		$eventModel = $this->_getEventModel();
		$eventModel->deleteEvent('ciel_add_status_product_column');
		$eventModel->deleteEvent('ciel_add_status_product_editor_tab');
	}

	private function _uninstallDb() {

	}

	/**
	 * @return \ModelUserUserGroup
	 */
	private function _getUserGroupModel() {
		$this->load->model('user/user_group');
		return $this->model_user_user_group;
	}
}