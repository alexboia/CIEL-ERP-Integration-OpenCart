<?php
namespace CielIntegration\Integration\Admin\Article\Model {
    use CielIntegration\Integration\Admin\IntegrationModel;

	class RemoteArticle extends IntegrationModel {
		const BASE_TABLE_NAME = 'mycciel_oc_remote_product';

		const ID_COLUMN_KEY = 'product_id';

		public function add(array $remoteArticleInfo) {
			return $this->_add($remoteArticleInfo);
		}

		public function update(array $remoteArticleInfo) {
			return $this->_update($remoteArticleInfo);
		}

		public function addAll(array $remoteArticlesInfos) {
			if (empty($remoteArticlesInfos)) {
				return;
			}

			return $this->_addAll($remoteArticlesInfos);
		}

		public function removeByProductId($productId) {
			return $this->_removeByModelId($productId);
		}

		public function removeAll() {
			return $this->_removeAll();
		}

		public function getByProductId($productId) {
			return $this->_getOneByModelId($productId);
		}

		public function getByProductIds(array $productIds) {
			if (empty($productIds)) {
				return array();
			}

			return $this->_getAllByModelIds($productIds);
		}

		public function existsForProductId($productId) {
			$record = $this->_getOneByModelId($productId);
			return !empty($record);
		}

		public function isConnectedToCielErp($productId) {
			$record = $this->_getOneByModelId($productId);
			return !empty($record)
				&& !empty($record['remote_id']);
		}

		public function areConnectedToCielErp(array $productIds) {
			$status = array_fill_keys($productIds, false);
			$records = $this->_getAllByModelIds($productIds);

			foreach ($records as $r) {
				$productId = $r['product_id'];
				$status[$productId] = !empty($r['remote_id']);
			}

			return $status;
		}

		public function getAllProductIdsBySkus() {
			$db = $this->_getDb();
			$query = 'SELECT p.product_id, p.sku
				FROM `' . $this->_getFullTableName() . '` rp
					LEFT JOIN `' . DB_PREFIX . 'product` p ON p.product_id = rp.mycciel_oc_product_id';
			
			$productIds = array();
			$result = $db->query($query);
			if (!empty($result) && !empty($result->rows)) {
				foreach ($result->rows as $row) {
					$productIds[$row['sku']] = intval($row['product_id']);
				}
			}

			return $productIds;
		}

		public function getBatchStrackingStatusByProductId($productId) {
			$record = $this->getByProductId($productId);
			return !empty($record) 
				&& !empty($record['remote_batch_tracking_enabled']) 
				&& intval($record['remote_batch_tracking_enabled']) == 1;
		}

		public function getRemoteArticleId($productId) {
			$record = $this->getByProductId($productId);
			return !empty($record) 
				&& !empty($record['remote_id']) 
					? intval($record['remote_id']) 
					: 0;
		}

		public function getVatOutOptionName($productId) {
			$record = $this->getByProductId($productId);
			return !empty($record) 
				&& !empty($record['remote_price_vat_option_name']) 
					? $record['remote_price_vat_option_name'] 
					: null;
		}

		public function getVatOutQuotaValue($productId) {
			$record = $this->getByProductId($productId);
			return !empty($record) 
				&& !empty($record['remote_price_vat_quota_value']) 
					? floatval($record['remote_price_vat_quota_value']) 
					: 0;
		}

		protected function _getFullTableName() {
			return DB_PREFIX . $this->_getTableName();
		}

		protected function _getTableName() {
			return self::BASE_TABLE_NAME;
		}

		protected function _getIdColumnKey() {
			return self::ID_COLUMN_KEY;
		}
	}
}