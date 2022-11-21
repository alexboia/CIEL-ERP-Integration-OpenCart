<?php
namespace MyClar\ManualBuilder {
	class PageCollection {
		private $_pages = array();

		public function addPage(Page $page): void {
			$this->_pages[] = $page;
		}

		/**
		 * @return \MyClar\ManualBuilder\Page[]
		 */
		public function getPages(): array {
			return $this->_pages;
		}

		public function getCount() {
			return count($this->_pages);
		}
	}
}