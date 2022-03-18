<?php
namespace CielIntegration\Integration\Admin {

    use Ciel\Api\Data\DocumentStatusType;
    use Ciel\Api\Data\DocumentType;
    use CielIntegration\Integration\Admin\StockUpdateMode;
    use CielIntegration\WithLanguage;
    use Exception;
    use ModelCustomerCustomerGroup;
    use ModelLocalisationGeoZone;
    use ModelLocalisationLengthClass;
    use ModelLocalisationOrderStatus;
    use ModelLocalisationStockStatus;
    use ModelLocalisationWeightClass;

	/**
	 * @property \Loader $load
	 */
	class LookupDataProvider extends IntegrationService {
		use WithLanguage;

		public function __construct(\Registry $registry) {
			parent::__construct($registry);
		}

		public function getOpenCartOrderStatuses() {
			$orderStatusModel = $this->_getOrderStatusModel();
			$statusRows = $orderStatusModel->getOrderStatuses(array(
				'order' => 'ASC'
			));

			$statuses = array();
			foreach ($statusRows as $sRow) {
				$statuses[$sRow['order_status_id']] = $sRow['name'];
			}

			return $statuses;
		}

		/**
		 * @return ModelLocalisationOrderStatus
		 */
		private function _getOrderStatusModel() {
			$this->load->model('localisation/order_status');
			return $this->model_localisation_order_status;
		}

		public function getSupportedDocumentStatusTypes() {
			return array(
                DocumentStatusType::Valid => $this->_t('option_txt_document_status_type_valid'),
                DocumentStatusType::Temporary => $this->_t('option_txt_document_status_type_temporary')
            );
		}

		public function getSupportedStockUpdateModes() {
			return array(
                StockUpdateMode::Manual => $this->_t('option_txt_stock_update_manual'),
                StockUpdateMode::SystemCron => $this->_t('option_txt_stock_update_system_cron')
            );
		}

		public function getSupportedDocumentTypes() {
 			return array(
                DocumentType::None => $this->_t('option_txt_document_type_none'),
                DocumentType::SaleOrder => $this->_t('option_txt_document_type_sale_order'),
                DocumentType::SaleInvoice => $this->_t('option_txt_document_type_sale_invoice')
            );
		}

		public function getDocumentTypeName($typeId) {
			$types = $this->getSupportedDocumentTypes();
            return isset($types[$typeId]) 
                ? $types[$typeId] 
                : null;
		}

		public function getOpenCartStockStatuses() {
			$statuses = array();
			$stockStatusModel = $this->_getStockStatusModel();
			$stockStatusRows = $stockStatusModel->getStockStatuses();
			
			foreach ($stockStatusRows as $sRow) {
				$statuses[$sRow['stock_status_id']] = $sRow['name'];
			}

			return $statuses;
		}

		/**
		 * @return ModelLocalisationStockStatus
		 */
		private function _getStockStatusModel() {
			$this->load->model('localisation/stock_status');
			return $this->model_localisation_stock_status;
		}

		public function getOpenCartGeoZones() {
			$geoZones = array();
			$geoZoneModel = $this->_getGeoZonesModel();
			$geoZonesRows = $geoZoneModel->getGeoZones();

			foreach ($geoZonesRows as $gRow) {
				$geoZones[$gRow['geo_zone_id']] = $gRow['name'];
			}

			return $geoZones;
		}

		/**
		 * @return ModelLocalisationGeoZone
		 */
		private function _getGeoZonesModel() {
			$this->load->model('localisation/geo_zone');
			return $this->model_localisation_geo_zone;
		}

		
		public function getOpenCartCustomerGroups() {
			$customerGroups = array();
			$customerGroupModel = $this->_getCustomerGroupModel();
			$customerGroupRows = $customerGroupModel->getCustomerGroups();

			foreach ($customerGroupRows as $cRow) {
				$customerGroups[$cRow['customer_group_id']] = $cRow['name'];
			}

			return $customerGroups;
		}

		/**
		 * @return ModelCustomerCustomerGroup
		 */
		private function _getCustomerGroupModel() {
			$this->load->model('customer/customer_group');
			return $this->model_customer_customer_group;
		}

		public function getOpenCartLengthClasses() {
			$lengthClasses = array();
			$lengthClassModel = $this->_getLengthClassModel();
			$lengthClassesRows = $lengthClassModel->getLengthClasses();

			foreach ($lengthClassesRows as $lRow) {
				$lengthClasses[$lRow['length_class_id']] = sprintf('%s (%s)', 
					$lRow['title'], 
					$lRow['unit']);
			}

			return $lengthClasses;
		}

		/**
		 * @return ModelLocalisationLengthClass
		 */
		private function _getLengthClassModel() {
			$this->load->model('localisation/length_class');
			return $this->model_localisation_length_class;
		}

		public function getOpenCartWeightClasses() {
			$weightClasses = array();
			$weightClassModel = $this->_getWeightClassModel();
			$weightClassesRows = $weightClassModel->getWeightClasses();

			foreach ($weightClassesRows as $wRow) {
				$weightClasses[$wRow['weight_class_id']] = sprintf('%s (%s)', 
					$wRow['title'], 
					$wRow['unit']);
			}

			return $weightClasses;
		}

		/**
		 * @return ModelLocalisationWeightClass
		 */
		private function _getWeightClassModel() {
			$this->load->model('localisation/weight_class');
			return $this->model_localisation_weight_class;
		}

		public function getOpenCartCustomerCustomFields($customerGroupId = null, $location = 'address') {
			$result = array();
			$params = array();
			$customFieldsModel = $this->_getCustomerCustomFieldsModel();

			if (!empty($customerGroupId)) {
				$params['filter_customer_group_id'] = $customerGroupId;
			}

			$customFieldsRows = $customFieldsModel
				->getCustomFields($params);

			foreach ($customFieldsRows as $row) {		
				if (!empty($location)) {
					if ($row['location'] != $location) {
						continue;
					}
				}

				$id = intval($row['custom_field_id']);
				$result[$id] = array(
					'id' => $id,
					'name' => $row['name'],
					'location' => $row['location'],
					'status' => $row['status'],
					'groups' => $this->_getCustomerCustomFieldGroups($id)
				);
			}

			return $result;
		}

		private function _getCustomerCustomFieldGroups($customFieldId) {
			$ids = array();
			$customFieldsModel = $this->_getCustomerCustomFieldsModel();

			$groups = $customFieldsModel->getCustomFieldCustomerGroups($customFieldId);
			if (!empty($groups)) {
				foreach ($groups as $g) {
					$ids[] = intval($g['customer_group_id']);
				}
			}

			return array_unique($ids);
		}

		/**
		 * @return \ModelCustomerCustomField
		 */
		private function _getCustomerCustomFieldsModel() {
			$this->load->model('customer/custom_field');
			return $this->model_customer_custom_field;
		}
	}
}