<?php
namespace CielIntegration {
	class LogFileManager {
		const READ_THRESHOLD_SIZE_BYTES = 5242880;

		private $_filePath;

		private $_fileSizeBytes;

		private $_exists;

		private $_fileName;

		public function __construct($logFileName) {
			$this->_fileName = $logFileName;
			$this->_filePath = DIR_LOGS . $logFileName;
			$this->_exists = is_readable($this->_filePath);

			if ($this->_exists) {
				$this->_fileSizeBytes = filesize($this->_filePath);
			} else {
				$this->_fileSizeBytes = 0;
			}
		}

		public function getSizeInBytes() {
			return $this->_fileSizeBytes;
		}

		public function getSizeDescription() {
			$size = $this->_fileSizeBytes;
			$suffix = array(
				'B',
				'KB',
				'MB',
				'GB',
				'TB',
				'PB',
				'EB',
				'ZB',
				'YB'
			);

			$i = 0;
			while (($size / 1024) > 1) {
				$size = $size / 1024;
				$i++;
			}

			return round(substr($size, 0, strpos($size, '.') + 4), 2) 
				. $suffix[$i];
		}

		public function getReadableContents() {
			return $this->canContentsBeRead() 
				? $this->_dumpContents()
				: null;
		}

		public function getEntireContents() {
			return $this->_dumpContents();
		}

		private function _dumpContents() {
			return file_get_contents($this->_filePath, 
				FILE_USE_INCLUDE_PATH, 
				null);
		}

		public function canContentsBeRead() {
			return $this->exists() 
				&& $this->isWitinReadableThreshold();
		}

		public function isWitinReadableThreshold() {
			return $this->_fileSizeBytes <= self::READ_THRESHOLD_SIZE_BYTES;
		}

		public function clear() {
			if ($this->_exists) {
				@unlink($this->_filePath);
			}
		}

		public function exists() {
			return $this->_exists && $this->_fileSizeBytes > 0;
		}

		public function getFileName() {
			return $this->_fileName;
		}
	}
}