<?php
namespace CielIntegration\Integration {
    use CielIntegration\CielModel;
    use InvalidArgumentException;

	abstract class IntegrationModel extends CielModel {
		const COLUMN_PREFIX = 'mycciel_oc_';

		protected function _add(array $modelInfo) {
			if (empty($modelInfo)) {
				throw new InvalidArgumentException('Model info cannot be empty.');
			}

			$dbOperations = $this->_getDbOperations();
			$saveInfo = $this->_buildInfoToSave($modelInfo);

			$dbOperations->insert($this->_getTableName(), 
				$saveInfo);

			return true;			
		}

		protected function _addAll(array $modelInfos) {
			$dbOperations = $this->_getDbOperations();
			$dbOperations->beginTransaction();

			foreach ($modelInfos as $modelInfo) {
				$this->_add($modelInfo);
			}

			$dbOperations->commitTransaction();
			return true;
		}

		protected function _update(array $modelInfo) {
			if (empty($modelInfo)) {
				throw new InvalidArgumentException('Model info cannot be empty.');
			}

			$modelIdKey = $this->_getIdColumnKey();
			$modelId = isset($modelInfo[$modelIdKey]) 
				? intval($modelInfo[$modelIdKey]) 
				: 0;

			if ($modelId <= 0) {
				throw new InvalidArgumentException('Invalid model ID provided.');
			}

			$dbOperations = $this->_getDbOperations();
			$saveInfo = $this->_buildInfoToSave($modelInfo);
			$where = $this->_createModelIdCondition($modelId);

			$dbOperations->udpate($this->_getTableName(), 
				$saveInfo, 
				$where
			);

			return true;
		}

		protected function _removeByModelId($modelId) {
			$removeModelId = intval($modelId);
			if (empty($removeModelId)) {
				throw new InvalidArgumentException('Invalid model ID provided');
			}

			$dbOperations = $this->_getDbOperations();
			$where = $this->_createModelIdCondition($removeModelId);

			$dbOperations->delete($this->_getTableName(), 
				$where);

			return true;
		}

		protected function _removeAll() {
			$dbOperations = $this->_getDbOperations();
			$dbOperations->delete($this->_getTableName());
			return true;
		}

		protected function _buildInfoToSave(array $modelInfo) {
			$dbSaveInfo = array();
			foreach ($modelInfo as $key => $value) {
				$dbSaveInfo[$this->_getColumnName($key)] = $value;
			}
			return $dbSaveInfo;
		}

		protected function _getOneByModelId($modelId) {
			$selectModelId = intval($modelId);
			if (empty($selectModelId)) {
				throw new InvalidArgumentException('Invalid model ID provided');
			}

			$dbOperations = $this->_getDbOperations();
			$where = $this->_createModelIdCondition($selectModelId);

			$dbModelInfo = $dbOperations->getOne($this->_getTableName(), 
				$where);

			if (!empty($dbModelInfo)) {
				return $this->_buildInfoToReturn($dbModelInfo);
			} else {
				return null;
			}
		}

		protected function _getAllByModelIds(array $modelIds) {
			$dbOperations = $this->_getDbOperations();
			$where = $this->_createModelIdsCondition($modelIds);

			$dbModelInfos = $dbOperations->select($this->_getTableName(), 
				$where);

			if (!empty($dbModelInfos)) {
				return $this->_buildInfosToReturn($dbModelInfos);
			} else {
				return array();
			}
		}

		protected function _getColumnName($columnKey) {
			return self::COLUMN_PREFIX . $columnKey;
		}

		protected function _buildInfoToReturn(array $dbModelInfo) {
			$modelInfo = array();
			foreach ($dbModelInfo as $columnName => $value) {
				$modelInfo[$this->_getColumnKey($columnName)] = $value;
			}
			return $modelInfo;
		}

		protected function _getColumnKey($columnName) {
			return str_ireplace(self::COLUMN_PREFIX, '', $columnName);
		}

		protected function _buildInfosToReturn(array $dbModelInfos) {
			$modelInfos = array();
			foreach ($dbModelInfos as $dbModelInfo) {
				$modelInfos[] = $this->_buildInfoToReturn($dbModelInfo);
			}
			return $modelInfos;	
		}

		protected function _filterLocalIdsForDb($localIds) {
			$filteredLocalIds = array_map('intval', 
				$localIds);
			$filteredLocalIds = array_filter($filteredLocalIds, 
				'empty');
			return $filteredLocalIds;
		}

		private function _createModelIdCondition($modelId) {
			return array(
				$this->_getColumnName($this->_getIdColumnKey()) => $modelId
			);
		}

		private function _createModelIdsCondition(array $modelIds) {
			return array(
				$this->_getColumnName($this->_getIdColumnKey()) => array(
					'value' => $modelIds,
					'operator' => 'IN'
				)
			);
		}

		abstract protected function _getTableName();

		abstract protected function _getIdColumnKey();
	}
}