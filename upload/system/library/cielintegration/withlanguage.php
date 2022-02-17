<?php
namespace CielIntegration {
	use \Loader;

	/** @property \Loader $load */
	/** @property \Language $language */
	trait WithLanguage {
		private $_loaded = false;

		protected function _t($key) {
			$this->_ensureLanguageLoaded();
			return $this->language->get($key);
		}

		private function _ensureLanguageLoaded() {
			if (!$this->_loaded) {
				$this->language->load('extension/module/ciel');
				$this->_loaded = true;
			}
		}
	}
}