<?php
namespace MyClar\ManualBuilder\Convert {

    use MyClar\ManualBuilder\Manifest;
    use MyClar\ManualBuilder\PageCollection;
    use MyClar\ManualBuilder\Template;

	class PdfRenderer {
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

		public function convert(PageCollection $pages): string {
			if (!$this->_manifest->shouldOutputPdf()) {
				return null;
			}

			$htmlDocContents = $this->_getHtmlDocumentContents($pages);
			return $this->_convertToPdf($htmlDocContents);
		}

		private function _getHtmlDocumentContents(PageCollection $pages) : string {
			$contents = $this->_renderCover();

			foreach ($pages->getPages() as $page) {
				$contents .= $this->_template->render('pdf', $page->getName(), array(
					'mp_page_name' => $page->getName(),
					'mp_page_contents' => $page->getContent()
				));
			}

			return $this->_template->render('pdf', 'document', array(
				'mp_title' => 'NextUp Integration for OpenCart 2 Manual',
				'mp_contents' => $contents
			));
		}

		private function _renderCover() {
			return $this->_template->render('pdf', 
				'cover', 
				array());
		}

		private function _convertToPdf($htmlDocContents) {
			$dompdf = new \Dompdf\Dompdf();
			$dompdf->loadHtml($htmlDocContents);
			$dompdf->setPaper('A4', 'portrait');
			$dompdf->render();
			
			$output = $dompdf->output(array('compress' => 0));
			return $output;
		}
	}
}