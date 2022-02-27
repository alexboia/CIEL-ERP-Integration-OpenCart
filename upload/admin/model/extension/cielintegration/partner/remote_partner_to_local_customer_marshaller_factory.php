<?php
namespace CielIntegration\Integration\Admin\Partner {

    use CielIntegration\Integration\Admin\IntegrationService;

	class RemotePartnerToLocalCustomerMarshallerFactory extends IntegrationService {	
		public function createForCustomer($customerId) {
			return new RemotePartnerToLocalCustomerMarshaller($customerId, 
				$this->registry);
		}
	}
}