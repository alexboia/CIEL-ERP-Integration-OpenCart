<?php
namespace Ciel\Api\Integration\Partners {
	class CielErpPartnerExportParameters {
		private $_isBlocked = CielErpPartnerExportDefaults::DEFAULT_IS_BLOCKED;

		private $_partnerTemplate = CielErpPartnerExportDefaults::DEFAULT_PARTNER_TEMPLATE;

		private $_paymentDueDays = CielErpPartnerExportDefaults::DEFAULT_PAYMENT_DUE_DAYS;

		private $_separatorChar = CielErpPartnerExportDefaults::DEFAULT_SEPARATOR_CHAR;

		private $_enclosureChar = CielErpPartnerExportDefaults::DEFAULT_ENCLOSURE_CHAR;

		public function getIsBlocked() {
			return $this->_isBlocked;
		}

		public function setIsBlocked($isBlocked) {
			$this->_isBlocked = $isBlocked;
			return $this;
		}

		public function getPartnerTemplate() {
			return $this->_partnerTemplate;
		}

		public function setPartnerTemplate($value) {
			$this->_partnerTemplate = $value;
			return $this;
		}

		public function getPaymentDueDays() {
			return $this->_paymentDueDays;
		}

		public function setPaymentDueDays($value) {
			$this->_paymentDueDays = $value;
			return $this;
		}

		public function getSeparatorChar() {
			return $this->_separatorChar;
		}

		public function getEnclosureChar() {
			return $this->_enclosureChar;
		}
	}
}