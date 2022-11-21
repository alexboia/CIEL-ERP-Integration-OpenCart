<?php
namespace MyClar\ManualBuilder {

    use Exception;
    use InvalidArgumentException;

	class Manifest {
		/**
		 * @var string
		 */
		private $_directory = null;

		/**
		 * @var array|null
		 */
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
			return realpath($this->_directory 
				. DIRECTORY_SEPARATOR 
				. 'manifest.json');
		}

		public function getPageDescriptors(): array {
			$this->_readIfNeeded();
			return !empty($this->_contents['pages'])
				&& is_array($this->_contents['pages'])
					? $this->_contents['pages']
					: array();
		}

		public function locatePage(array $pageDescriptor): string {
			if (empty($pageDescriptor) || empty($pageDescriptor['file'])) {
				throw new InvalidArgumentException('Page descriptor cannot be empty');
			}

			$path = realpath($this->_directory 
				. DIRECTORY_SEPARATOR 
				. $pageDescriptor['file']);

			return $path 
				? $path 
				: null;
		}

		public function shouldOutputPdf(): bool {
			return $this->_isOutputTypeEnabled('pdf');
		}

		private function _isOutputTypeEnabled(string $type): bool {
			$this->_readIfNeeded();
			return !empty($this->_contents['output'])
				&& !empty($this->_contents['output'][$type])
					? $this->_contents['output'][$type] === true
					: true;
		}

		public function shouldOutputOnline(): bool {
			return $this->_isOutputTypeEnabled('online');
		}

		public function locateTemplateDirectory(): string {
			if (func_num_args() == 1) {
				$type = func_get_arg(0);
			} else {
				$type = null;
			}

			$basePath = realpath($this->_directory 
				. DIRECTORY_SEPARATOR 
				. 'template');

			if (!empty($type) && !empty($basePath)) {
				$path = realpath($basePath 
					. DIRECTORY_SEPARATOR 
					. $type);
			} else {
				$path = $basePath;
			}

			return $path 
				? $path 
				: null;
		}
	}
}