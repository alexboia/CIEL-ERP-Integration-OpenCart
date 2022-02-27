<?php
namespace CielIntegration {
	class StaticDataSource implements GenericDataSource {
		private $_data;

		public function __construct(array $data) {
			$this->_data = $data;
		}

		public function getValueForKey($id, $key) { 
			if (!empty($this->_data[$id]) 
				&& !empty($this->_data[$id][$key])) {
				return $this->_data[$id][$key];
			} else {
				return null;
			}
		}		
	}
}