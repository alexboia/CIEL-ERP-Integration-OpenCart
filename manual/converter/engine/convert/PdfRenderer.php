<?php
namespace MyClar\ManualBuilder\Convert {

    use MyClar\ManualBuilder\Manifest;
    use MyClar\ManualBuilder\OutputType;
    use MyClar\ManualBuilder\ManualPage;
    use MyClar\ManualBuilder\ManualPageCollection;
    use MyClar\ManualBuilder\ManualRenderer;
    use MyClar\ManualBuilder\Template;

	class PdfRenderer implements ManualRenderer {
		/**
		 * @var Manifest
		 */
		private $_manifest;

		/**
		 * @var Template
		 */
		private $_template;

		public function __construct(Manifest $manifest) {
			$this->_manifest = $manifest;
			$this->_template = new Template($manifest);
		}

		public function render(ManualPageCollection $pages): string {
			if (!$this->_manifest->shouldOutputPdf()) {
				return null;
			}

			$htmlDocContents = $this->_getHtmlDocumentContents($pages);
			return $this->_convertToPdf($htmlDocContents);
		}

		private function _getHtmlDocumentContents(ManualPageCollection $pages) : string {
			$contents = $this->_renderCover();

			foreach ($pages->getPages() as $page) {
				$contents .= $this->_renderPage($page);
			}

			return $this->_renderDocument($contents);
		}

		private function _renderCover(): string {
			return $this->_template->render(OutputType::Pdf, 
				'cover', 
				array());
		}

		private function _renderPage(ManualPage $page): string {
			return $this->_template->render(OutputType::Pdf, 
				$page->getName(), 
				$page->getRenderData());
		}

		private function _renderDocument(string $contents): string {
			$documentData = array(
				'title' => $this->_manifest->getDocumentTitle(),
				'contents' => $contents
			);

			return $this->_template->render(OutputType::Pdf, 
				'document', 
				$documentData);
		}

		private function _convertToPdf(string $htmlDocContents) {
			$dompdf = new \Dompdf\Dompdf();
			$dompdf->loadHtml($htmlDocContents);
			$dompdf->setPaper('A4', 'portrait');
			$dompdf->render();
			
			$output = $dompdf->output(array(
				'compress' => 0
			));

			return $output;
		}
	}
}