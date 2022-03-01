<?php
namespace CielIntegration\Integration\Admin\Order {

    use CielIntegration\Integration\Admin\IntegrationService;

	class LocalOrderToRemoteDocumentMarshallerFactory extends IntegrationService {
		public function createForOrder($orderId) {
			return new LocalOrderToRemoteDocumentMarshaller($orderId, 
				$this->registry);
		}
	}
}