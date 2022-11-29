<?php
namespace CielIntegration {
	class ManualManager {
		public function pdfExists() {
			return is_readable($this->getManualPdfFilePath());
		}

		public function getManualPdfFilePath() {
			return $this->_getManualPdfStorageDirectory() . 'manual.pdf';
		}

		private function _getManualPdfStorageDirectory() {
			return DIR_SYSTEM . 'storage/manual/pdf/';
		}

		public function getContents() {
			return $this->pdfExists() 
				? file_get_contents($this->getManualPdfFilePath()) 
				: null;
		}
	}
}