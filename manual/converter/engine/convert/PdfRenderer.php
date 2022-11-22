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
			$contents = '';
			$cover = $this->_renderCover();
			$pageHeader = $this->_renderPageHeader();

			foreach ($pages->getPages() as $page) {
				$contents .= $this->_renderPage($page);
			}

			return $this->_renderDocument($cover, 
				$pageHeader, 
				$contents);
		}

		private function _renderCover(): string {
			return $this->_template->render(OutputType::Pdf, 
				'cover', 
				$this->_manifest->getViewVariablesToSet());
		}

		private function _renderPageHeader(): string {
			return $this->_template->render(OutputType::Pdf, 
				'header', 
				$this->_manifest->getViewVariablesToSet());
		}

		private function _renderPage(ManualPage $page): string {
			$pageData = array_merge(
				$this->_manifest->getViewVariablesToSet(), 
				$page->getRenderData()
			);

			return $this->_template->render(OutputType::Pdf, 
				$page->getName(), 
				$pageData);
		}

		private function _renderDocument(string $cover, string $pageHeader, string $contents): string {
			$documentData = array_merge(
				$this->_manifest->getViewVariablesToSet(), 
				array(
					'cover' => $cover,
					'page_header' => $pageHeader,
					'title' => $this->_manifest->getDocumentTitle(),
					'contents' => $contents
				)
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