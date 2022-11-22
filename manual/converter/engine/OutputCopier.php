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
					copy($sourceOutputFile,  $copyDestinationFile);
				}
			}

			if ($this->_manifest->imagesRequiredForOutputType($outputType)) {
				$this->_copyImages($outputType);
			}
		}

		private function _determineSourceOutputFilePath(string $outputType) {
			return $this->_manifest->determineOutputFilePath($outputType);
		}

		private function _determineCopyDestinationOutputFilePath(string $outputType) {
			return $this->_manifest->determineCopyOutputFilePath($outputType);
		}

		private function _copyImages(string $outputType): void {
			$imageFiles = $this->_manifest->getSourceImageFiles();
			if (!empty($imageFiles)) {
				$this->_ensureCopyDestinationOutputImageDir($outputType);
				foreach ($imageFiles as $srcImgFile) {
					$imgFileName = basename($srcImgFile);
					$copyImgFilePath = $this->_determineCopyDestinationOutputImageFilePath($outputType, $imgFileName);
					var_dump($copyImgFilePath);
					if (file_exists($copyImgFilePath)) {
						unlink($copyImgFilePath);
					}

					copy($srcImgFile, $copyImgFilePath);
				}
			}
		}

		private function _ensureCopyDestinationOutputImageDir(string $outputType) {
			$dirPath = $this->_determineCopyDestinationOutputImageDirPath($outputType);
			var_dump($dirPath);
			if (!is_dir($dirPath)) {
				mkdir($dirPath);
			}
		}

		private function _determineCopyDestinationOutputImageDirPath(string $outputType) {
			return $this->_manifest->locateCopyOutputDirectory($outputType) 
				. DIRECTORY_SEPARATOR 
				. 'images';
		}

		private function _determineCopyDestinationOutputImageFilePath(string $outputType, string $imgFileName) {
			return $this->_determineCopyDestinationOutputImageDirPath($outputType) 
				. DIRECTORY_SEPARATOR 
				. $imgFileName;
		}
	}
}