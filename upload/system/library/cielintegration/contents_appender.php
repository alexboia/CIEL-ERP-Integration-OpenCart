<?php
namespace CielIntegration {

    use Exception;
    use voku\helper\HtmlDomParser;

	class ContentsAppender {
		use WithLogging;
		use WithContentCleaning;

		private $_parentSelector;

		private $_contentElements = array();

		public function __construct($parentSelector) {
			$this->_parentSelector = $parentSelector;	
		}

		public function setContents($contents) {
			return $this
				->resetContents()
				->addContent($contents);
		}

		public function addContent($contents) {
			$this->_contentElements[] = $contents;
			return $this;
		}

		public function resetContents() {
			$this->_contentElements = array();
			return $this;
		}

		public function rewrite($htmlContents) {
			if (empty($this->_contentElements)) {
				return $htmlContents;
			}

			try {
				$htmlContents = $this->_prepare($htmlContents);
				return $this->_rewrite($htmlContents);
			} catch (Exception $exc) {
				$this->_logError($exc, 'Error appending content.');
				return $htmlContents;
			}
		}

		private function _prepare($htmlContents) {
			return $this->_cleanRepairContents($htmlContents);
		}

		private function _rewrite($htmlContents) {
			/** @var HtmlDomParser $dom */
			$dom = HtmlDomParser::str_get_html($htmlContents);
			
			$parent = $dom->findOneOrFalse($this->_parentSelector);
			if (!empty($parent)) {
				foreach ($this->_contentElements as $contentEl) {
					$parent->innerhtmlKeep .= $contentEl;
				}
			}

			return $dom->save();
		}
	}
}