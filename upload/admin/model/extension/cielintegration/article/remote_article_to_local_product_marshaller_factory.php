<?php
namespace CielIntegration\Integration\Admin\Article {

    use CielIntegration\Integration\Admin\IntegrationService;

	class RemoteArticleToLocalProductMarshallerFactory extends IntegrationService {
		public function createForProduct($productId) {
			return new RemoteArticleToLocalProductMarshaller($productId, 
				$this->registry);
		}

		public function createForNewProduct() {
			return $this->createForProduct(0);
		}
	}
}