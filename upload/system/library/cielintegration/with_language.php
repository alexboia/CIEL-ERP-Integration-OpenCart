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

		protected function _loadText(array &$data, $key) {
			$data[$key] = $this->_t($key);
			return $data;
		}

		protected function _ta(array $keys) {
			$texts = array();
			foreach ($keys as $index => $key) {
				$textKey = is_string($index) && !is_numeric($index) 
					? $index 
					: $key;

				$texts[$textKey] = $this->_t($key);
			}
			return $texts;
		}

		protected function _loadTexts(array &$data, array $keys) {
			$texts = $this->_ta($keys);
			foreach ($texts as $key => $text) {
				$data[$key] = $text;
			}
			return $data;
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