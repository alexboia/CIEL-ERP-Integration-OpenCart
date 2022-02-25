<?php
namespace CielIntegration\Integration\Binding\Model {
    use CielIntegration\CielModel;

	class IntegrationSettings extends CielModel {
		const BINDING_SETTINGS_KEY = 'binding_settings';

		const WORKFLOW_SETTINGS_KEY = 'workflow_settings';

		const BASE_TABLE_NAME = 'mycciel_oc_binding_settings';

		/**	
		 * @var array
		 */
		static $_settings = array();

		public function saveBindingSettings(array $settings) {
			$this->_saveSettings(self::BINDING_SETTINGS_KEY, 
				$settings);
		}

		public function _saveSettings($key, array $settings) {
			$currentSettings = $this->_loadSettingsIfNeeded($key);
			if ($currentSettings === null) {
				$this->_initSettings($key);
			}

			$this->_persistSettings($key, 
				$settings);
		}

		private function _loadSettingsIfNeeded($key) {
			if (!isset(self::$_settings[$key])) {
				$settingsForKey = $this->_readSettings($key);
				self::$_settings[$key] = $settingsForKey;
			}
			return self::$_settings[$key];
		}

		private function _readSettings($key) {
			$dbOperations = $this->_getDbOperations();
			$where = $this->_constructSettingsKeyWhereClause($key);

			$row = $dbOperations->getOne($this->_getTableName(), 
				$where);

			if (!empty($row) && !empty($row['settings_values'])) {
				return unserialize($row['settings_values']);
			} else {
				return null;
			}
		}

		private function _constructSettingsKeyWhereClause($key) {
			return array(
				'settings_key' => $key
			);
		}

		private function _initSettings($key) {	
			$initialSettings = array();
			$saveData = $this->_constructSettingsDataToSave($key, 
				$initialSettings);

			$dbOperations = $this->_getDbOperations();
			$dbOperations->insert($this->_getTableName(), 
				$saveData);

			self::$_settings[$key] = 
				$initialSettings;
		}

		private function _constructSettingsDataToSave($key, $settings) {
			return array(
				'settings_key' => $key,
				'settings_values' => serialize($settings)	
			);
		}

		private function _persistSettings($key, array $settings) {
			$where = $this->_constructSettingsKeyWhereClause($key);
			$saveData = $this->_constructSettingsDataToSave($key, 
				$settings);

			$dbOperations = $this->_getDbOperations();
			$dbOperations->udpate($this->_getTableName(), 
				$saveData, 
				$where);

			self::$_settings[$key] = 
				$settings;
		}

		private function _getTableName() {
			return self::BASE_TABLE_NAME;
		}

		public function getBindingSettings() {
			return $this->_getSettingsForKey(self::BINDING_SETTINGS_KEY);
		}

		private function _getSettingsForKey($key) {
			$this->_loadSettingsIfNeeded($key);
			return self::$_settings[$key];
		}

		public function saveWorkflowSettings(array $settings) {
			$this->_saveSettings(self::WORKFLOW_SETTINGS_KEY, 
				$settings);
		}

		public function getWorkflowSettings() {
			return $this->_getSettingsForKey(self::WORKFLOW_SETTINGS_KEY);
		}

		public function clearSettings() {
			$dbOperations = $this->_getDbOperations();
			$dbOperations->delete($this->_getTableName());
			self::$_settings = null;
		}
	}
}