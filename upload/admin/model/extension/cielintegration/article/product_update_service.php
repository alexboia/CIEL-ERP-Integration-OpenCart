<?php
namespace CielIntegration\Integration\Admin\Article {

    use Ciel\Api\Exception\ArticleCodeAlreadyExistsException;
    use CielIntegration\Integration\Admin\IntegrationService;
    use CielIntegration\Integration\Admin\PriceFormatter;
    use InvalidArgumentException;
    use ModelCatalogProduct;
    use Registry;

	/**
	 * @property \Loader $load
	 * @property \Config $config
	 */
	class ProductUpdateService extends IntegrationService {
		/**
		 * @var int
		 */
		private $_productId;

		/**
		 * @var array|null
		 */
		private $_productData;

		/**
		 * @var TaxService
		 */
		private $_taxService;

		/**
		 * @var PriceFormatter
		 */
		private $_priceFormatter;

		/**
		 * @var ManufacturerService
		 */
		private $_manufacturerService;

		/**
		 * @param int $productId 
		 * @param Registry $registry 
		 */
		public function __construct($productId, \Registry $registry) {
			parent::__construct($registry);

			if (empty($productId) && $productId !== 0) {
				throw new InvalidArgumentException('Product id may not be empty.');
			}

			$this->_productId = $productId;
			$this->_taxService = new TaxService($registry);
			$this->_manufacturerService = new ManufacturerService($registry);
			$this->_priceFormatter = new PriceFormatter($registry);
		}

		public function createNewProduct(array $remoteArticleData) {
			$initialProductData = $this->_buildInitialProductData($remoteArticleData);
			
			$productId = $this->_addProduct($initialProductData);
			$this->_productId = $productId;

			if ($this->_productId > 0) {
				$this->setProductBindingInformation($remoteArticleData);
			}

			return $productId;
		}

		private function _buildInitialProductData($remoteArticleData) {
			$nowDate = $this->_getNowAsDate();
			$sku = $this->_getRemoteCode($remoteArticleData);
			$manufacturerId = $this->_getOrCreateDefaultManufacturerId();
			
			$stockQuantity = $this->_getProductStockQuantity($remoteArticleData);
			$stockStatusId = $this->_determineNewStockStatusId($stockQuantity);
			
			$articlePrices = $this->_getArticlePrices($remoteArticleData);
			$price = $this->_determinePriceForUpdate($articlePrices);

			$taxClassId = $this->_determineAndEnsureTaxClassIdForUpdate($remoteArticleData);

			$languageId = $this->_getLanguageId();
			$name = $this->_getRemoteArticleName($remoteArticleData);

			return array(
				'model' => '',
				'sku' => $sku,
				'upc' => '',
				'ean' => '',
				'jan' => '',
				'isbn' => '',
				'mpn' => '',
				'location' => '',
				'quantity' => $stockQuantity,
				'stock_status_id' => $stockStatusId,
				'image' => null,
				'manufacturer_id' => $manufacturerId,
				'shipping' => 1,
				'price' => $price,
				'points' => 0,
				'tax_class_id' => $taxClassId,
				'date_available' => $nowDate,
				'weight' => 0,
				'weight_class_id' => $this->_getNewProductWeightClassId(),
				'length' => 0,
				'width' => 0,
				'height' => 0,
				'length_class_id' => $this->_getNewProductLengthClassId(),
				'subtract' => 1,
				'minimum' => 1,
				'status' => 0,
				'viewed' => 0,
				'keyword' => null,
				'sort_order' => 0,
				'date_added' => $nowDate,
				'date_modified' => $nowDate,
				'product_description' => array(
					$languageId => array(
						'name' => $name,
						'description' => '',
						'tag' => '',
						'meta_title' => $name,
						'meta_description' => '',
						'meta_keyword' => ''
					)
				),
				'product_store' => array(
					0
				)
			);
		}

		private function  _getOrCreateDefaultManufacturerId() {
			return $this->_manufacturerService
				->getOrCreateDefaultManufacturer();
		}

		private function _getNowAsDate() {
			return date('Y-m-d');
		}

