<?php
namespace CielIntegration\Wrappers {
	class DbOperations {
		/**
		 * @var \DB
		 */
		private $_db;

		private $_isTransactionStarted = false;

		public function __construct(\DB $db) {
			$this->_db = $db;
		}

		public function beginTransaction() {
			$this->_db->query('START TRANSACTION;');
			$this->_isTransactionStarted = true;
		}

		public function commitTransaction() {
			if ($this->_isTransactionStarted) {
				$this->_db->query('COMMIT;');
				$this->_isTransactionStarted = false;
			}
		}

		public function rollbackTransaction() {
			if ($this->_isTransactionStarted) {
				$this->_db->query('ROLLBACK;');
				$this->_isTransactionStarted = false;
			}
		}

		public function insert($table, array $data) {
			$insertTable = $this->_getPrefixedTableName($table);

			$columns = $this->_getColumnsForInsert($data);
			$values = $this->_getValuesForInsert($data);

			$insertQuery = sprintf(
				'INSERT INTO %s (%s) VALUES (%s)',
					$insertTable,
					join(',', $columns),
					join(',', $values)
			);

			$this->_db->query($insertQuery);			
			
			return $this->_db
				->getLastId();
		}

		private function _getColumnsForInsert(array $data) {
			return array_map(function($column) {
				return $this->_quoteIdentifier($column);
			}, array_keys($data));
		}

		private function _getValuesForInsert(array $data) {
			return array_map(function($value) {
				return $this->_prepareQuotedValueForQuery($value);
			}, array_values($data));
		}

		private function _getPrefixedTableName($table) {
			return $this->_quoteIdentifier(DB_PREFIX . $table);
		}

		private function _quoteIdentifier($identifier) {
			return '`' . $identifier . '`';
		}

		private function _prepareQuotedValueForQuery($value) {
			if (is_array($value)) {
				$value = array_map(function($vItem) {
					return $this->_prepareSingleQuotedValueForQuery($vItem);
				}, $value);

				return '(' . join(', ', $value) . ')';
			} else {
				return $this->_prepareSingleQuotedValueForQuery($value);
			}
		}

		private function _prepareSingleQuotedValueForQuery($value) {
			return !is_null($value) 
				? '"' . $this->_db->escape($value) . '"' 
				: 'NULL';
		}

		public function udpate($table, array $data, $where, $limit = 0) {
			$updateColumnParts = array();
			$udpateTable = $this->_getPrefixedTableName($table);
			$condition = $this->_composeQueryCondition($where);

			foreach ($data as $column => $value) {
				$updateColumnParts[] = $this->_composeColumnValueAssignmentPart($column, 
					$value);
			}

			$updateQuery = 'UPDATE ' . $udpateTable 
				. ' SET ' . join(', ', $updateColumnParts);

			if (!empty($condition)) {
				$updateQuery .= ' WHERE ' . $condition;
			}

			if ($limit > 0) {
				$updateQuery .= ' LIMIT ' . $limit;
			}

			$this->_db->query($updateQuery);

			return $this->_db
				->countAffected();
		}

		private function _composeColumnValueAssignmentPart($column, $value) {
			return sprintf(
				'%s = %s', 
					$this->_quoteIdentifier($column), 
					$this->_prepareQuotedValueForQuery($value)
			);
		}

		private function _composeQueryCondition($where) {
			if (empty($where)) {
				return '';
			}

			if (!is_array($where)) {
				return (string)$where;
			}

			$parts = array();
			foreach ($where as $column => $info) {
				$parts[] = $this->_composeQueryConditionPart($column, $info);
			}

			return join(' AND ', 
				$parts);
		}

		private function _composeQueryConditionPart($column, $info) {
			if (is_array($info)) {
				$value = isset($info['value']) 
					? $info['value'] 
					: null;

				$operator = isset($info['operator']) 
					? $info['operator'] 
					: null;

				if (empty($operator)) {
					$operator = !is_array($value) 
						?  '='  
						: 'IN';
				}
			} else {
				$value = $info;
				$operator = '=';
			}

			return sprintf(
				'(%s %s %s)', 
					$this->_quoteIdentifier($column),
					$operator,
					$this->_prepareQuotedValueForQuery($value)
			);
		}

		public function delete($table, $where = null) {
			$deleteTable = $this->_getPrefixedTableName($table);
			$condition = $this->_composeQueryCondition($where);

			$deleteQuery = sprintf('DELETE FROM %s',  $deleteTable);
			if (!empty($where)) {
				$deleteQuery = sprintf('%s WHERE %s', 
					$deleteQuery, 
					$condition);
			}

			$this->_db->query($deleteQuery);

			return $this->_db
				->countAffected();
		}

		public function getOne($table, $where) {
			$rows = $this->select($table, $where, 0, 1);
			return is_array($rows) && !empty($rows) 
				? $rows[0] 
				: array();
		}

		public function select($table, $where, $offset = -1, $limit = 0) {
			$selectTable = $this->_getPrefixedTableName($table);
			$condition = $this->_composeQueryCondition($where);

			$selectQuery = 'SELECT * FROM ' . $selectTable;
			if (!empty($where)) {
				$selectQuery .= ' WHERE ' . $condition;
			}

			if ($limit > 0) {
				$selectQuery .= ' LIMIT ' . intval($limit);
			}

			if ($offset >= 0)  {
				$selectQuery .= 'OFFSET ' . intval($offset);
			}

			$result = $this->_db->query($selectQuery);
			return $result->rows;
		}
	}
}