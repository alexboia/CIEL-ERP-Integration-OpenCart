<?php
namespace Ciel\Api\Request\Parameters {
	use Ciel\Api\Request\CielRequestParameters;

	class AddSaleOrderRequestParameters extends CielRequestParameters {
		private $_document = null;

		private $_documentStatusId = null;

		private $_documentStateDetailId = null;

		public function setDocument($val) {
			$this->_document = $val;
			return $this;
		}

		public function setDocumentStatusId($val) {
			$this->_documentStatusId = $val;
			return $this;
		}

		public function setDocumentStateDetailId($val) {
			$this->_documentStateDetailId = $val;
			return $this;
		}

		public function getParams() {
			$params = array();

			$params['document'] = $this->_document;
			if ($this->_documentStatusId !== null) {
				$params['documentStatusId'] = $this->_documentStatusId;
			}
			if ($this->_documentStateDetailId !== null) {
				$params['documentStateDetailId'] = $this->_documentStateDetailId;
			}

			return $params;
		}
	}
}