		private function _getNewProductWeightClassId() {
			return $this->_getWorkflow()
				->getNewProductWeightClassId();
		}

		private function _getNewProductLengthClassId() {
			return $this->_getWorkflow()
				->getNewProductLengthClassId();
		}

		private function _getLanguageId() {
			return $this->config->get('config_language_id');
		}

		private function _getRemoteArticleName($remoteArticleData) {
			return $remoteArticleData['Name'];
		}

		private function _addProduct($productData) {
			return $this->_getCatalogProductModel()
				->addProduct($productData);
		}

		public function setProductBindingInformation(array $remoteArticleData) {
			$productId = $this->_productId;
			$remoteId = $this->_getRemoteId($remoteArticleData);
			$measuringUnitName = $this->_getMeasurementUnitName($remoteArticleData);
			$vatOutQuotaValue = $this->_getVatOutQuotaValue($remoteArticleData);
			$vatOutOptionName = $this->_getVatOutOptionName($remoteArticleData);
			$batchTrackingEnabled = $this->_getBatchTrackingEnabled($remoteArticleData);

			$saveRemoteArticleData = array(
				'product_id' => $productId,
				'remote_id' => $remoteId,
				'remote_measurement_unit' => $measuringUnitName,
				'remote_price_vat_quota_value' => $vatOutQuotaValue,
				'remote_price_vat_option_name' => $vatOutOptionName,
				'remote_batch_tracking_enabled' => $batchTrackingEnabled 
					? 1 
					: 0
			);

			$remoteArticleModel = $this->_getRemoteArticleModel();
			if (empty($productId) || !$remoteArticleModel->existsForProductId($productId)) {
				$remoteArticleModel->add($saveRemoteArticleData);
			} else {
				$remoteArticleModel->update($saveRemoteArticleData);
			}

			$this->_updateProduct(array(
				'sku' => $this->_getRemoteCode($remoteArticleData)
			));
		}

		private function _getRemoteId($remoteArticleData) {
			return intval($remoteArticleData['Id']);
		}
		
		private function _getRemoteCode($remoteArticleData) {
			return $remoteArticleData['Code'];
		}

		private function _getMeasurementUnitName($remoteArticleData) {
			return isset($remoteArticleData['MeasuringUnitName']) 
				? $remoteArticleData['MeasuringUnitName'] 
				: null;
		}

		private function _getVatOutQuotaValue($remoteArticleData) {
			return isset($remoteArticleData['VatOutQuotaValue']) 
				? floatval($remoteArticleData['VatOutQuotaValue']) 
				: 0;
		}

		private function _getVatOutOptionName($remoteArticleData) {
			return isset($remoteArticleData['VatOutOptionName']) 
				? $remoteArticleData['VatOutOptionName'] 
				: 0;
		}

		private function _getBatchTrackingEnabled($remoteArticleData) {
			return isset($remoteArticleData['BatchTrackingEnabled']) 
				? $remoteArticleData['BatchTrackingEnabled'] == true
				: false;
		}

		private function _updateProduct($modifiedProductData) {
			$db = $this->_getDb();
			$cache = $this->_getCache();

			$query = $this->_buildProductUpdateQuery($modifiedProductData);
			$cache->delete('product');

			$db->query($query);
		}

		private function _buildProductUpdateQuery($modifiedProductData) {
			$db = $this->_getDb();
			$updateColums = array();

			foreach ($modifiedProductData as $key => $value) {
				$updateColums[] = sprintf('`%s` = "%s"', 
					$key, 
					$db->escape($value));
			}

			$updateColums = join(', ', 
				$updateColums);

			$query = 'UPDATE `' . DB_PREFIX .'product` 
				SET ' . $updateColums . ' 
				WHERE product_id =' 
					. intval($this->_productId);

			return $query;
		}

		public function setProductTaxInformation(array $remoteArticleData) {
			$this->_updateProduct(array(
				'tax_class_id' => $this->_determineAndEnsureTaxClassIdForUpdate($remoteArticleData)
			));
		}

