<?php
namespace Ciel\Api\Integration\Articles {

use Ciel\Api\Data\LocalProductType;

class CielImportArticlesExporter {
		private $_localArticles;

		private $_parameters;

		private $_eans = array();

		public function __construct(array $localArticles, CielErpArticleExportParameters $parameters) {
			$this->_localArticles = $localArticles;
			$this->_parameters = $parameters;
		}

		private function _reset() {
			$this->_eans = array();
		}

		public function makeCsv() {
			$this->_reset();

			ob_start();
			$outputStream = fopen('php://output', 'w'); 

			$headerFields = $this->_getExportHeaderFields();
			fputcsv($outputStream, 
				$headerFields, 
				$this->_parameters->getSeparatorChar(), 
				$this->_parameters->getEnclosureChar());

			foreach ($this->_localArticles as $local) {
				$type = $local['type'];
				if ($type == LocalProductType::Simple) {
					$dataRowFields = $this->_getExportDataRowFields($local);
					fputcsv($outputStream, 
						$dataRowFields, 
						$this->_parameters->getSeparatorChar(), 
						$this->_parameters->getEnclosureChar());
				} else if ($type == LocalProductType::Variable) {
					foreach ($local['variations'] as $localVariation) {
						$dataRowFields = $this->_getExportDataRowFields($localVariation);
						fputcsv($outputStream, 
							$dataRowFields, 
							$this->_parameters->getSeparatorChar(), 
							$this->_parameters->getEnclosureChar());
					}
				}
			}

			fclose($outputStream);
			return ob_get_clean();
		}

		private function _getExportHeaderFields() {
			return array(
				'Cod',
				'Denumire',
				'Unitate de masura',
				'Cod de bare',
				'Blocat',
				'Categorie',
				'Articol stocabil',
				'Pret vanzare fara tva',
				'Pret vanzare cu tva',
				'Moneda pret vanzare',
				'Sablon cont',
				'Optiune tva pentru vanzarea cu amanuntul',
				'Cota tva pentru vanzarea cu amanuntul'
			);
		}

		private function _getExportDataRowFields($localProductData) {
			$vatQuotaValue = isset($localProductData['taxRate']) 
				? $localProductData['taxRate'] 
				: $this->_parameters->getDefaultTaxRate();

			$vatOptionName = $vatQuotaValue > 0 
				? $this->_parameters->getTaxableVatOptionName()
				: $this->_parameters->getNonTaxableVatOptionName();
	
			return array(
				$localProductData['code'],
				$this->_formatValueForExport($localProductData['name']),
				$this->_parameters->getMeasurementUnitName(),
				$this->_getEanIfUniqueOrEmpty($localProductData),
				$this->_parameters->getIsBlocked(),
				!empty($localProductData['categoryName']) 
					? $this->_formatCategoryNameForExport($localProductData['categoryName'], 
						$this->_parameters->getArticleCategoryNameSeparator(), 
						$this->_parameters->getArticleCategoryPartCount())
					: null,
				$this->_parameters->getIsStoreable(),
				$this->_formatPriceForExport($this->_getExportPriceOutWithoutVat($localProductData, $vatQuotaValue)),
				$this->_formatPriceForExport($this->_getExportPriceOutWithVat($localProductData, $vatQuotaValue)),
				$this->_parameters->getPriceCurrencyCode(),
				$this->_parameters->getArticleTemplateName(),
				$vatOptionName,
				$vatQuotaValue
			);
		}

		private function _getEanIfUniqueOrEmpty($localProductData) {
			$ean = isset($localProductData['ean'])
				? strtolower($localProductData['ean'])
				: null;

			if (!empty($ean)) {
				if (!in_array($ean, $this->_eans)) {
					$this->_eans[] = $ean;
				} else {
					$ean = null;
				}
			}

			return $ean;
		}

		private function _formatValueForExport($value) {
			$fmtValue = preg_replace('/[^a-zA-Z0-9\\-\s\\/]/', '', $value);
			$fmtValue = preg_replace('/\s+/', ' ', $fmtValue);
			return $fmtValue;
		}

		private function _formatCategoryNameForExport($categoryNameParts, $namePartsSeparator, $extractNamePartsCount) {
			$categoryNamePartsCount = count($categoryNameParts);
			if ($extractNamePartsCount <= 0) {
				$extractNamePartsCount = $categoryNamePartsCount - 1;
			}

			if ($extractNamePartsCount > 0) {
				$categoryNamePartsCount = count($categoryNameParts);
				$extractNamePartsCount = min($extractNamePartsCount, $categoryNamePartsCount);

				$categoryNameParts = array_splice($categoryNameParts, 
					$categoryNamePartsCount - $extractNamePartsCount, 
					$extractNamePartsCount);
			} else {
				$categoryNameParts = array();
			}

			return !empty($categoryNameParts)
				? $this->_formatValueForExport(join($namePartsSeparator, $categoryNameParts))
				: null;
		}

		private function _formatPriceForExport($price) {
			return number_format($price, 2, 
				$this->_parameters->getDecimalPointChar(), 
				$this->_parameters->getThousandsSeparatorChar());
		}

		private function _getExportPriceOutWithoutVat($localProductData, $vatQuotaValue) {
			$divide = 1 + ($vatQuotaValue / 100);

			$rawPrice = $localProductData['catalogPriceIncludesTax'] 
				? ($localProductData['catalogPrice'] / $divide) 
				: $localProductData['catalogPrice'];

			return round($rawPrice, 2);
		}

		private function _getExportPriceOutWithVat($localProductData, $vatQuotaValue) {
			$multiply = 1 + ($vatQuotaValue / 100);

			$rawPrice = $localProductData['catalogPriceIncludesTax'] 
				? $localProductData['catalogPrice'] 
				: ($localProductData['catalogPrice'] * $multiply);

			return round($rawPrice, 2);
		}
	}
}