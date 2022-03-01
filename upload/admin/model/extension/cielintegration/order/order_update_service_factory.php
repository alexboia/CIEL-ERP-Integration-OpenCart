<?php
namespace CielIntegration\Integration\Admin\Order {

    use CielIntegration\Integration\Admin\IntegrationService;
    use InvalidArgumentException;

	class OrderUpdateServiceFactory extends IntegrationService {
		public function createForOrder($orderId) {
			if (empty($orderId)) {
				throw new InvalidArgumentException('Order id may not be empty.');
			}

			return new OrderUpdateService($orderId, 
				$this->registry);
		}
	}
}