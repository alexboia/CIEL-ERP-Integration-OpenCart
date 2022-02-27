<?php
namespace CielIntegration {
	use \Loader;

	/** @property \Loader $load */
	/** @property \Language $language */
	trait WithLanguage {
		private $_loaded = false;

		private $_textDomain = null;

		protected function _t($key) {
			$this->_ensureLanguageLoaded();
			return $this->language->get($key);
		}

		private function _ensureLanguageLoaded() {
			if (!$this->_loaded) {
				$this->language->load('extension/module/ciel');
				if (!empty($this->_textDomain)) {
					$this->language->load($this->_textDomain);
				}
				$this->_loaded = true;
			}
		}

		protected function _setTextDomain($textDomain) {
			$this->_textDomain = $textDomain;
		}
	}
}