<?php
namespace CielIntegration {

    use voku\helper\HtmlDomParser;
    use voku\helper\SimpleHtmlDomInterface;
    use voku\helper\SimpleHtmlDomNodeBlank;

	class ListingContentsRewriter {
		/**
		 * @var GenericDataSource
		 */
		private $_dataSource;

		private $_selectorPrefix;

		private $_headerRowSelector;

		private $_bodyRowSelector;

		private $_recordIdElementSelector;

		private $_columns = array();

		public function __construct($selectorPrefix, GenericDataSource $dataSource) {
			$this->_selectorPrefix = $selectorPrefix;
			$this->_dataSource = $dataSource;

			$this->_headerRowSelector = $this->_selectorPrefix . ' table thead tr';
			$this->_bodyRowSelector = $this->_selectorPrefix . ' table tbody tr';
			$this->_recordIdElementSelector = 'td input[name="selected[]"]';
		}

		public function addColumn($key, $headerTitle) {
			$this->_columns[$key] = array(
				'key' => $key,
				'headerTitle' => $headerTitle
			);
		}

		public function rewrite($listingContents) {
			if (empty($this->_columns)) {
				return $listingContents;
			}

			/** @var HtmlDomParser $dom */
			$dom = HtmlDomParser::str_get_html($listingContents);

			$headerRow = $dom->findOneOrFalse($this->_headerRowSelector);
			if (!empty($headerRow)) {
				$headerCells = $headerRow->findMulti('td');
				$headerCellCount = count($headerCells);
				$headerRowInnerHtml = '';
				
				for ($hIdx = 0; $hIdx < $headerCellCount; $hIdx ++) {
					if ($hIdx == $headerCellCount - 1 ) {
						foreach ($this->_columns as $key => $column) {
							$headerRowInnerHtml .= $this->_renderColumnHeader($key, $column);
						}
					}

					$headerCell = $headerCells[$hIdx];
					$headerRowInnerHtml .= $headerCell->outerhtml;
				}

				$headerRow->innerhtml = $headerRowInnerHtml;
			}

			$bodyRows = $dom->findMulti($this->_bodyRowSelector);
			foreach ($bodyRows as $row) {
				$recordId = $this->_getRecordId($row);
				if (empty($recordId)) {
					continue;
				}

				$rowCells = $row->findMulti('td');
				$rowCellCount = count($rowCells);
				$rowInnerHtml = '';

				for ($rIdx = 0; $rIdx < $rowCellCount; $rIdx ++) {
					if ($rIdx == $rowCellCount - 1) {
						foreach ($this->_columns as $key => $column) { 
							$value = $this->_dataSource->getValueForKey($recordId, 
								$key);
		
							$contents = $this->_renderColumnValue($key, 
								$column, 
								$recordId, 
								$value);
		
							$rowInnerHtml .= $contents;
						}
					}

					$rowCell = $rowCells[$rIdx];
					$rowInnerHtml .= $rowCell->outerhtml;
				}

				$row->innerhtml = $rowInnerHtml;
			}

			return $dom->save();
		}

		private function _renderColumnHeader($key, $column) {
			return '<td class="ciel-rewritten-column-header ciel-added-column-header ciel-' . $key . '-column-header">' 
					. $column['headerTitle'] 
				. '</td>';
		}

		private function _getRecordId(SimpleHtmlDomInterface $row) {
			$idElement = $row->findOneOrFalse($this->_recordIdElementSelector);
			if (!empty($idElement)) {
				return $idElement->getAttribute('value');
			} else {
				return null;
			}
		}

		private function _renderColumnValue($key, $column, $recordId, $value) {
			return '<td class="ciel-rewritten-column-value ciel-added-column-value ciel-' . $key . '-column-value" data-record-id="' . $recordId . '">' 
				. $value
			. '</td>';
		}
	}
}