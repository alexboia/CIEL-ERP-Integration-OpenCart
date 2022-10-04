<?php
namespace CielIntegration {
	class LogFileDownloader {
		/**
		 * @var \Response
		 */
		private $_response;

		public function __construct(\Response $response) {
			$this->_response = $response;
		}

		public function sendLogFile($fileName, &$contents) {
			$this->_sendHeaders($fileName);
			$this->_sendContents($contents);
		}

		private function _sendHeaders($fileName) {
			$this->_response->addheader('Pragma: public');
			$this->_response->addheader('Expires: 0');
			$this->_response->addheader('Content-Description: File Transfer');
			$this->_response->addheader('Content-Type: application/octet-stream');
			$this->_response->addheader('Content-Disposition: attachment; filename="' . $this->_buildLogFileDownloadName($fileName) . '"');
			$this->_response->addheader('Content-Transfer-Encoding: binary');
		}

		private function _buildLogFileDownloadName($logFileName) {
			$logFileNameWithoutExtension = str_ireplace('.log', '', 
				$logFileName);
	
			return $logFileNameWithoutExtension . '_' 
				. date('Y-m-d_H-i-s', time()) 
				. '.log';
		}

		private function _sendContents(&$contents) {
			$this->_response->setOutput($contents);
		}
	}
}