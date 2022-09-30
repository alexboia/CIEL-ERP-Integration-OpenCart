<?php
namespace Ciel\Api\Data {
	class ArticleLookupInfo {
		private $_id;
		
		private $_code;

		private $_children = array();

		private $_type;

		public function __construct($id, $code, $type, array $children) {
			$this->_id = $id;
			$this->_code = $code;
			$this->_type = $type;
			$this->_children = $children;
		}

		public function getId() {
			return $this->_id;
		}

		public function getCode() {
			return $this->_code;
		}

		public function getType() {
			return $this->_type;
		}

		public function getChildren() {
			return $this->_children;
		}

		public function isSimple() {
			return $this->getType() == LocalProductType::Simple;
		}

		public function isPack() {
			return $this->getType() == LocalProductType::Pack;
		}

		public function isVariable() {
			return $this->getType() == LocalProductType::Variable;
		}

		public function isVariation() {
			return $this->getType() == LocalProductType::Variation;
		}
	}
}