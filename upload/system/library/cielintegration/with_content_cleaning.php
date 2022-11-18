<?php
namespace CielIntegration {
	trait WithContentCleaning {
		private $_cleanRepair = true;

		public function disableCleanRepair() {
			$this->_cleanRepair = false;
			return $this;
		}

		public function enableCleanRepair() {
			$this->_cleanRepair = true;
			return $this;
		}

		protected function _shouldCleanRepair() {
			return $this->_cleanRepair;
		}

		protected function _cleanRepairContents($contents) {
			if ($this->_shouldCleanRepair()) {
				return \myc_clean_repair_html($contents);
			} else {
				return $contents;
			}
		}
	}
}