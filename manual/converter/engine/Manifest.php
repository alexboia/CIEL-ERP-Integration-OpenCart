<?php
namespace MyClar\ManualBuilder {

    use Exception;
    use InvalidArgumentException;

	class Manifest {
		private $_directory = null;

		private $_contents = null;

		public function __construct($directory) {
			$this->_directory = $directory;
		}

		private function _readIfNeeded(): void {
			if ($this->_contents === null) {
				$filePath = $this->_getManifestFilePath();
				if (!$filePath) {
					throw new Exception('No manifest found in directory "' . $this->_directory . '"');
				}

				$jsonContents = file_get_contents($filePath);
				if (empty($jsonContents)) {
					throw new Exception('Empty manifest found in directory "' . $this->_directory . '"');
				}

				$contents = json_decode($jsonContents, true);
				if (empty($contents)) {
					throw new Exception('Invalid manifest found in directory "' . $this->_directory . '"');
				}

				$this->_contents = $contents;
			}
		}

		private function _getManifestFilePath(): string {
			return realpath($this->_directory . '/manifest.json');
		}

		public function getPageDescriptors(): array {
			$this->_readIfNeeded();
			return !empty($this->_contents['pages'])
				&& is_array($this->_contents['pages'])
					? $this->_contents['pages']
					: array();
		}

		public function locatePage(array $pageDescriptor) {
			if (empty($pageDescriptor) || empty($pageDescriptor['file'])) {
				throw new InvalidArgumentException('Page descriptor cannot be empty');
			}
			return realpath($this->_directory . '/' . $pageDescriptor['file']);
		}

		public function shouldOutputPdf() {
			return $this->_isOutputTypeEnabled('pdf');
		}

		private function _isOutputTypeEnabled($type) {
			$this->_readIfNeeded();
			return !empty($this->_contents['output'])
				&& !empty($this->_contents['output'][$type])
					? $this->_contents['output'][$type] === true
					: true;
		}

		public function shouldOutputOnline() {
			return $this->_isOutputTypeEnabled('online');
		}

		public function locateTemplateDirectory() {
			
		}
	}
}