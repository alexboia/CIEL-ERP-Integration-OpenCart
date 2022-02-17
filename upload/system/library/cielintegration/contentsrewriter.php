<?php
namespace CielIntegration {
	use voku\helper\HtmlDomParser;
    use voku\helper\SimpleHtmlDomNodeInterface;

	class ContentsRewriter {
		private $_rewrites = array();

		public function addRewriteRule($selector, $callback) {
			$this->_rewrites[$selector] = $callback;
		}

		public function rewrite($contents) {
			if (empty($this->_rewrites)) {
				return $contents;
			}

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