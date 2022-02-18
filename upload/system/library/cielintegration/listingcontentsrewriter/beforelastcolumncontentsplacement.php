<?php
namespace CielIntegration\ListingContentsRewriter {
    use voku\helper\SimpleHtmlDomInterface;

	class BeforeLastColumnContentsPlacement implements ColumnContentsPlacement {
		private $_columnContents = array();

		public function addColumnContents($columnContents) { 
			if (!empty($columnContents)) {
				$this->_columnContents[] = $columnContents;
			}
		}

		public function render(SimpleHtmlDomInterface $row) { 
			$cells = $row->findMulti('td');
			$cellCount = count($cells);
			$rowInnerHtml = '';
			
			for ($cIdx = 0; $cIdx < $cellCount; $cIdx ++) {
				if ($cIdx == $cellCount - 1 ) {
					foreach ($this->_columnContents as $columnContents) {
						$rowInnerHtml .= $columnContents;
					}
				}

				$headerCell = $cells[$cIdx];
				$rowInnerHtml .= $headerCell->outerhtml;
			}

			$row->innerhtml = $rowInnerHtml;
			return $row;
		}

		public function clear() { 
			$this->_columnContents = array();
		}
	}
}