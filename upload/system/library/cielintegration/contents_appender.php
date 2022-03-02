<?php
namespace CielIntegration {
    use voku\helper\HtmlDomParser;

	class ContentsAppender {
		private $_parentSelector;

		private $_contentElements = array();

		public function __construct($parentSelector) {
			$this->_parentSelector = $parentSelector;	
		}

		public function addContent($contents) {
			$this->_contentElements[] = $contents;
		}

		public function rewrite($htmlContents) {
			if (empty($this->_contentElements)) {
				return $htmlContents;
			}

			/** @var HtmlDomParser $dom */
			$dom = HtmlDomParser::str_get_html($htmlContents);
			$parent = $dom->findOneOrFalse($this->_parentSelector);
			if (!empty($parent)) {
				foreach ($this->_contentElements as $contentEl) {
					$parent->innerhtml .= $contentEl;
				}
			}

			return $dom->save();
		}
	}
}