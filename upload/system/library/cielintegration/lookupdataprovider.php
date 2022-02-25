<?php
namespace CielIntegration {

    use Ciel\Api\Data\DocumentStatusType;
    use Ciel\Api\Data\DocumentType;
    use CielIntegration\Integration\StockUpdateMode;
    use ModelLocalisationOrderStatus;
    use ModelLocalisationStockStatus;

	/**
	 * @property \Loader $load
	 */
	class LookupDataProvider extends \Model {
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
	}
}