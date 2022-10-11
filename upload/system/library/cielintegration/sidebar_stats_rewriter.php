<?php
namespace CielIntegration {
	use voku\helper\HtmlDomParser;

	class SidebarStatsRewriter {
		private $_listContainerSelector;

		private $_statsItems = array();

		public function __construct($listContainerSelector) {
			$this->_listContainerSelector = $listContainerSelector;
		}

		public function addStatsItem($id, $label, $value) {
			$this->_statsItems[] = array(
				'id' => $id,
				'label' => $label,
				'value' => $value
			);
		}

		public function rewrite(&$contents) {
			if (empty($this->_statsItems)) {
				return $contents;
			}

			/** @var HtmlDomParser $dom */
			$dom = HtmlDomParser::str_get_html($contents);
			$listContainer = $dom->findOneOrFalse($this->_listContainerSelector);

			if (!empty($listContainer)) {
				$listContainer->innerhtml .= $this->_renderStatsItems();
			}

			return $dom->save();
		}

		private function _renderStatsItems() {
			$html = '';
			foreach ($this->_statsItems as $statsItem) {
				$html .= $this->_renderStatsItem($statsItem);
			}
			return $html;
		}

		private function _renderStatsItem(array $statsItem) {
			$htmlParts = array(
				'<li id="' . $statsItem['id'] . '">',
					'<div>' . $statsItem['label'] . ' <span class="pull-right">' . $statsItem['value'] . '%</span></div>',
					'<div class="progress">',
						'<div class="progress-bar progress-bar-success" role="progressbar" aria-valuenow="' . $statsItem['value'] . '" aria-valuemin="0" aria-valuemax="100" style="width: ' . $statsItem['value'] . '%"> <span class="sr-only">' . $statsItem['value'] . '%</span></div>',
					'</div>',
				'</li>'
			);

			return implode('', $htmlParts);
		}
	}
}