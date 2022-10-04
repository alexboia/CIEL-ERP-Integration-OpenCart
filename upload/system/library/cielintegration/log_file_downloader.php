<?php
namespace CielIntegration {

    use Response;

	class LogFileDownloader {
		/**
		 * @var Response
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
			$this->response->addheader('Pragma: public');
			$this->response->addheader('Expires: 0');
			$this->response->addheader('Content-Description: File Transfer');
			$this->response->addheader('Content-Type: application/octet-stream');
			$this->response->addheader('Content-Disposition: attachment; filename="' . $this->_buildLogFileDownloadName($fileName) . '"');
			$this->response->addheader('Content-Transfer-Encoding: binary');
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