<?php
namespace CielIntegration\Integration\Admin\Article {

    use CielIntegration\Integration\Admin\IntegrationService;
    use ModelCatalogManufacturer;

	class ManufacturerService extends IntegrationService {
		const DEFAULT_MANUFACTURER_NAME = 'CIEL ERP';

		private static $_defaultManufacturerId = null;

		public function getOrCreateDefaultManufacturer() {
			$manufacturerId = $this->_getDefaultManufacturerId();
			if ($manufacturerId === 0) {
				$manufacturerId = $this->_createDefaultManufacturer();
			}

			return $manufacturerId;
		}

		private function _getDefaultManufacturerId() {
			if (self::$_defaultManufacturerId === null) {
				$defaultManufacturerId = 0;
				$manufacturersModel = $this->_getManufacturerModel();
				$manufacturers = $manufacturersModel->getManufacturers();
				
				foreach ($manufacturers as $mRow) {
					if ($mRow['name'] == self::DEFAULT_MANUFACTURER_NAME) {
						$defaultManufacturerId = intval($mRow['manufacturer_id']);
						break;
					}
				}

				self::$_defaultManufacturerId = $defaultManufacturerId;
			}

			return self::$_defaultManufacturerId;
		}

		private function _createDefaultManufacturer() {
			$manufacturersModel = $this->_getManufacturerModel();
			$defaultManufacturerId = $manufacturersModel->addManufacturer(array(
				'name' => self::DEFAULT_MANUFACTURER_NAME,
				'sort_order' => 100
			));

			self::$_defaultManufacturerId = $defaultManufacturerId;
			return $defaultManufacturerId;
		}

		/**
		 * @return ModelCatalogManufacturer
		 */
		private function _getManufacturerModel() {
			$this->load->model('catalog/manufacturer');
			return $this->model_catalog_manufacturer;
		}
	}
}