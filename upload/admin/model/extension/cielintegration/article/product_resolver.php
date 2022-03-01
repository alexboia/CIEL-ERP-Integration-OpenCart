<?php
namespace CielIntegration\Integration\Admin\Article {
    use CielIntegration\Integration\Admin\IntegrationService;
    use CielIntegration\WithRouteUrl;
    use ModelCatalogProduct;

	/**
	 * @property \DB $db;
	 */
	class ProductResolver extends IntegrationService {
		use WithRouteUrl;

		public function getProductEditUrl($producId) {
			return $this->_createRouteUrl('catalog/product/edit', array(
				'product_id' => $producId
			));
		}

		public function getRemoteArticleData($productId) {
			if (empty($productId)) {
				return null;
			}

			return $this->_getRemoteArticleModel()
				->getByProductId($productId);
		}

		public function getEmptyRemoteArticleData($productId) {
			return array(
				'product_id' => $productId,
				'remote_id' => null,
				'remote_measurement_unit' => null,
				'remote_price_vat_quota_value' => null,
				'remote_price_vat_option_name' => null,
				'remote_batch_tracking_enabled' => null
			);
		}

		public function getVatOutOptionName($productId) {
			if (empty($productId)) {
				return null;
			}

			return $this->_getRemoteArticleModel()
				->getVatOutOptionName($productId);
		}

		public function getVatOutQuotaValue($productId) {
			if (empty($productId)) {
				return 0;
			}

			return $this->_getRemoteArticleModel()
				->getVatOutQuotaValue($productId);
		}

		public function getBatchTrackingStatus($productId) {
			if (empty($productId)) {
				return false;
			}

			return $this->_getRemoteArticleModel()
				->getBatchStrackingStatusByProductId($productId);
		}

		public function isConnectedToCielErp($productId) {
			if (empty($productId)) {
				return false;
			}

			return $this->_getRemoteArticleModel()
				->isConnectedToCielErp($productId);
		}

		public function areConnectedToCielErp(array $productIds) {
			if (empty($productIds)) {
				return array();
			}

			return $this->_getRemoteArticleModel()
				->areConnectedToCielErp($productIds);
		}

		public function lookupRemoteArticleId($productId) {
			if (empty($productId)) {
				return false;
			}

			return $this->_getRemoteArticleModel()
				->getRemoteArticleId($productId);
		}

		public function getAllConnectedLocalProductIdsBySkus() {
			return $this->_getRemoteArticleModel()
				->getAllProductIdsBySkus();
		}

		public function productExits($productId) {
			if (empty($productId)) {
				return false;
			}

			$db = $this->_getDb();
			$result = $db->query('SELECT COUNT(product_id) as product_count FROM `' . DB_PREFIX . 'product` WHERE product_id = "' . intval($productId) . '"');
			
			$row = $result->row;
			return !empty($row) && !empty($row['product_count'])
				? intval($row['product_count']) > 0
				: false;
		}

		public function productSkuExists($sku) {
			if (empty($sku)) {
				return false;
			}

			return $this->lookupProductId($sku) 
				> 0;
		}

		public function lookupProductId($sku) {
			if (empty($sku)) {
				return 0;
			}

			$db = $this->_getDb();
			$result = $db->query('SELECT product_id FROM `' . DB_PREFIX . 'product` WHERE sku = "' . $db->escape($sku) . '"');

			$row = $result->row;
			return !empty($row) && !empty($row['product_id'])
				? intval($row['product_id'])
				: 0;
		}

		public function lookupProductSku($productId) {
			if (empty($productId)) {
				return 0;
			}

			$db = $this->_getDb();
			$result = $db->query('SELECT sku FROM `' . DB_PREFIX . 'product` WHERE product_id = "' . intval($productId) . '"');

			$row = $result->row;
			return !empty($row) && !empty($row['sku'])
				? $row['sku']
				: null;
		}

		public function getAllProducts() {
			return $this->_getCatalogProductModel()
				->getProducts(array(
					'filter_status' => 1
				));
		}

		public function getProduct($productId) {
			if (empty($productId)) {
				return null;
			}

			return $this->_getCatalogProductModel()
				->getProduct($productId);
		}

		/**
		 * @return ModelCatalogProduct
		 */
		private function _getCatalogProductModel() {
			$this->load->model('catalog/product');
			return $this->model_catalog_product;
		}

		/**
		 * @return \DB
		 */
		protected function _getDb() {
			return $this->db;
		}
	}
}