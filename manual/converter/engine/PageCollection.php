<?php
namespace MyClar\ManualBuilder {
	class PageCollection {
		private $_pages = array();

		public function addPage(Page $page): void {
			$this->_pages[] = $page;
		}

		public function getPages(): array {
			return $this->_pages;
		}
	}
}