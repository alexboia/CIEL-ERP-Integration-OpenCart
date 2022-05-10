<?php
namespace Ciel\Api\Request\Parameters {
	use Ciel\Api\Request\CielRequestParameters;

	class CancelSaleInvoicesParameters extends CielRequestParameters {
		private $_documentIds = array();

		public function setDocumentIds(array $documentIds) {
			$this->_documentIds = $documentIds;
			return $this;
		}

		public function addDocumentId($documentId) {
			$this->_documentIds[] = $documentId;
			return $this;
		}

		public function getParams() {
			return array(
				'documentIds' => $this->_documentIds
			);
		}
	}
}