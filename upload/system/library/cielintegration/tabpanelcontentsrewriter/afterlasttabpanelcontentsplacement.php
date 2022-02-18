<?php
namespace CielIntegration\TabPanelContentsRewriter {

    use voku\helper\SimpleHtmlDomInterface;

	class AfterLastTabPanelContentsPlacement implements TabContentsPlacement {

		private $_tabs = array();

		public function addTabContents($header, $content) { 
			$this->_tabs[] = array(
				'header' => $header,
				'content' => $content
			);
		}

		public function render(SimpleHtmlDomInterface $headerContainer, SimpleHtmlDomInterface $contentsContainer) { 
			foreach ($this->_tabs as $tab) {
				$headerContainer->innerhtml .= 
					$tab['header'];
				$contentsContainer->innerhtml .= 
					$tab['content'];
			}
		}

		public function clear() { 
			$this->_tabs = array();
		}
	}
}