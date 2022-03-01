<?php
namespace CielIntegration\Integration\Admin\Article {

	use CielIntegration\Integration\Admin\IntegrationService;
	use ModelLocalisationTaxClass;
	use ModelLocalisationTaxRate;

	/**
	 * @property \Loader $load
	 */
	class TaxService extends IntegrationService {
		const BASE_CIEL_ERP_TAX_CLASS_NAME = 'CIEL ERP';

		public function __construct(\Registry $registry) {
			parent::__construct($registry);
		}

		public function getOrCreateTaxClass($vatOptionName, $vatQuotaName, $vatQuotaValue) {
			$taxRate = $this->_getTaxRatesByVatQuotaProperties($vatQuotaName, 
				$vatQuotaValue);

			if (empty($taxRate)) {
				$taxRate = $this->_createTaxRate($vatQuotaName, 
					$vatQuotaValue);
			}

			$taxClassName = $this->_deriveTaxClassNameFromOptionAndQuotaNames($vatOptionName, 
				$vatQuotaName);
			
			$taxClass = $this->_getTaxClassByName($taxClassName);
			if (empty($taxClass)) {
				$taxClass = $this->_createTaxClass($taxClassName);
			}

			$this->_ensureTaxClassRules($taxClass, 
				$taxRate);

			return $taxClass;
		}

		private function _getTaxRatesByVatQuotaProperties($vatQuotaName, $vatQuotaValue) {
			$taxRate = null;
			$taxRatesRows = $this->_getAllTaxRates();
			$geoZoneId = $this->_getWorkflow()
				->getNewTaxRateGeoZoneId();

			foreach ($taxRatesRows as $tRow) {
				if (strcasecmp($tRow['name'], $vatQuotaName) === 0 
					&& $tRow['rate'] == $vatQuotaValue 
					&& $tRow['type'] == 'P'
					&& $tRow['geo_zone_id'] == $geoZoneId) {
					$taxRate = $tRow;
					break;
				}
			}

			return $taxRate;
		}

		private function _getAllTaxRates() {
			$db = $this->_getDb();
			$result = $db->query('SELECT * FROM `' . DB_PREFIX . 'tax_rate` ORDER BY name ASC');
			if (!empty($result) && !empty($result->rows)) {
				return $result->rows;
			} else {
				return array();
			}
		}

		private function _createTaxRate($vatQuotaName, $vatQuotaValue) {
			$geoZoneId = $this->_getWorkflow()
				->getNewTaxRateGeoZoneId();
			$customerGroupId = $this->_getWorkflow()
				->getNewTaxRateCustomerGroupId();

			$data = array(
				'type' => 'P',
				'name' => $vatQuotaName,
				'rate' => $vatQuotaValue,
				'geo_zone_id' => $geoZoneId,
				'tax_rate_customer_group' => array(
					$customerGroupId
				)
			);

			$taxRateModel = $this->_getTaxRateModel();
			$taxRateId = $taxRateModel->addTaxRate($data);

			return $taxRateModel
				->getTaxRate($taxRateId);
		}

		private function _deriveTaxClassNameFromOptionAndQuotaNames($vatOptionName, $vatQuotaName) {
			if (!empty($vatOptionName)) {
				$taxClassName = sprintf('%s - %s %s', self::BASE_CIEL_ERP_TAX_CLASS_NAME, 
					$vatOptionName, 
					$vatQuotaName);
			} else {
				$taxClassName = sprintf('%s - Standard %s', 
					self::BASE_CIEL_ERP_TAX_CLASS_NAME, 
					$vatQuotaName);
			}

			return $taxClassName;
		}

		private function _getTaxClassByName($taxClassName) {
			$taxClass = null;
			$taxClassRows = $this->_getTaxClassModel()
				->getTaxClasses();

			foreach ($taxClassRows as $tRow) {
				if (strcasecmp($tRow['title'], $taxClassName) === 0) {
					$taxClass = $tRow;
					break;
				}
			}

			return $taxClass;
		}

		private function _createTaxClass($taxClassName) {
			$data = array(
				'title' => $taxClassName,
				'description' => $taxClassName,
				'tax_rule' => array()
			);

			$taxClassModel = $this->_getTaxClassModel();
			$taxClassId = $taxClassModel->addTaxClass($data);

			return $taxClassModel
				->getTaxClass($taxClassId);
		}

		private function _ensureTaxClassRules($taxClass, $taxRate) {
			$found = false;
			$taxRateId = $taxRate['tax_rate_id'];
			$taxClassId = $taxClass['tax_class_id'];
			$taxClassModel = $this->_getTaxClassModel();

			$taxRules = $taxClassModel->getTaxRules($taxClass['tax_class_id']);
			if (!empty($taxRules)) {
				foreach ($taxRules as $r) {
					if ($r['tax_class_id'] == $taxClassId 
						&& $r['tax_rate_id'] == $taxRateId 
						&& $r['based'] == 'payment') {
						$found = true;
						break;
					}
				}
			}

			if (!$found) {
				$taxClassWithRule = array_merge($taxClass, array(
					'tax_rule' => array(
						array(
							'tax_class_id' => $taxClassId,
							'tax_rate_id' => $taxRateId,
							'based' => 'payment',
							'priority' => 0
						)
					)
				));

				$taxClassModel->editTaxClass($taxClassId, 
					$taxClassWithRule);
			}
		}

		public function deriveVatQuotaNameFromQuotaValue($vatQuotaValue) {
			return sprintf('%s %%', strval($vatQuotaValue));
		}

		/**
		 * @return ModelLocalisationTaxClass
		 */
		private function _getTaxClassModel() {
			$this->load->model('localisation/tax_class');
			return $this->model_localisation_tax_class;
		}

		/**
		 * @return ModelLocalisationTaxRate
		 */
		private function _getTaxRateModel() {
			$this->load->model('localisation/tax_rate');
			return $this->model_localisation_tax_rate;
		}

		public function productPricesIncludeTaxes() {
			return false;
		}

		public function useTaxes() {
			return true;
		}

		/**
		 * @return \DB
		 */
		private function _getDb() {
			return $this->db;
		}
	}
}