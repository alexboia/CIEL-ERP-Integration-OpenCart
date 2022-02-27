<?php
namespace CielIntegration\Integration\Admin\Article {

    use Ciel\Api\CielConfig;
    use CielIntegration\Integration\Admin\IntegrationService;
    use InvalidArgumentException;

	class RemoteArticleToLocalProductMarshaller extends IntegrationService {
		/**
		 * @var ProductUpdateService
		 */
		private $_productUpdateService;

		/**
		 * @var CielConfig
		 */
		private $_config;

		/**
		 * @var int
		 */
		private $_productId;

		public function __construct($productId, \Registry $registry) {
			parent::__construct($registry);

			if (empty($productId) && $productId !== 0) {
				throw new InvalidArgumentException('Product id may not be empty.');
			}

			$this->_productId = $productId;
			$this->_config = $this->_getConfig();
			$this->_productUpdateService = 
				new ProductUpdateService($productId, 
					$registry);
		}

		public function createFromRemoteData(array $remoteArticleData) {
			if (empty($remoteArticleData)) {
				throw new InvalidArgumentException('Remote article data may not be empty.');
			}

			$productId = $this->_productUpdateService
				->createNewProduct($remoteArticleData);
			$this->_productId = 
				$productId;

			return $productId;
		}

		public function updateFromRemoteData(array $remoteArticleData) {
			if (empty($remoteArticleData)) {
				throw new InvalidArgumentException('Remote article data may not be empty.');
			}

			$this->_setProductBindingInformation($remoteArticleData);
			if ($this->_isTaxRatesUpdateEnabled()) {
				$this->_setProductTaxInformation($remoteArticleData);
			}

			if ($this->_isPriceUpdateEnabled()) {
				$this->_setProductPriceInformation($remoteArticleData);
			}

			if ($this->_isStockUpdateEnabled()) {
				$this->_setProductStockInformation($remoteArticleData);
			}
		}

		private function _setProductBindingInformation($remoteArticleData) {
			$this->_productUpdateService
				->setProductBindingInformation($remoteArticleData);
		}

		private function _isPriceUpdateEnabled() {
			return $this->_config->isPriceUpdateEnabled();
		}

		private function _setProductPriceInformation($remoteArticleData) {
			$this->_productUpdateService
				->setProductPriceInformation($remoteArticleData);
		}

		private function _isTaxRatesUpdateEnabled() {
			return $this->_config->isTaxRatesUpdateEnabled();
		}

		private function _setProductTaxInformation($remoteArticleData) {
			$this->_productUpdateService
				->setProductTaxInformation($remoteArticleData);
		}

		private function _isStockUpdateEnabled() {
			return $this->_config->isStockUpdateEnabled();
		}

		private function _setProductStockInformation($remoteArticleData) {
			$this->_productUpdateService
				->setProductStockInformation($remoteArticleData);
		}

		public function updateProductStockQuantityFromRemoteStockData($remoteArticleStockData) {
			if (empty($remoteArticleStockData)) {
				throw new InvalidArgumentException('Remote article data may not be empty.');
			}

			if ($this->_isStockUpdateEnabled()) {
				$this->_setProductStockInformationFromStockData($remoteArticleStockData);
			}
		}

		private function _setProductStockInformationFromStockData($remoteArticleStockData) {
			$this->_productUpdateService
				->setProductStockInformationFromStockData($remoteArticleStockData);
		}

		public function clearBindingInformation() {
			$this->_productUpdateService
				->clearProductBindingInformation();	
		}

		public function getProductId() {
			return $this->_productId;
		}
	}
}