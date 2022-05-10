<?php
namespace Ciel\Api\Request\Parameters {
	use Ciel\Api\Request\CielRequestParameters;

	class DeleteDocumentRequestParams extends CielRequestParameters {
		private $_documentId;

		public function setDocumentId($val) {
			$this->_documentId = $val;
			return $this;
		}

		public function getParams() {
			return array(
				'documentId' => $this->_documentId
			);
		}
	}
}