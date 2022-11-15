<?php
namespace MyClar\ManualBuilder {
	class Page {
		/**
		 * @var string
		 */
		private $_title;

		/**
		 * @var int
		 */
		private $_order;

		/**
		 * @var string
		 */
		private $_content;

		public function __construct(string $title, int $order, string $content) {
			$this->_title = $title;
			$this->_order = $order;
			$this->_content = $content;
		}

		public function getTitle(): string {
			return $this->_title;
		}

		public function getOrder(): int {
			return $this->_order;
		}

		public function getContent(): string {
			return $this->_content;
		}
	}
}