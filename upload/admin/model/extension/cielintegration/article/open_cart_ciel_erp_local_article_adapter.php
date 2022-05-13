<?php
namespace CielIntegration\Integration\Admin\Article {

    use Ciel\Api\Data\StockUpdateResult;
    use Ciel\Api\Exception\ArticleCodeAlreadyExistsException;
    use Ciel\Api\Exception\LocalArticleNotFoundException;
    use Ciel\Api\Integration\Articles\Providers\CielErpLocalArticleAdapter;
    use CielIntegration\Integration\Admin\IntegrationService;
    use InvalidArgumentException;

	class OpenCartCielErpLocalArticleAdapter 
		extends IntegrationService 
		implements CielErpLocalArticleAdapter {

		/**
		 * @var RemoteArticleToLocalProductMarshallerFactory
		 */
		private $_productMarshallerFactory;

		/**
		 * @var ProductResolver
		 */
		private $_productResolver;

		public function __construct(\Registry $registry) {
			parent::__construct($registry);
			$this->_productMarshallerFactory = 
				new RemoteArticleToLocalProductMarshallerFactory($registry);
			$this->_productResolver = 
				new ProductResolver($registry);
		}

		public function createArticleFromRemoteData($remoteArticleData) { 
			if (!is_array($remoteArticleData) || empty($remoteArticleData)) {
				throw new InvalidArgumentException('Remote article data must be a valid, non-empty, array');
			}

			$sku = $this->_getRemoteCode($remoteArticleData);
			if ($this->_productSkuExists($sku)) {
				throw new ArticleCodeAlreadyExistsException($sku);
			}

			return $this->_createProductFromRemoteData($remoteArticleData);
		}

		private function _getRemoteCode($remoteArticleData) {
			return $remoteArticleData['Code'];
		}

		private function _productSkuExists($sku) {
			return $this->_productResolver
				->productSkuExists($sku);
		}

		private function _createProductFromRemoteData($remoteArticleData) {
			return  $this->_createNewProductMarshaller()
				->createFromRemoteData($remoteArticleData);
		}

		private function _createNewProductMarshaller() {
			return $this->_productMarshallerFactory
				->createForNewProduct();
		}

		public function connectArticleAndUpdateWithRemoteData($localId, $remoteArticleData) { 
			if (empty($localId)) {
				throw new InvalidArgumentException('Local Id must not be empty');
			}

			if (!$this->_productExits($localId)) {
				throw new LocalArticleNotFoundException('id', $localId);
			}

			$this->_updateProductFromRemoteData($localId, 
				$remoteArticleData);
		}

		private function _productExits($productId) {
			return $this->_productResolver
				->productExits($productId);
		}

		private function _updateProductFromRemoteData($productId, array $remoteArticleData) {
			$this->_createProductMarshaller($productId)
				->updateFromRemoteData($remoteArticleData);
		}

		private function _createProductMarshaller($productId) {
			return $this->_productMarshallerFactory
				->createForProduct($productId);
		}

		public function disconnectArticle($localId) { 
			if (empty($localId)) {
				throw new InvalidArgumentException('Local Id must not be empty');
			}

			if (!$this->_productExits($localId)) {
				throw new LocalArticleNotFoundException('id', $localId);
			}

			$this->_clearProductBindingInformation($localId);
		}

		private function _clearProductBindingInformation($productId) {
			$this->_createProductMarshaller($productId)
				->clearBindingInformation();
		}

		public function isArticleConnected($localId) { 
			if (empty($localId)) {
				throw new InvalidArgumentException('Local Id must not be empty');
			}

			if ($this->_productExits($localId)) {
				return $this->_isConnectedToCielErp($localId);
			} else {
				return false;
			}
		}

		private function _isConnectedToCielErp($localId) {
			return $this->_productResolver
				->isConnectedToCielErp($localId);
		}

		public function updateArticleFromRemoteData($localId, $remoteArticleData) { 
			if (empty($localId)) {
				throw new InvalidArgumentException('Local Id must not be empty');
			}

			if (!$this->_productExits($localId)) {
				throw new LocalArticleNotFoundException('id', $localId);
			}

			$this->_updateProductFromRemoteData($localId, 
				$remoteArticleData);
		}

		public function updateStocksForConnectedArticle($localId, $remoteArticleStockData) { 
			if (empty($localId)) {
				throw new InvalidArgumentException('Local Id must not be empty');
			}

			if (!$this->_productExits($localId)) {
				throw new LocalArticleNotFoundException('id', $localId);
			}

			if (!$this->_shopStockManagementEnabled()) {
				return StockUpdateResult::failedShopStockManagementNotEnabled();
			}

			if (!$this->_hasStockQuantityInfo($remoteArticleStockData)) {
				return StockUpdateResult::failedNoSourceStockData();
			}

			if (!$this->_isConnectedToCielErp($localId)) {
				return StockUpdateResult::failedProductNotConnected();
			}

			$this->_updateProductStockQuantityFromRemoteStockData($localId, 
				$remoteArticleStockData);

			return StockUpdateResult::successful();
		}

		private function _shopStockManagementEnabled() {
			return true;
		}

		private function _hasStockQuantityInfo($remoteArticleStockData) {
			return !empty($remoteArticleStockData) 
				&& isset($remoteArticleStockData['StockQuantity']) 
				&& $remoteArticleStockData['StockQuantity'] !== null
				&& is_numeric($remoteArticleStockData['StockQuantity']);
		}

		private function _updateProductStockQuantityFromRemoteStockData($productId, array $remoteArticleStockData) {
			$this->_createProductMarshaller($productId)
				->updateProductStockQuantityFromRemoteStockData($remoteArticleStockData);
		}

		public function updateStocksForAllConnectedArticles($remoteArticlesStockData) { 
			$eligibleProductCount = 0;
			$updatedProductCount = 0;

			if (!is_array($remoteArticlesStockData)) {
				throw new InvalidArgumentException('Remote articles stock data must be a valid array');
			}

			if ($this->_shopStockManagementEnabled()) {
				if (!empty($remoteArticlesStockData)) {
					$productIds = $this->_getAllConnectedLocalProductIdsBySkus();
					foreach ($productIds as $code => $pId) {
						$articleStockData = isset($remoteArticlesStockData[$code]) 
							? $remoteArticlesStockData[$code] 
							: null;

						$eligibleProductCount ++;
						if ($this->_hasStockQuantityInfo($articleStockData)) {
							$this->_updateProductStockQuantityFromRemoteStockData($pId, 
								$articleStockData);
							$updatedProductCount ++;
						}
					}
				}
			}

			return array(
				'eligible' => $eligibleProductCount,
				'updated' => $updatedProductCount
			);
		}

		private function _getAllConnectedLocalProductIdsBySkus() {
			return $this->_productResolver
				->getAllConnectedLocalProductIdsBySkus();
		}

		public function updateAllConnectedArticlesFromRemoteData($remoteArticlesData) { 
			$eligibleProductCount = 0;
			$updatedProductCount = 0;

			if (!is_array($remoteArticlesData)) {
				throw new InvalidArgumentException('Remote articles data must be a valid array');
			}

			if (!empty($remoteArticlesData)) {
				$productIds = $this->_getAllConnectedLocalProductIdsBySkus();
				foreach ($productIds as $code => $pId) {
					$articleData = isset($remoteArticlesData[$code]) 
						? $remoteArticlesData[$code] 
						: null;

					$eligibleProductCount ++;
					if (!empty($articleData)) {
						$this->_updateProductFromRemoteData($pId, 
							$articleData);
						$updatedProductCount ++;
					}
				}
			}

			return array(
				'eligible' => $eligibleProductCount,
				'updated' => $updatedProductCount
			);
		}

		public function lookupLocalArticleCode($localId) { 
			if (empty($localId)) {
				throw new InvalidArgumentException('Local Id must not be empty');
			}

			if ($this->_productExits($localId)) {
				$sku = $this->_getProductSku($localId);
				$result = array(
					$localId => $sku
				);
			} else {
				$result = array();
			}

			return $result;
		}

		private function _getProductSku($productId) {
			return $this->_productResolver
				->lookupProductSku($productId);
		}

		public function getBatchTrackingStatusForSingleArticle($localId) { 
			if (empty($localId)) {
				throw new InvalidArgumentException('Local Id must not be empty');
			}

			return $this->_getProductBatchTrackingStatus($localId);
		}

		private function _getProductBatchTrackingStatus($productId) {
			return $this->_productResolver
				->getBatchTrackingStatus($productId);
		}

		public function disconnectAllArticles() { 
			$remoteArticleModel = $this->_getRemoteArticleModel();
			$remoteArticleModel->removeAll();
		}

		public function getAllLocalArticles() { 
			$result = array();
			$products = $this->_getAllProducts();

			foreach ($products as $p) {
				$productData = $this->_getProductData($p);
				$result[] = $productData;
			}

			return $result;
		}

		private function _getAllProducts() {
			return $this->_productResolver
				->getAllProducts();
		}

		private function _getProductData($product) {
			$productId = intval($product['product_id']);
			$data = array(
				'id' => $productId,
				'code' => $product['sku'],
				'type' => 'simple',
				'name' => $product['name'],
				'description' => trim($product['description']),
				'manageStock' => true,
				'permalink' => $this->_getProductEditUrl($productId),
				'remoteId' => $this->_getRemoteArticleId($productId)
			);
			
			return $data;
		}

		private function _getRemoteArticleId($productId) {
			return $this->_productResolver
				->lookupRemoteArticleId($productId);
		}

		private function _getProductEditUrl($producId) {
			return $this->_productResolver
				->getProductEditUrl($producId);
		}

		public function getAllLocalArticlesForExport() { 
			return $this->getAllLocalArticles();
		}
	}
}