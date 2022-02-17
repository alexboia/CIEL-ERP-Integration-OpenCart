<?php
namespace Ciel\Api\Integration\Articles {
	class CielErpArticleExportParameters {
		private $_defaultTaxRate = CielErpArticleExportDefaults::DEFAULT_TAX_RATE;

		private $_taxableVatOptionName = CielErpArticleExportDefaults::DEFAULT_TAXABLE_VAT_OPTION_NAME;

		private $_nonTaxableVatOptionName = CielErpArticleExportDefaults::DEFAULT_NONTAXABLE_VAT_OPTION_NAME;

		private $_measurementUnitName = CielErpArticleExportDefaults::DEFAULT_MEASUREMENT_UNIT_NAME;

		private $_isBlocked = CielErpArticleExportDefaults::DEFAULT_IS_BLOCKED;

		private $_isStoreable = CielErpArticleExportDefaults::DEFAULT_IS_STOREABLE;

		private $_priceCurrencyCode = CielErpArticleExportDefaults::DEFAULT_PRICE_CURRENCY_CODE;

		private $_articleTemplateName = CielErpArticleExportDefaults::DEFAULT_ARTICLE_TEMPLATE_NAME;

		private $_articleCategoryPartCount = CielErpArticleExportDefaults::DEFAULT_ARTICLE_CATEGORY_PART_COUNT;

		private $_articleCategoryNameSeparator = CielErpArticleExportDefaults::DEFAULT_ARTICLE_CATEGORY_NAME_SEPARATOR;

		private $_separatorChar = CielErpArticleExportDefaults::DEFAULT_SEPARATOR_CHAR;

		private $_enclosureChar = CielErpArticleExportDefaults::DEFAULT_ENCLOSURE_CHAR;

		private $_thousandsSeparatorChar = CielErpArticleExportDefaults::DEFAULT_THOUSANDS_SEPARATOR_CHAR;

		private $_decimalPointChar = CielErpArticleExportDefaults::DEFAULT_DECIMAL_POINT_CHAR;

		public function getDecimalPointChar() {
			return $this->_decimalPointChar;
		}

		public function setDecimalPointChar($char) {
			$this->_decimalPointChar = $char;
			return $this;
		}

		public function getThousandsSeparatorChar() {
			return $this->_thousandsSeparatorChar;
		}

		public function setThousandsSeparatorChar($char) {
			$this->_thousandsSeparatorChar = $char;
			return $this;
		}

		public function getDefaultTaxRate() {
			return $this->_defaultTaxRate;
		}

		public function setDefaultTaxRate($defaultTaxRate) {
			$this->_defaultTaxRate = $defaultTaxRate;
			return $this;
		}

		public function getTaxableVatOptionName() {
			return $this->_taxableVatOptionName;
		}

		public function setTaxableVatOptionName($taxableVatOptionName) {
			return $this->_taxableVatOptionName = $taxableVatOptionName;
			return $this;
		}

		public function getNonTaxableVatOptionName() {
			return $this->_nonTaxableVatOptionName;
		}

		public function setNonTaxableVatOptionName($nonTaxableVatOptionName) {
			return $this->_nonTaxableVatOptionName;
			return $this;
		}

		public function getMeasurementUnitName() {
			return $this->_measurementUnitName;
		}

		public function setMeasurementUnitName($measurementUnitName) {
			return $this->_measurementUnitName = $measurementUnitName;
			return $this;
		}

		public function getIsBlocked() {
			return $this->_isBlocked;
		}

		public function setIsBlocked($isBlocked) {
			$this->_isBlocked = $isBlocked;
			return $this;
		}

		public function getIsStoreable() {
			return $this->_isStoreable;
		}

		public function setIsStoreable($isStoreable) {
			$this->_isStoreable = $isStoreable;
			return $this;
		}

		public function getPriceCurrencyCode() {
			return $this->_priceCurrencyCode;
		}

		public function setPriceCurrencyCode($priceCurrencyCode) {
			$this->_priceCurrencyCode = $priceCurrencyCode;
			return $this;
		}

		public function getArticleTemplateName() {
			return $this->_articleTemplateName;
		}

		public function setArticleTemplateName($articleTemplateName) {
			$this->_articleTemplateName = $articleTemplateName;
			return $this;
		}

		public function getArticleCategoryPartCount() {
			return $this->_articleCategoryPartCount;
		}

		public function setArticleCategoryPartCount($articleCategoryPartCount) {
			$this->_articleCategoryPartCount = $articleCategoryPartCount;
			return $this;
		}

		public function getArticleCategoryNameSeparator() {
			return $this->_articleCategoryNameSeparator;
		}

		public function setArticleCategoryNameSeparator($articleCategoryNameSeparator) {
			$this->_articleCategoryNameSeparator = $articleCategoryNameSeparator;
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