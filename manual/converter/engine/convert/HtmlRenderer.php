<?php
namespace MyClar\ManualBuilder\Convert {

    use MyClar\ManualBuilder\Manifest;
    use MyClar\ManualBuilder\PageCollection;
    use MyClar\ManualBuilder\Template;

	class HtmlRenderer {
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
			if (!$this->_manifest->shouldOutputOnline()) {
				return null;
			}

			$htmlDocContents = $this->_getHtmlDocumentContents($pages);
			return $htmlDocContents;
		}

		private function _getHtmlDocumentContents(PageCollection $pages) : string {
			$contents = $this->_renderCover();

			foreach ($pages->getPages() as $page) {
				$contents .= $this->_template->render('online', $page->getName(), array(
					'mp_page_name' => $page->getName(),
					'mp_page_contents' => $page->getContent()
				));
			}

			return $this->_template->render('online', 'document', array(
				'mp_title' => 'NextUp Integration for OpenCart 2 Manual',
				'mp_contents' => $contents
			));
		}

		private function _renderCover() {
			return $this->_template->render('online', 
				'cover', 
				array());
		}
	}
}