		private function _determineAndEnsureTaxClassIdForUpdate(array $remoteArticleData) {
			$vatQuotaValue = $this->_getVatOutQuotaValue($remoteArticleData);
			$vatQuotaName = $this->_getVatOutQuotaName($remoteArticleData);
			$vatOptionName = $this->_getVatOutOptionName($remoteArticleData);

			if (empty($vatQuotaName)) {
				$vatQuotaName = $this->_deriveVatQuotaNameFromQuotaValue($vatQuotaValue);
			}

			$taxClass = $this->_getOrCreateTaxClass($vatOptionName, 
				$vatQuotaName, 
				$vatQuotaValue);

			return $taxClass['tax_class_id'];
		}

		private function _getVatOutQuotaName($remoteArticleData) {
			return isset($remoteArticleData['VatOutQuotaName'])
				? $remoteArticleData['VatOutQuotaName']
				: '';
		}

		private function _deriveVatQuotaNameFromQuotaValue($vatQuotaValue) {
			return $this->_taxService->deriveVatQuotaNameFromQuotaValue($vatQuotaValue);
		}

		private function _getOrCreateTaxClass($vatOptionName, $vatQuotaName, $vatQuotaValue) {
			return $this->_taxService->getOrCreateTaxClass($vatOptionName, 
				$vatQuotaName, 
				$vatQuotaValue);
		}

		public function clearProductBindingInformation() {
			$remoteArticleModel = $this->_getRemoteArticleModel();
			$remoteArticleModel->removeByProductId($this->_productId);
		}

		public function setProductPriceInformation(array $remoteArticleData) {
			$prices = $this->_getArticlePrices($remoteArticleData);
			$this->_updateProduct(array(
				'price' => $this->_determinePriceForUpdate($prices)
			));
		}

		private function _getArticlePrices($remoteArticleData) {
			$vatQuotaValue = $this->_getVatOutQuotaValue($remoteArticleData);
			$whPrices = $this->_getProductWarehousePrices($remoteArticleData);
			
			$priceOut = 0;
			$priceOutWithVat = 0;

			if (!empty($whPrices)) {
				$priceOut = $this->_getWarehousePriceOut($whPrices);
				$priceOutWithVat = $this->_getWarehousePriceOutWithVat($whPrices);
			}

			if ($priceOut == 0 && $priceOutWithVat == 0) {
				if ($this->_isBoundToEnGrossWarehouse()) {
					//If the current warehouse is an en-gross warehouse,
					//  the generic en-gross sale price will be extracted.
					//The generic en-gross sale price NEVER includes VAT
					$priceOut = $this->_getGenericSalePriceEnGross($remoteArticleData);
					$priceOutWithVat = 0;
				} else {
					//If the current warehouse is an en-detail warehouse,
					//  the generic en-detail sale price will be extracted;    
					//The generic en-detail sale price ALWAYS includes VAT
					$priceOutWithVat = $this->_getGenericSalePriceEnDetail($remoteArticleData);
					$priceOut = 0;
				}
			}

			if ($priceOut == 0) {
				$priceOut = $this->_calculatePriceOutWithoutVat($priceOutWithVat, $vatQuotaValue);
			} else if ($priceOutWithVat == 0) {
				$priceOutWithVat = $this->_calculatePriceOutWithVat($priceOut, $vatQuotaValue);
			}

			return array(
				'priceOut' => $this->_priceFormatter
					->roundPrice($priceOut),
				'priceOutWithVat' => $this->_priceFormatter
					->roundPrice($priceOutWithVat)
			);
		}

		private function _determinePriceForUpdate(array $prices) {
			return $this->_productPricesIncludeTaxes() 
				? $prices['priceOutWithVat'] 
				: $prices['priceOut'];
		}

		private function _isBoundToEnGrossWarehouse() {
			return $this->_getStoreBinding()
				->isBoundToEnGrossWarehouse();
		}

		private function _getWarehousePriceOut($whPrices) {
			return !empty($whPrices['PriceOut']) 
				? $whPrices['PriceOut'] 
				: 0;
		}

		private function _getWarehousePriceOutWithVat($whPrices) {
			return !empty($whPrices['PriceOutWithVAT']) 
				? $whPrices['PriceOutWithVAT'] 
				: 0;
		}

