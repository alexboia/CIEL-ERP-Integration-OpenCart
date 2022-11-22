<?php
namespace MyClar\ManualBuilder {
	class ManualPageCollection {
		private $_pages = array();

		public function addPage(ManualPage $page): void {
			$this->_pages[] = $page;
		}

		/**
		 * @return \MyClar\ManualBuilder\ManualPage[]
		 */
		public function getPages(): array {
			return $this->_pages;
		}

		public function getCount() {
			return count($this->_pages);
		}

		public function hasPages() {
			return $this->getCount() > 0;
		}
	}
}