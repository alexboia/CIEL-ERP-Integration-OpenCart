<?php
namespace CielIntegration {
	class ManualFileDownloader {
		/**
		 * @var \Response
		 */
		private $_response;

		public function __construct(\Response $response) {
			$this->_response = $response;
		}

		public function sendManualFile($fileName, &$contents) {
			$this->_sendHeaders($fileName);
			$this->_sendContents($contents);
		}

		private function _sendHeaders($fileName) {
			$this->_response->addheader('Pragma: public');
			$this->_response->addheader('Expires: 0');
			$this->_response->addheader('Content-Description: File Transfer');
			$this->_response->addheader('Content-Type: application/pdf');
			$this->_response->addheader('Content-Disposition: attachment; filename="' . $fileName . '"');
			$this->_response->addheader('Content-Transfer-Encoding: binary');
		}

		private function _sendContents(&$contents) {
			$this->_response->setOutput($contents);
		}
	}
}