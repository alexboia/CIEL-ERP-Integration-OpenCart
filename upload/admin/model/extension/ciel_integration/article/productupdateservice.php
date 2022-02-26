<?php
namespace CielIntegration\Integration\Admin\Article {
    use CielIntegration\Integration\Admin\IntegrationService;

	class ProductUpdateService extends IntegrationService {
		private $_workflow;

		public function __construct(\Registry $registry) {
			parent::__construct($registry);
		}

		public function setProductTaxInformation($remoteArticleData) {

		}

		public function setProductPriceInformation($remoteArticleData) {

		}

		public function setProductStockInformation($remoteArticleData) {

		}
	}
} 