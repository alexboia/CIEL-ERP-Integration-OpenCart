<?php
namespace CielIntegration {

    use CielIntegration\ListingContentsRewriter\BeforeLastColumnContentsPlacement;
    use Exception;
    use voku\helper\HtmlDomParser;
    use voku\helper\SimpleHtmlDomInterface;

	class ListingContentsRewriter {
		use WithLogging;
		use WithContentCleaning;

		/**
		 * @var GenericDataSource
		 */
		private $_dataSource;

		private $_selectorPrefix;

		private $_headerRowSelector;

		private $_bodyRowSelector;

		private $_recordIdElementSelector;

		private $_columns = array();

		public function __construct($selectorPrefix, 
			$recordIdElementSelector, 
			GenericDataSource $dataSource) {

			$this->_selectorPrefix = $selectorPrefix;
			$this->_dataSource = $dataSource;

			$this->_headerRowSelector = $this->_selectorPrefix . ' table thead tr';
			$this->_bodyRowSelector = $this->_selectorPrefix . ' table tbody tr';
			$this->_recordIdElementSelector = $recordIdElementSelector;
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

			try {
				$listingContents = $this->_prepare($listingContents);
				return $this->_rewrite($listingContents);
			} catch (Exception $exc) {
				$this->_logError($exc, 'Error rewriting listing contents.');
				return $listingContents;
			}
		}

		private function _prepare($listingContents) {
			return $this->_cleanRepairContents($listingContents);
		}

		private function _rewrite($listingContents) {
			/** @var HtmlDomParser $dom */
			$dom = HtmlDomParser::str_get_html($listingContents);
			$columnPlacement = new BeforeLastColumnContentsPlacement();

			$headerRow = $dom->findOneOrFalse($this->_headerRowSelector);
			if (!empty($headerRow)) {
				foreach ($this->_columns as $key => $column) {
					$columnPlacement->addColumnContents(
						$this->_renderColumnHeader($key, 
							$column)
					);
				}

				$columnPlacement
					->render($headerRow);
			}

			$bodyRows = $dom->findMulti($this->_bodyRowSelector);
			foreach ($bodyRows as $row) {
				$columnPlacement->clear();
				$recordId = $this->_getRecordId($row);

				if (empty($recordId)) {
					continue;
				}

				foreach ($this->_columns as $key => $column) { 
					$cellValue = $this->_getColumnCellValue($recordId, 
						$key);

					$columnPlacement->addColumnContents(
						$this->_renderColumnValue($key, 
							$column, 
							$recordId, 
							$cellValue)
					);
				}

				$columnPlacement
					->render($row);
			}

			$columnPlacement->clear();
			return $dom->save();
		}

		private function _renderColumnHeader($columnKey, $column) {
			return '<td class="ciel-rewritten-column-header ciel-added-column-header ciel-' . $columnKey . '-column-header">' 
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

		private function _getColumnCellValue($recordId, $columnKey) {
			return $this->_dataSource
				->getValueForKey($recordId, $columnKey);
		}

		private function _renderColumnValue($columnKey, $column, $recordId, $value) {
			return '<td class="ciel-rewritten-column-value ciel-added-column-value ciel-' . $columnKey . '-column-value" data-record-id="' . $recordId . '">' 
				. $value
			. '</td>';
		}
	}
}