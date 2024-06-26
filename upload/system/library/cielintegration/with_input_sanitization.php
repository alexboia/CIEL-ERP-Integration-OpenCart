<?php
namespace CielIntegration {
	trait WithInputSanitization {
		protected function _sanitizeTextInput($value) {
			if (is_object($value) || is_array($value)) {
				return '';
			}

			$safeValue = strip_tags($value);
			$safeValue = filter_var($safeValue, FILTER_SANITIZE_SPECIAL_CHARS);

			return $safeValue !== false 
				? $safeValue 
				: '';
		}

		protected function _sanitizeTextInputArray(array $values) {
			$safeValues = array();

			foreach ($values as $key => $value) {
				$safeValues[$key] = $this->_sanitizeTextInput($value);
			}

			return $safeValues;
		}

		protected function _sanitizeUrl($value) {
			if (is_object($value) || is_array($value)) {
				return '';
			}

			$safeValue = strip_tags($value);
			$safeValue = filter_var($safeValue, FILTER_SANITIZE_URL);

			return $safeValue !== false 
				? $safeValue 
				: '';
		}
	}
}