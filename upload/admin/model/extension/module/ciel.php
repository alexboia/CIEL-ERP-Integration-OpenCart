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
		$adminUserGroupId = $this->_resolveAdminGroupUserId();

		if (!is_null($adminUserGroupId)) {
			foreach ($this->_getPermissionRoutes() as $route) {
				$userGroupModel->addPermission(
					$adminUserGroupId,
					'access',
					$route
				);
				$userGroupModel->addPermission(
					$adminUserGroupId,
					'modify',
					$route
				);
			}
		}
	}

	private function _resolveAdminGroupUserId() {
		$userGroupModel = $this->_getUserGroupModel();
		$userGroups = $userGroupModel->getUserGroups();

		$adminUserGroupId = null;
		foreach ($userGroups as $uGroup) {
			if ($uGroup['name'] === 'Administrator') {
				$adminUserGroupId = $uGroup['user_group_id'];
				break;
			}
		}

		return $adminUserGroupId;
	}

	private function _getPermissionRoutes() {
		return array(
			'extension/ciel_connect_products',
			'extension/ciel_product_actions',
			'extension/ciel_order_actions',
			'extension/ciel_sync_products',
			'extension/ciel_import_oc_romania',
			'extension/ciel_import_new_products',
			'extension/ciel_export_for_nextup_import',
			'extension/ciel_status'
		);
	}

	private function _installEvents() {
		$eventModel = $this->_getEventModel();

		foreach ($this->_getEvents() as $evt) {
			$eventModel->addEvent($evt['code'], 
				$evt['trigger'], 
				$evt['action'], 
				1);
		}
	}

	private function _getEvents() {
		return array(
			array(
				'code' =>'ciel_status_product_column',
				'trigger' => 'admin/view/catalog/product_list/after',
				'action' => 'extension/ciel_status_product_column',
			),
			array(
				'code' => 'ciel_product_editor_tab_assets',
				'trigger' => 'admin/controller/common/header/before',
				'action' => 'extension/ciel_status_product_form_tab/assets'
			),
			array(
				'code' => 'ciel_product_editor_tab',
				'trigger' => 'admin/view/catalog/product_form/after',
				'action' => 'extension/ciel_status_product_form_tab'
			),

			array(
				'code' => 'ciel_status_order_column',
				'trigger' => 'admin/view/sale/order_list/after',
				'action' => 'extension/ciel_status_order_column'
			),
			array(
				'code' => 'ciel_order_info_tab_assets',
				'trigger' => 'admin/controller/common/header/before',
				'action' => 'extension/ciel_status_order_info_tab/assets'
			),
			array(
				'code' => 'ciel_order_info_tab',
				'trigger' => 'admin/view/sale/order_info/after',
				'action' => 'extension/ciel_status_order_info_tab'
			),

			array(
				'code' => 'ciel_status_customer_column',
				'trigger' => 'admin/view/customer/customer_list/after',
				'action' => 'extension/ciel_status_customer_column'
			),
			array(
				'code' => 'ciel_customer_editor_tab',
				'trigger' => 'admin/view/customer/customer_form/after',
				'action' => 'extension/ciel_status_customer_form_tab'
			),
			array(
				'code' => 'ciel_menu_entries',
				'trigger' => 'admin/view/common/column_left/before',
				'action' => 'extension/ciel_menu_entries'
			),

			array(
				'code' => 'ciel_after_customer_add',
				'trigger' => 'admin/model/customer/customer/addCustomer/after',
				'action' => 'extension/ciel_custom_fields_sync/afterAdd'
			),
			array(
				'code' => 'ciel_after_customer_edit',
				'trigger' => 'admin/model/customer/customer/editCustomer/after',
				'action' => 'extension/ciel_custom_fields_sync/afterEdit'
			),

			array(
				'code' => 'ciel_after_add_order_history',
				'trigger' => 'catalog/model/checkout/order/addOrderHistory/after',
				'action' => 'extension/ciel_catalog_process_order_status_change'
			),

			array(
				'code' => 'ciel_after_checkout_order_add',
				'trigger' => 'catalog/model/checkout/order/addOrder/after',
				'action' => 'extension/ciel_order_custom_fields_sync/afterAdd'
			),
			array(
				'code' => 'ciel_after_checkout_order_edit',
				'trigger' => 'catalog/model/checkout/order/editOrder/after',
				'action' => 'extension/ciel_order_custom_fields_sync/afterEdit'
			),

			array(
				'code' => 'ciel_catalog_anaf_data_assets',
				'trigger' => 'catalog/controller/common/header/before',
				'action' => 'extension/ciel_catalog_anaf_data/assets'
			),
			array(
				'code' => 'ciel_catalog_anaf_data_assets_qc',
				'trigger' => 'catalog/controller/quick_checkout/header/before',
				'action' => 'extension/ciel_catalog_anaf_data/assets'
			),
			array(
				'code' => 'ciel_catalog_anaf_data_addr',
				'trigger' => 'catalog/view/*/template/account/address_form/after',
				'action' => 'extension/ciel_catalog_anaf_data'
			),
			array(
				'code' => 'ciel_catalog_anaf_data_ck_root',
				'trigger' => 'catalog/view/*/template/checkout/checkout/after',
				'action' => 'extension/ciel_catalog_anaf_data/checkout'
			),
			array(
				'code' => 'ciel_catalog_anaf_data_ck_paddr',
				'trigger' => 'catalog/view/*/template/checkout/payment_address/after',
				'action' => 'extension/ciel_catalog_anaf_data/checkoutPayment'
			),
			array(
				'code' => 'ciel_catalog_anaf_data_qc_paddr',
				'trigger' => 'catalog/view/*/template/quick_checkout/address/after',
				'action' => 'extension/ciel_catalog_anaf_data/quickCheckoutPayment'
			)
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
				`mycciel_oc_remote_batch_tracking_enabled` SMALLINT(6) NOT NULL,
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
		$userGroupModel = $this->_getUserGroupModel();
		$adminUserGroupId = $this->_resolveAdminGroupUserId();

		if (!is_null($adminUserGroupId)) {
			foreach ($this->_getPermissionRoutes() as $route) {
				$userGroupModel->removePermission($adminUserGroupId, 
					'access', 
					$route
				);
				$userGroupModel->removePermission($adminUserGroupId, 
					'modify', 
					$route
				);
			}
		}
	}

	private function _uninstallEvents() {
		$eventModel = $this->_getEventModel();

		foreach ($this->_getEvents() as $evt) {
			$eventModel->deleteEvent($evt['code']);
		}
	}

	private function _uninstallDb() {
		$tableNames = array(
			'mycciel_oc_binding_settings',
			'mycciel_oc_remote_customer',
			'mycciel_oc_remote_order',
			'mycciel_oc_remote_product'
		);

		foreach ($tableNames as $tableName) {
			$this->_dropTable($tableName);
		}
	}

	private function _dropTable($tableName) {
		$db = $this->_getDb();
		$db->query('DROP TABLE IF EXISTS `' . DB_PREFIX . $tableName . '`;');
	}

	/**
	 * @return \ModelUserUserGroup
	 */
	private function _getUserGroupModel() {
		$this->load->model('user/user_group');
		return $this->model_user_user_group;
	}	
}