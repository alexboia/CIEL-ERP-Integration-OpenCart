<?php
namespace Ciel\Api\Integration\Orders {
	class RemoteDocumentOptions {
		const NO_CUSTOM_DUE_DAYS = 0;

		const NO_CUSTOM_DOCUMENT_SERIES = null;

		private $_documentSeries;

		private $_warehouseCode;

		private $_dueDays;

		private $_shippingVatQuotaValue;

		public function __construct(
			$warehouseCode,
			$shippingVatQuotaValue,
			$dueDays = self::NO_CUSTOM_DUE_DAYS,
			$documentSeries = self::NO_CUSTOM_DOCUMENT_SERIES
		) {
			$this->_warehouseCode = $warehouseCode;
			$this->_shippingVatQuotaValue = $shippingVatQuotaValue;
			$this->_dueDays = $dueDays;
			$this->_documentSeries = $documentSeries;
		}

		public function getWarehouseCode() {
			return $this->_warehouseCode;
		}

		public function getShippingVatQuotaValue() {
			return $this->_shippingVatQuotaValue;
		}

		public function getDueDays() {
			return $this->_dueDays;
		}

		public function getDocumentSeries() {
			return $this->_documentSeries;
		}
	}
}