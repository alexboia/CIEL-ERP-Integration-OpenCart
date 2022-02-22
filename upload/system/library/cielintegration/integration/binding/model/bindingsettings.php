<?php
namespace CielIntegration\Integration\Binding\Model {
    use CielIntegration\CielModel;

	class BindingSettings extends CielModel {
		const SETTINGS_KEY = 'binding_settings';

		const BASE_TABLE_NAME = 'mycciel_oc_binding_settings';

		/**	
		 * @var array|null
		 */
		static $_settings = null;

		public function saveSettings(array $settings) {
			$currentSettings = $this->_loadSettingsIfNeeded();
			if ($currentSettings === null) {
				$this->_initSettings();
			}

			$this->_persistSettings($settings);
		}

		private function _loadSettingsIfNeeded() {
			if (self::$_settings === null) {
				$settings = $this->_readSettings();
				self::$_settings = $settings;
			}
			return self::$_settings;
		}

		private function _readSettings() {
			$dbOperations = $this->_getDbOperations();
			$where = $this->_constructSettingsKeyWhereClause();

			$row = $dbOperations->getOne($this->_getTableName(), 
				$where);

			if (!empty($row) && !empty($row['settings_values'])) {
				return unserialize($row['settings_values']);
			} else {
				return null;
			}
		}

		private function _constructSettingsKeyWhereClause() {
			return array(
				'settings_key' => self::SETTINGS_KEY
			);
		}

		private function _initSettings() {	
			$initialSettings = array();
			$saveData = $this->_constructSettingsDataToSave($initialSettings);

			$dbOperations = $this->_getDbOperations();
			$dbOperations->insert($this->_getTableName(), 
				$saveData);

			self::$_settings = $initialSettings;
		}

		private function _constructSettingsDataToSave($settings) {
			return array(
				'settings_key' => self::SETTINGS_KEY,
				'settings_values' => serialize($settings)	
			);
		}

		private function _persistSettings(array $settings) {
			self::$_settings = $settings;

			$saveData = $this->_constructSettingsDataToSave($settings);
			$where = $this->_constructSettingsKeyWhereClause();

			$dbOperations = $this->_getDbOperations();
			$dbOperations->udpate($this->_getTableName(), 
				$saveData, 
				$where);

			self::$_settings = $settings;
		}

		private function _getTableName() {
			return DB_PREFIX . self::BASE_TABLE_NAME;
		}

		public function getSettings() {
			$this->_loadSettingsIfNeeded();
			return self::$_settings;
		}

		public function clearSettings() {
			$dbOperations = $this->_getDbOperations();
			$dbOperations->delete($this->_getTableName());
			self::$_settings = null;
		}
	}
}