		private function _getGenericSalePriceEnGross($remoteArticleData) {
			return isset($remoteArticleData['GenericSalePriceEnGross']) 
				? $remoteArticleData['GenericSalePriceEnGross'] 
				: 0;
		}

		private function _getGenericSalePriceEnDetail($remoteArticleData) {
			return isset($remoteArticleData['GenericSalePriceEnDetail']) 
				? $remoteArticleData['GenericSalePriceEnDetail'] 
				: 0;
		}

		private function _getProductWarehousePrices($remoteArticleData) {
			return !empty($remoteArticleData['ArticlesWarehousesPrices']) 
					&& count($remoteArticleData['ArticlesWarehousesPrices']) > 0
				? $remoteArticleData['ArticlesWarehousesPrices'][0]
				: null;
		}

		private function _calculatePriceOutWithoutVat($priceOutWithVat, $vatQuotaValue) {
			return $this->_priceFormatter
				->calculatePriceWithoutVat($priceOutWithVat, 
					$vatQuotaValue);
		}

		private function _calculatePriceOutWithVat($priceOutWithoutVat, $vatQuotaValue) {
			return $this->_priceFormatter
				->calculatePriceWithVat($priceOutWithoutVat, 
					$vatQuotaValue);
		}

		private function _productPricesIncludeTaxes() {
			return $this->_taxService
				->productPricesIncludeTaxes();
		}

		public function setProductStockInformation(array $remoteArticleData) {
			if ($this->_shopStockManagementEnabled()) {
				$remoteArticleStockData = $this->_getRemoteArticleStockData($remoteArticleData);
				$this->setProductStockInformationFromStockData($remoteArticleStockData);
			}
		}

		private function _getProductStockQuantity($remoteArticleData) {
			$remoteArticleStockData = $this->_getRemoteArticleStockData($remoteArticleData);
			return $this->_getProductStockQuantityFromStockData($remoteArticleStockData);
		}

		private function _getRemoteArticleStockData($remoteArticleData) {
			$remoteArticleStockData = !empty($remoteArticleData['ArticlesWarehouseStocks']) 
					&& !empty($remoteArticleData['ArticlesWarehouseStocks'][0])
				? $remoteArticleData['ArticlesWarehouseStocks'][0]
				: array();

			return $remoteArticleStockData;
		}

		public function setProductStockInformationFromStockData(array $remoteArticleStockData) {
			if ($this->_shopStockManagementEnabled()) {
				$stockQuantity = $this->_getProductStockQuantityFromStockData($remoteArticleStockData);
				if (!is_null($stockQuantity)) {
					$newStockStatusId = $this->_determineNewStockStatusId($stockQuantity);
					$this->_updateProduct(array(
						'quantity' => $stockQuantity,
						'stock_status_id' => $newStockStatusId
					));
				}
			}
		}

		private function _getProductStockQuantityFromStockData(array $remoteArticleStockData) {
			if (!empty($remoteArticleStockData)) {
				return isset($remoteArticleStockData['StockQuantity']) 
					? $remoteArticleStockData['StockQuantity'] 
					: null;
			} else {
				return null;
			}
		}

		private function _determineNewStockStatusId($stockQuantity) {
			if ($stockQuantity > 0) {
				$newStockStatusId = $this->_getInStockStatusId();
			} else {
				$newStockStatusId = $this->_getOutOfStockStatusId();
			}

			return $newStockStatusId;
		}

		private function _getInStockStatusId() {
			return $this->_getWorkflow()
				->getInStockStatusId();
		}

		private function _getOutOfStockStatusId() {
			return $this->_getWorkflow()
				->getOutOfStockStatusId();
		}

		/**
		 * @return ModelCatalogProduct
		 */
		private function _getCatalogProductModel() {
			$this->load->model('catalog/product');
			return $this->model_catalog_product;
		}

		private function _shopStockManagementEnabled() {
			return true;
		}

		/**
		 * @return \DB
		 */
		private function _getDb() {
			return $this->db;
		}

		/**
		 * @return \Cache
		 */
		private function _getCache() {
			return $this->cache;
		}
	}
} 