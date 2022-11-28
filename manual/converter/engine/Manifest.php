<?php
namespace MyClar\ManualBuilder {

    use Exception;
    use InvalidArgumentException;

	class Manifest {
		/**
		 * @var string
		 */
		private $_inputDirectory = null;

		/**
		 * @var string
		 */
		private $_outputDirectory;

		/**
		 * @var array|null
		 */
		private $_contents = null;

		/**
		 * @var array|null
		 */
		private $_sourceImageFiles = null;

		public function __construct(string $inputDirectory, string $outputDirectory) {
			$this->_inputDirectory = $this->_ensureNoTrailingDirSep($inputDirectory);
			$this->_outputDirectory = $this->_ensureNoTrailingDirSep($outputDirectory);
		}

		private function _ensureNoTrailingDirSep(string $dirPath): string {
			return rtrim(rtrim($dirPath, '/'), DIRECTORY_SEPARATOR);
		}

		private function _readIfNeeded(): void {
			if ($this->_contents === null) {
				$filePath = $this->_getManifestFilePath();
				if (!$filePath) {
					throw new Exception('No manifest found in directory "' . $this->_inputDirectory . '"');
				}

				$jsonContents = file_get_contents($filePath);
				if (empty($jsonContents)) {
					throw new Exception('Empty manifest found in directory "' . $this->_inputDirectory . '"');
				}

				$contents = json_decode($jsonContents, true);
				if (empty($contents)) {
					throw new Exception('Invalid manifest found in directory "' . $this->_inputDirectory . '"');
				}

				$this->_contents = $contents;
			}
		}

		private function _getManifestFilePath(): string {
			return realpath($this->_inputDirectory 
				. DIRECTORY_SEPARATOR 
				. 'manual.json');
		}

		public function getPageDescriptors(): array {
			$this->_readIfNeeded();
			return !empty($this->_contents['input']) && !empty($this->_contents['input']['pages'])
				&& is_array($this->_contents['input']['pages'])
					? $this->_contents['input']['pages']
					: array();
		}

		public function locatePage(array $pageDescriptor) {
			if (empty($pageDescriptor) || empty($pageDescriptor['file'])) {
				throw new InvalidArgumentException('Page descriptor cannot be empty');
			}

			$path = realpath($this->_inputDirectory 
				. DIRECTORY_SEPARATOR 
				. 'text'
				. DIRECTORY_SEPARATOR
				. $pageDescriptor['file']);

			return $path 
				? $path 
				: null;
		}

		public function shouldOutputPdf(): bool {
			return $this->_isOutputTypeEnabled(OutputType::Pdf);
		}

		private function _isOutputTypeEnabled(string $outputType): bool {
			$this->_readIfNeeded();
			return !empty($this->_contents['output'])
				&& isset($this->_contents['output'][$outputType])
					? $this->_contents['output'][$outputType] === true
					: true;
		}

		public function shouldOutputType(string $outputType) {
			return $this->_isOutputTypeEnabled($outputType);
		}

		public function shouldOutputHtml(): bool {
			return $this->_isOutputTypeEnabled(OutputType::Html);
		}

		public function locateTemplateDirectory() {
			if (func_num_args() == 1) {
				$type = func_get_arg(0);
			} else {
				$type = null;
			}

			$basePath = realpath($this->_inputDirectory 
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

		public function getDocumentTitle() {
			$this->_readIfNeeded();
			return !empty($this->_contents['document'])
				&& !empty($this->_contents['document']['title'])
					? !empty($this->_contents['document']['title'])
					: null;
		}

		public function locateOutputDirectory($outputType): string {
			return realpath($this->_outputDirectory 
				. DIRECTORY_SEPARATOR 
				. $outputType);
		}

		public function determineOutputFilePath(string $outputType) {
			$outputDir = $this->locateOutputDirectory($outputType);

			if (!empty($outputDir)) {
				$outputFilePath = $outputDir 
					. DIRECTORY_SEPARATOR 
					. 'manual.' 
					. $outputType;
			} else {
				$outputFilePath = null;
			}

			return $outputFilePath;
		}

		public function locateCopyOutputDirectory($outputType) {
			$this->_readIfNeeded();
			if (!empty($this->_contents['output']['copy']) 
				&& !empty($this->_contents['output']['copy'][$outputType])) {

				$basePath = !empty($this->_contents['output']['copy']) 
						&& !empty($this->_contents['output']['copy']['base'])
					? $this->_contents['output']['copy']['base']
					: '';

				$copyOutputPath = $this->_contents['output']['copy'][$outputType];

				if (!empty($basePath)) {
					$copyOutputPath = $this->_ensureNoTrailingDirSep($basePath) 
						. DIRECTORY_SEPARATOR 
						. $copyOutputPath;
				}

				if (!empty($copyOutputPath) 
					&& (stripos($copyOutputPath, '../') === 0 
						|| stripos($copyOutputPath, './') === 0)) {
					$copyOutputPath = realpath($this->_inputDirectory 
						. DIRECTORY_SEPARATOR 
						. $copyOutputPath);
				}

				return $copyOutputPath;
			} else {
				return null;
			}
		}

		public function determineCopyOutputFilePath(string $outputType) {
			$copyDestinationOutput = $this->locateCopyOutputDirectory($outputType);

			if (!empty($copyDestinationOutput)) {
				$copyDestinationFile = $copyDestinationOutput 
					. DIRECTORY_SEPARATOR 
					. 'manual.' 
					. $outputType;
			} else {
				$copyDestinationFile = null;
			}

			return $copyDestinationFile;
		}

		public function getViewVariablesToSet() {
			if (!empty($this->_contents['set']) && is_array($this->_contents['set'])) {
				return $this->_contents['set'];
			} else {
				return array();
			}
		}

		public function getSourceImageFiles() {
			if ($this->_sourceImageFiles === null) {
				$this->_sourceImageFiles = array();

				$imgDir = $this->_locateImagesDirectory();
				$imgDirHandle = dir($imgDir);

				if ($imgDirHandle) {
					while (($entry = $imgDirHandle->read()) !== false) {
						if ($entry === '.' || $entry === '..') {
							continue;
						}

						$imgFile = $imgDir . DIRECTORY_SEPARATOR . $entry;
						if (is_file($imgFile)) {
							$this->_sourceImageFiles[] = $imgFile;
						}
					}
				}
			}

			return $this->_sourceImageFiles;
		}

		private function _locateImagesDirectory() {
			return realpath($this->_inputDirectory 
				. DIRECTORY_SEPARATOR 
				. 'images');
		}

		public function imagesRequiredForOutputType(string $outputType): bool {
			return $outputType == OutputType::Html;
		}

		public function getInputImagesDirectory() {
			return $this->_inputDirectory . DIRECTORY_SEPARATOR . 'images';
		}

		public function getInputImagesDirectoryUrl() {
			$directory = $this->getInputImagesDirectory();
			return 'file:///' . str_replace(DIRECTORY_SEPARATOR, '/', $directory);
		}

		public function getInputDirectory() {
			return $this->_inputDirectory;
		}

		public function getOutputDirectory() {
			return $this->_outputDirectory;
		}
	}
}