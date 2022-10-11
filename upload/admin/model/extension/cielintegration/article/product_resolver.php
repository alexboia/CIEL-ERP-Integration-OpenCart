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

		public function getRemoteVatOutOptionName($productId) {
			if (empty($productId)) {
				return null;
			}

			return $this->_getRemoteArticleModel()
				->getVatOutOptionName($productId);
		}

		public function getRemoteVatOutQuotaValue($productId) {
			if (empty($productId)) {
				return 0;
			}

			return $this->_getRemoteArticleModel()
				->getVatOutQuotaValue($productId);
		}

		public function getRemoteBatchTrackingStatus($productId) {
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
				return null;
			}

			return $this->_getLocalProductModel()
				->lookupProductSku($productId);
		}

		public function productHasSku($productId) {
			return !empty($this->lookupProductSku($productId));
		}

		public function getAllProducts() {
			return $this->_getLocalProductModel()
				->getProducts();
		}

		public function getProduct($productId) {
			if (empty($productId)) {
				return null;
			}

			return $this->_getLocalProductModel()
				->getProduct($productId);
		}

		public function getHighestPriorityTaxRate($productId) {
			if (empty($productId)) {
				return null;
			}

			$taxRateInfo = $this->_getLocalProductModel()
				->getHighestPriorityTaxRateInfo($productId);

			if (!empty($taxRateInfo)) {
				return floatval($taxRateInfo['product_main_tax_rate']);
			} else {
				return 0;
			}
		}

		public function getCategoryNames($productId) {
			if (empty($productId)) {
				return null;
			}

			$categories = $this->_getLocalProductModel()
				->getCategories($productId);

			$categoryNames = array();
			if (!empty($categories)) {
				foreach ($categories as $c) {
					$categoryNames[] = $c['category_name'];
				}
			}

			return $categoryNames;
		}

		public function getLocalProductsInformation(array $productIds) {
			$productIds = array_map('intval', 
				$productIds);
			$productIds = array_filter($productIds, function($productId) {
				return $productId > 0;
			});

			if (empty($productIds)) {
				return array();
			}

			$rawProductsInfos = $this->_getLocalProductModel()
				->getProductsInformation($productIds);

			$productsInfos = array();
			foreach ($rawProductsInfos as $rp) {
				$productId = $rp['product_id'];
				$productsInfos[$productId] = array_merge($rp, 
					array(
						'product_url' => $this->getProductEditUrl($productId)
					)
				);
			}

			foreach ($productIds as $pId) {
				if (!isset($productsInfos[$pId])) {
					$productsInfos[$pId] = array(
						'product_id' => $pId,
						'product_name' => null,
						'product_model' => null,
						'product_url' => null,
						'product_sku' => null
					);
				}
			}

			return $productsInfos;
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