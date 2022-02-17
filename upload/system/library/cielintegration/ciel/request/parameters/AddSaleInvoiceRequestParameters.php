<?php
namespace Ciel\Api\Request\Parameters {
	use Ciel\Api\Request\CielRequestParameters;

	class AddSaleInvoiceRequestParameters extends CielRequestParameters {
		private $_document = null;

		private $_documentStatusId = null;

		public function setDocument($val) {
			$this->_document = $val;
			return $this;
		}

		public function setDocumentStatusId($val) {
			$this->_documentStatusId = $val;
			return $this;
		}

		public function getParams() {
			$params = array();

			$params['document'] = $this->_document;
			if ($this->_documentStatusId !== null) {
				$params['documentStatusId'] = $this->_documentStatusId;
			}

			return $params;
		}
	}
}