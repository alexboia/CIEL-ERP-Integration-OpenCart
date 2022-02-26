<?php
namespace CielIntegration\Integration\Admin\Article {

    use Ciel\Api\Integration\Binding\CielErpToStoreBinding;
    use CielIntegration\Integration\Admin\Article\Model\RemoteArticle;
    use CielIntegration\Integration\Admin\Binding\OpenCartCielWorkflow;
    use \ModelCatalogProduct;

	class RemoteArticleToLocalProductMarshaller {
		private $_productId;

		/**
		 * @var CielErpToStoreBinding
		 */
		private $_storeBinding;

		/**
		 * @var OpenCartCielWorkflow
		 */
		private $_workflow;

		/**
		 * @var ModelCatalogProduct
		 */
		private $_productModel;

		/**
		 * @var RemoteArticle
		 */
		private $_remoteArticleModel;

		public function __construct($productId, 
			ModelCatalogProduct $productModel, 
			RemoteArticle $remoteArticleModel, 
			CielErpToStoreBinding $storeBinding, 
			OpenCartCielWorkflow $workflow) {

			if (empty($productId)) {
				
			}

			$this->_productId = $productId;
			$this->_productModel = $productModel;
			$this->_remoteArticleModel = $remoteArticleModel;
			$this->_storeBinding = $storeBinding;
			$this->_workflow = $workflow;
		}
	}
}