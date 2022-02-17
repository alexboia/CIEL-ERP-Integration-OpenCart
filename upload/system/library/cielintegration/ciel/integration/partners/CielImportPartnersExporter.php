<?php
namespace Ciel\Api\Integration\Partners {
	class CielImportPartnersExporter {
		private $_localPartners;
		
		private $_parameters;

		public function __construct(array $localPartners, CielErpPartnerExportParameters $parameters) {
			$this->_localPartners = $localPartners;
			$this->_parameters = $parameters;
		}

		public function makeCsv() {
			ob_start();
			$outputStream = fopen('php://output', 'w'); 

			$headerFields = $this->_getExportHeaderFields();
			fputcsv($outputStream, 
				$headerFields, 
				$this->_parameters->getSeparatorChar(), 
				$this->_parameters->getEnclosureChar());

			foreach ($this->_localPartners as $local) {
				$dataRowFields = $this->_getExportDataRowFields($local);
					fputcsv($outputStream, 
						$dataRowFields, 
						$this->_parameters->getSeparatorChar(), 
						$this->_parameters->getEnclosureChar());
			}

			fclose($outputStream);
			return ob_get_clean();
		}

		private function _getExportHeaderFields() {
			return array(
				'Cod',
				'Denumire',
				'Blocat',
				'Atribut fiscal',
				'Cod fiscal',
				'Numar la registrul comertului',
				'Strada',
				'Localitate',
				'Judet',
				'Tara',
				'Rezidenta',
				'Banca',
				'IBAN',
				'Termen de plata',
				'Sablon partener',
				'Comentarii/observatii'
			);
		}

		private function _getExportDataRowFields($localPartnerData) {
			$varNumberParts = $this->_parseTaxCodeForExport($localPartnerData);

			return array(
				$this->_getPartnerCodeForExport($localPartnerData),
				$this->_getPartnerNameForExport($localPartnerData),
				$this->_parameters->getIsBlocked(),
				$varNumberParts['tax_attribute'],
				$varNumberParts['tax_code'],
				$localPartnerData['address']['address_trade_reg_number'],
				$this->_getStreetNameForExport($localPartnerData),
				$this->_normalizeTerritoryNameForExport($localPartnerData['address']['address_city_name']),
				$this->_normalizeTerritoryNameForExport($localPartnerData['address']['address_county_name']),
				$this->_normalizeTerritoryNameForExport($localPartnerData['address']['address_country_name']),
				'',
				$localPartnerData['address']['address_bank'],
				$localPartnerData['address']['address_iban'],
				$this->_parameters->getPaymentDueDays(),
				$this->_parameters->getPartnerTemplate(),
				$this->_getEmailAddressForExport($localPartnerData)
			);
		}

		private function _getPartnerCodeForExport($localPartnerData) {
			$code = $localPartnerData['code'];
			if (empty($code)) {
				$code = str_pad($localPartnerData['id'], 7, '0', STR_PAD_LEFT);
			}
			return $code;
		}

		private function _getPartnerNameForExport($localPartnerData) {
			$nameForExport = $this->_getPartnerPersonNameForExport($localPartnerData);
			if (!empty($localPartnerData['address']['address_company_name'])) {
				$nameForExport = $localPartnerData['address']['address_company_name'];
			}
			return $this->_normalizePartnerNameForExport($nameForExport);
		}

		private function _getPartnerPersonNameForExport($localPartnerData) {
			$nameForExport = '';

			if (!empty($localPartnerData['first_name'])) {
				$nameForExport = $localPartnerData['first_name'];
				if (!empty($localPartnerData['last_name'])) {
					$nameForExport = sprintf('%s %s', $nameForExport, $localPartnerData['last_name']);
				}
			}

			if (!empty($localPartnerData['address']['address_first_name'])) {
				$nameForExport = $localPartnerData['address']['address_first_name'];
				if (!empty($localPartnerData['address']['address_last_name'])) {
					$nameForExport = sprintf('%s %s', $nameForExport, $localPartnerData['address']['address_last_name']);
				}
			}

			return $nameForExport;
		}

		private function _normalizePartnerNameForExport($nameForExport) {
			$normalizedNameForExport = trim(strtoupper($nameForExport));
			$normalizedNameForExport = preg_replace('/[\s+]/', ' ', $normalizedNameForExport);
			return $normalizedNameForExport;
		}

		private function _parseTaxCodeForExport($localPartnerData) {
			$taxCode = '';
			$taxAttribute = '';

			$rawTaxCode = $this->_getRawTaxCodeForExport($localPartnerData);
			if (!empty($rawTaxCode)) {
				if (stripos($rawTaxCode, 'RO') !== false) {
					$taxAttribute = 'RO';
					$taxCode = str_ireplace('RO', '', $rawTaxCode);
				} else {
					$taxCode = $rawTaxCode;
				}
			}

			return array(
				'tax_code' => $this->_normalizeFinalTaxCodeForExport($taxCode),
				'tax_attribute' => $taxAttribute
			);
		}

		private function _getRawTaxCodeForExport($localPartnerData) {
			$rawTaxCode = $localPartnerData['tax_code'];
			if (!empty($localPartnerData['address']['address_tax_code'])) {
				$rawTaxCode = $localPartnerData['address']['address_tax_code'];
			}
			return $this->_normalizeRawTaxCodeForExport($rawTaxCode);
		}

		private function _normalizeRawTaxCodeForExport($rawTaxCode) {
			$normalizedRawTaxCode = strtoupper(trim($rawTaxCode));
			$normalizedRawTaxCode = preg_replace('/[^a-zA-Z0-9]/', '', $normalizedRawTaxCode);
			return $normalizedRawTaxCode;
		}

		private function _normalizeFinalTaxCodeForExport($taxCode) {
			$normalizedTaxCode = strlen($taxCode) > 10 
				? substr($taxCode, 0, 10) 
				: $taxCode;

			$normalizedTaxCode = preg_replace('/[^0-9]/', '', $normalizedTaxCode);
			return $normalizedTaxCode;
		}

		private function _getStreetNameForExport($localPartnerData) {
			$streetParts = array();

			if (!empty($localPartnerData['address']['address_lines_1'])) {
				$streetParts[] = $this->_normalizeStreetAddressLineForExport($localPartnerData['address']['address_lines_1']);
			}

			if (!empty($localPartnerData['address']['address_lines_2'])) {
				$streetParts[] = $this->_normalizeStreetAddressLineForExport($localPartnerData['address']['address_lines_2']);
			}

			return join(', ', $streetParts);
		}

		private function _normalizeStreetAddressLineForExport($line) {
			$normalizedLine = preg_replace('/[\s+]/', ' ', $line);
			$normalizedLine = ucwords(strtolower($normalizedLine));
			return $normalizedLine;
		}

		private function _getEmailAddressForExport($localPartnerData) {
			$email = $localPartnerData['email'];
			if (!empty($localPartnerData['address']['address_email'])) {
				$email = $localPartnerData['address']['address_email'];
			}
			return strtolower($email);
		}

		private function _normalizeTerritoryNameForExport($name) {
			$normalizedName = $this->_formatValueForExport($name);
			$normalizedName = ucwords($normalizedName);
			return $normalizedName;
		}

		private function _formatValueForExport($value) {
			$fmtValue = preg_replace('/[^a-zA-Z0-9\\-\s\\/]/', '', $value);
			$fmtValue = preg_replace('/\s+/', ' ', $fmtValue);
			return $fmtValue;
		}
	}
}