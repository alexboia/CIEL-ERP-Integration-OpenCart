<?php
namespace CielIntegration\Integration\Admin {

    use InvalidArgumentException;
    use ModelExtensionExtension;
    use ModelSettingSetting;

	class ShippingService extends IntegrationService {
		private $_knownShippingMethodTaxClassIdKeys = array(
			'item' => 'item_tax_class_id',
			'flat' => 'flat_tax_class_id',
			'auspost' => 'auspost_tax_class_id',
			'citylink' => 'citylink_tax_class_id',
			'fedex' => 'fedex_tax_class_id',
			'free' => null,
			'parcelforce_48' => 'parcelforce_48_tax_class_id',
			'pickup' => null,
			'royal_mail' => 'royal_mail_tax_class_id',
			'ups' => 'ups_tax_class_id',
			'usps' => 'usps_tax_class_id',
			'weight' => 'weight_tax_class_id'
		);

		private $_fallbackTaxClassIdKeyPatterns = array(
			'placeholder' => array(
				'[shipping_code]_tax_class_id'
			),
			'regex' => array(
				'/^(.+)tax_class_id(.*)$/i'
			)
		);

		public function getShippingMethodCodes() {
			$extensionModel = $this->_getExtensionModel();
			return $extensionModel
				->getInstalled('shipping');
		}

		/**
		 * @return ModelExtensionExtension
		 */
		private function _getExtensionModel() {
			$this->load->model('extension/extension');
			return $this->model_extension_extension;
		}

		public function getShippingMethodConfig($code) {
			if (empty($code)) {
				return null;
			}
		
			$settingModel = $this->_getSettingModel();
			return $settingModel
				->getSetting($code);
		}

		public function udpateShippingMethodConfig($code, $settingsSet) {
			if (empty($code)) {
				throw new InvalidArgumentException('Shipping method code may not be empty.');
			}

			$settingModel = $this->_getSettingModel();
			$settingModel->editSetting($code, 
				$settingsSet);
		}

		public function getShippingMethodTaxClassId($code) {
			$settingsSet = $this->getShippingMethodConfig($code);
			if (empty($settingsSet)) {
				return null;
			}

			$taxClassIdKey = $this->_tryGetTaxClassIdKey($code, 
				$settingsSet);

			if (!empty($taxClassIdKey)) {
				return $settingsSet[$taxClassIdKey];
			} else {
				return null;
			}
		}

		private function _tryGetTaxClassIdKey($code, $settingsSet) {
			$taxClassIdKey = $this->_tryGetTaxClassIdKeyUsingKnownKeyLookup($code, 
				$settingsSet);

			if (empty($taxClassIdKey)) {
				$taxClassIdKey = $this->_tryGetTaxClassIdKeyUsingPlaceholderMatching($code, 
					$settingsSet);

				if (empty($taxClassIdKey)) {
					$taxClassIdKey = $this->_tryGetTaxClassIdKeyUsingRegexMatching($code, 
						$settingsSet);
				}
			}

			return $taxClassIdKey;
		}

		private function _tryGetTaxClassIdKeyUsingKnownKeyLookup($code, $settingsSet) {
			$taxClassIdKey = null;
			$knownKey = $this->_getKnownTaxClassIdKey($code);

			if (!empty($knownKey) && isset($settingsSet[$knownKey])) {
				$taxClassIdKey = $knownKey;
			}

			return $taxClassIdKey;
		}

		private function _getKnownTaxClassIdKey($code) {
			return isset($this->_knownShippingMethodTaxClassIdKeys[$code])
				? $this->_knownShippingMethodTaxClassIdKeys[$code]
				: null;
		}

		private function _tryGetTaxClassIdKeyUsingPlaceholderMatching($code, $settingsSet) {
			$taxClassIdKey = null;
			foreach ($this->_fallbackTaxClassIdKeyPatterns['placeholder'] as $pattern) {
				$candidateKey = str_replace('[shipping_code]', $code, $pattern);
				if (isset($settingsSet[$candidateKey])) {
					$taxClassIdKey = $candidateKey;
					break;
				}
			}
			return $taxClassIdKey;
		}

		private function _tryGetTaxClassIdKeyUsingRegexMatching($code, $settingsSet) {
			$taxClassIdKey = null;
			foreach ($this->_fallbackTaxClassIdKeyPatterns['regex'] as $pattern) {
				foreach (array_keys($settingsSet) as $key) {
					if (preg_match($pattern, $key)) {
						$taxClassIdKey = $key;
						break;
					}
				}				
			}
			return $taxClassIdKey;
		}

		public function setTaxClassIdForActiveShippingMethods($taxClassId) {
			$updatedMethodCodes = array();
			$shippingMethods = $this->getShippingMethodCodes();

			foreach ($shippingMethods as $methodCode) {
				$settingsSet = $this->getShippingMethodConfig($methodCode);
				$taxClassIdKey = $this->_tryGetTaxClassIdKey($methodCode, 
					$settingsSet);

				if (!empty($taxClassIdKey)) {
					$updatedMethodCodes[] = $methodCode;
					$settingsSet[$taxClassIdKey] = $taxClassId;
					$this->udpateShippingMethodConfig($methodCode, 
						$settingsSet);
				}
			}

			return $updatedMethodCodes;
		}

		/**
		 * @return ModelSettingSetting
		 */
		private function _getSettingModel() {
			$this->load->model('setting/setting');
			return $this->model_setting_setting;
		}
	}
}