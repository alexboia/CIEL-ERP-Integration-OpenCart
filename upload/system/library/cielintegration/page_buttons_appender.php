<?php
namespace CielIntegration {
	class PageButtonsAppender {
		/**
		 * @var ContentsAppender
		 */
		private $_contentsAppender;

		/**
		 * @var array
		 */
		private $_buttons = array();

		public function __construct() {
			$this->_contentsAppender = new ContentsAppender('#content > div.page-header > div.container-fluid > div.pull-right');
		}

		public function disableCleanRepair() {
			$this->_contentsAppender->disableCleanRepair();
			return $this;
		}

		public function enableCleanRepair() {
			$this->_contentsAppender->enableCleanRepair();
			return $this;
		}

		public function addButton($id, $icon, $class = null, $url = null) {
			$this->_buttons[] = array(
				'id' => $id,
				'icon' => $icon,
				'class' => $class,
				'url' => $url
			);

			return $this;
		}

		public function rewrite($htmlContents) {
			$appendContents = $this->_buildContents();
			if (!empty($appendContents)) {
				$this->_contentsAppender
					->setContents($appendContents);				
				return $this->_contentsAppender
					->rewrite($htmlContents);
			} else {
				return $htmlContents;
			}
		}

		private function _buildContents() {
			$contents = '';

			foreach ($this->_buttons as $b) {
				$class = !empty($b['class']) 
					? sprintf('btn %s', $b['class']) 
					: 'btn';

				$url = !empty($b['url'])
					? $b['url']
					: 'javascript:void(0)';

				$bHtml = sprintf('<a id="%s" class="%s" href="%s" style="margin-left: 4px;"><i class="fa %s"></i></a>', 
					$b['id'], 
					$class, 
					$url, 
					$b['icon']);

				$contents .= $bHtml;
			}

			return $contents;
		}
	}
}