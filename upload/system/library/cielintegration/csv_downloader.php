<?php
namespace CielIntegration {
	class CsvDownloader {
		public function sendCsv($name, $csvData) {
			$fileName = $this->_generateFileDownloadName($name);
			$this->_sendHeaders($fileName);
			$this->_sendData($csvData);
		}

		private function _sendHeaders($fileName) {
			header('Content-Description: File Transfer');
			header('Content-Type: text/csv; charset=utf-8');
			header('Content-Disposition: attachment; filename="' . $fileName . '"');
			header('Expires: 0');
			header('Cache-Control: must-revalidate');
			header('Pragma: public');
		}

		private function _sendData($csvData) {
			echo $csvData;
			die;
		}

		private function _generateFileDownloadName($baseName) {
			return sprintf('%s_%s.csv', 
				$baseName, 
				date('YmdHis'));
		}
	}
}