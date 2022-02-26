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

		protected function _getTableName() {
			return self::BASE_TABLE_NAME;
		}

		protected function _getIdColumnKey() {
			return self::ID_COLUMN_KEY;
		}
	}
}