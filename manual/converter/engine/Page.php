<?php
namespace MyClar\ManualBuilder {
	class Page {
		private $_name;

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

		public function __construct(string $name, string $title, int $order, string $content) {
			$this->_name = $name;
			$this->_title = $title;
			$this->_order = $order;
			$this->_content = $content;
		}

		public function getName(): string {
			return $this->_name;
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