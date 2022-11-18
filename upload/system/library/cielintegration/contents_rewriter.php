<?php
namespace CielIntegration {

    use Exception;
    use voku\helper\HtmlDomParser;
    use voku\helper\SimpleHtmlDomNodeInterface;

	class ContentsRewriter {
		use WithLogging;
		use WithContentCleaning;

		private $_rewrites = array();

		public function addRewriteRule($selector, $callback) {
			$this->_rewrites[$selector] = $callback;
			return $this;
		}

		public function rewrite($contents) {
			if (empty($this->_rewrites)) {
				return $contents;
			}
			
			try {
				$contents = $this->_prepare($contents);
				return $this->_rewrite($contents);
			} catch (Exception $exc) {
				$this->_logError($exc, 'Error rewriting contents');
				return $contents;
			}
		}

		private function _prepare($contents) {
			return $this->_cleanRepairContents($contents);
		}

		private function _rewrite($contents) {
			/** @var HtmlDomParser $dom */
			$dom = HtmlDomParser::str_get_html($contents);

			foreach ($this->_rewrites as $selector => $callback) {
				$nodes = $dom->findMulti($selector);
				if (!empty($nodes)) {
					foreach ($nodes as $node) {
						/** @var SimpleHtmlDomNodeInterface $node */
						call_user_func_array($callback, array(
							$dom, 
							$node
						));
					}
				}
			}

			$contents = $dom->html();
			return $contents;
		}
	}
}