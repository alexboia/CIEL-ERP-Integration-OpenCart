<?php
namespace MyClar\ManualBuilder {
	class OutputCopier {
		private $_manifest;

		public function __construct(Manifest $manifest) {
			$this->_manifest = $manifest;	
		}

		public function copyOutput(): void {
			$this->_copyOutputType(OutputType::Html);
			$this->_copyOutputType(OutputType::Pdf);
		}

		private function _copyOutputType(string $outputType): void {
			$sourceOutputFile = $this->_determineSourceOutputFilePath($outputType);
			if (!empty($sourceOutputFile)) {
				$copyDestinationFile = $this->_determineCopyDestinationOutputFilePath($outputType);
				if (!empty($copyDestinationFile)) {
					@copy($sourceOutputFile, 
						$copyDestinationFile);
				}
			}
		}

		private function _determineSourceOutputFilePath(string $outputType) {
			return $this->_manifest->determineOutputFilePath($outputType);
		}

		private function _determineCopyDestinationOutputFilePath(string $outputType) {
			return $this->_manifest->determineCopyOutputFilePath($outputType);
		}
	}
}