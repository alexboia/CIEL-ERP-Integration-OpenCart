<?php
namespace CielIntegration\Integration\Admin\Article {

    use CielIntegration\Integration\Admin\Article\Model\LocalProduct;
    use CielIntegration\Integration\Admin\IntegrationService;
    use CielIntegration\WithRouteUrl;

	/**
	 * @property \DB $db
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

			return $this->_getLocalProductModel()
				->productExits($productId);
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

			return $this->_getLocalProductModel()
				->lookupProductId($sku);
		}

		public function lookupProductSku($productId) {
			if (empty($productId)) {
				return 0;
			}

			return $this->_getLocalProductModel()
				->lookupProductSku($productId);
		}

		public function getAllProducts() {
			return $this->_getLocalProductModel()
				->getProducts(array(
					'filter_status' => 1
				));
		}

		public function getProduct($productId) {
			if (empty($productId)) {
				return null;
			}

			return $this->_getLocalProductModel()
				->getProduct($productId);
		}

		/**
		 * @return LocalProduct
		 */
		private function _getLocalProductModel() {
			return new LocalProduct($this->registry);
		}

		/**
		 * @return \DB
		 */
		protected function _getDb() {
			return $this->db;
		}
	}
}