<?php
namespace MyClar\ManualBuilder {

    use Exception;
    use MyClar\ManualBuilder\Convert\HtmlRenderer;
    use MyClar\ManualBuilder\Convert\PdfRenderer;

	class ManualProcessor {
		/**
		 * @var Manifest
		 */
		private $_manifest;

		public function __construct(string $inputDirectory, string $outputDirectory) {
			$this->_manifest = new Manifest($inputDirectory, $outputDirectory);
		}

		public function process() {
			$pages = $this->_readPages();
			$this->_processOutputType(OutputType::Html, $pages);
			$this->_processOutputType(OutputType::Pdf, $pages);
			$this->_copyOutput();
		}

		private function _readPages(): ManualPageCollection {
			$contentProvider = new ContentProvider($this->_manifest);
			return $contentProvider->readPages();
		}

		private function _processOutputType(string $outputType, ManualPageCollection $pages): void {
			if ($this->_manifest->shouldOutputType($outputType)) {
				$contents = $this->_renderOutputType($outputType, $pages);
				$this->_storeOutput($outputType, $contents);
			} else {
				$this->_removeExistingOutput($outputType);
			}
		}

		private function _renderOutputType(string $outputType, ManualPageCollection $pages) {
			return $this->_getRenderer($outputType)
				->render($pages);
		}

		private function _getRenderer(string $outputType): ManualRenderer {
			if ($outputType == OutputType::Html) {
				return new HtmlRenderer($this->_manifest);
			} else if ($outputType == OutputType::Pdf) {
				return new PdfRenderer($this->_manifest);
			} else {
				throw new Exception('Invalid output type: <' . $outputType . '>');
			}
		}

		private function _storeOutput(string $outputType, string $contents): void {
			$outputFilePath = $this->_manifest->determineOutputFilePath($outputType);
			if (file_exists($outputFilePath)) {
				@unlink($outputFilePath);
			}

			if (!empty($contents)) {
				file_put_contents($outputFilePath, 
					$contents);
			}
		}

		private function _copyOutput(): void {
			$copier = new OutputCopier($this->_manifest);
			$copier->copyOutput();
		}

		private function _removeExistingOutput($outputType) {
			$outputFilePath = $this->_manifest->determineOutputFilePath($outputType);
			if (file_exists($outputFilePath)) {
				@unlink($outputFilePath);
			}
		}
	}
}