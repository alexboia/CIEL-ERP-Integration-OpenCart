<?php
namespace CielIntegration {

    use CielIntegration\TabPanelContentsRewriter\AfterLastTabPanelContentsPlacement;
    use Exception;
    use voku\helper\HtmlDomParser;

	class TabPanelContentsRewriter {
		use WithLogging;
		use WithContentCleaning;

		private $_tabs = array();

		private $_selectorPrefix;

		private $_tabHeaderSelector;

		private $_tabContentsSelector;

		public function __construct($selectorPrefix, 
			$tabHeaderSelector = 'ul.nav-tabs', 
			$tabContentsSelector = 'div.tab-content') {
			$this->_selectorPrefix = $selectorPrefix;
			$this->_tabHeaderSelector = $selectorPrefix . ' ' . $tabHeaderSelector;
			$this->_tabContentsSelector = $selectorPrefix . ' ' . $tabContentsSelector;
		}

		public function addTab($key, $header, $content) {
			$this->_tabs[$key] = array(
				'key' => $key,
				'header' => $header,
				'content' => $content
			);
			return $this;
		}

		public function rewrite($tabPanelContents) {
			if (empty($this->_tabs)) {
				return $tabPanelContents;
			}

			try {
				$tabPanelContents = $this->_prepare($tabPanelContents);
				return $this->_rewrite($tabPanelContents);
			} catch (Exception $exc) {
				$this->_logError($exc, 'Error rewriting tab panel contents.');
				return $tabPanelContents;
			}
		}

		private function _prepare($tabPanelContents) {
			return $this->_cleanRepairContents($tabPanelContents);
		}

		private function _rewrite($tabPanelContents) {
			/** @var HtmlDomParser $dom */
			$dom = HtmlDomParser::str_get_html($tabPanelContents);
			$tabPanelPlacement = new AfterLastTabPanelContentsPlacement();

			$tabHeaderContainer = $dom->findOneOrFalse($this->_tabHeaderSelector);
			$tabContentContainer = $dom->findOneOrFalse($this->_tabContentsSelector);

			if (!empty($tabHeaderContainer) && !empty($tabContentContainer)) {
				foreach ($this->_tabs as $key => $tab) {
					$header = $this->_renderTabHeader($key, 
						$tab);
					$contents = $this->_renderTabContents($key, 
						$tab);

					$tabPanelPlacement->addTabContents($header, 
						$contents);
				}

				$tabPanelPlacement->render($tabHeaderContainer, 
					$tabContentContainer);
			}

			return $dom->save();
		}

		private function _renderTabHeader($tabKey, $tab) {
			return '<li id="ciel-tab-' . $tabKey . '-header" class="ciel-tab-header ciel-tab-' . $tabKey . '-header"><a href="#' . $tabKey . '" data-toggle="tab">' 
				. $tab['header'] 
			. '</a></li>';
		}

		private function _renderTabContents($tabKey, $tab) {
			return '<div class="tab-pane ciel-tab-content ciel-tab-' . $tabKey . '-content" id="' . $tabKey . '">' 
				. $tab['content'] 
			. '</div>';
		}
	}
}