<?php
namespace CielIntegration\Integration\Admin\Partner {

    use Ciel\Api\Exception\LocalOrderNotFoundException;
    use Ciel\Api\Exception\LocalPartnerNotFoundException;
    use Ciel\Api\Integration\Partners\Providers\CielErpLocalPartnerAdapter;
    use CielIntegration\Integration\Admin\IntegrationService;
    use CielIntegration\Integration\Admin\Order\OrderPartnerResolver;
    use CielIntegration\Integration\Admin\Order\OrderResolver;
    use InvalidArgumentException;

	class OpenCartCielErpLocalPartnerAdapter 
		extends IntegrationService
		implements CielErpLocalPartnerAdapter {

		/**
		 * @var RemotePartnerToLocalCustomerMarshallerFactory
		 */
		private $_remotePartnerMarshallerFactory;

		/**
		 * @var PartnerResolver
		 */
		private $_partnerResolver;

		/**
		 * @var OrderResolver
		 */
		private $_orderResolver;

		/**
		 * @var OrderPartnerResolver
		 */
		private $_orderPartnerResolver;

		public function __construct(\Registry $registry) {
			parent::__construct($registry);
			$this->_remotePartnerMarshallerFactory = 
				new RemotePartnerToLocalCustomerMarshallerFactory($registry);
			$this->_partnerResolver = 
				new PartnerResolver($registry);
			$this->_orderResolver = 
				new OrderResolver($registry);
			$this->_orderPartnerResolver = 
				new OrderPartnerResolver($registry);
		}

		public function getAllLocalPartnersForExport() { 
			//TODO: Implement
			return array();
		}

		public function removeCustomAddressBillingDataForAllCustomers() { 
			$remotePartnerModel = $this->_getRemotePartnerModel();
			$remotePartnerModel->removeAll();
		}

		public function connectWithRemotePartner($localPartnerId, $remotePartnerData, $remoteShopBillingAddressData) { 
			if (empty($localPartnerId)) {
				throw new InvalidArgumentException('Local partner Id must not be empty');
			}

			if (empty($remotePartnerData) || !is_array($remotePartnerData)) {
				throw new InvalidArgumentException('Remote data must be a vaild, non-empty, array');
			}

			if (!$this->_customerExists($localPartnerId)) {
				throw new LocalPartnerNotFoundException('id', $localPartnerId);
			}

			if ($remoteShopBillingAddressData == null) {
				$remoteShopBillingAddressData = array();
			}

			$this->_setCustomerBindingInformation($localPartnerId, 
				$remotePartnerData, 
				$remoteShopBillingAddressData);

			return $this->getPartnerData($localPartnerId);
		}

		private function _customerExists($customerId) {
			return $this->_partnerResolver
				->customerExists($customerId);
		}

		private function _createPartnerMarshaller($customerId) {
			return $this->_remotePartnerMarshallerFactory
				->createForCustomer($customerId);
		}

		private function _setCustomerBindingInformation($customerId, 
			$remotePartnerData, 
			$remoteShopBillingAddressData) {
			$this->_createPartnerMarshaller($customerId)
				->setCustomerBindingInformation($remotePartnerData, 
					$remoteShopBillingAddressData);
		}

		public function connectOrderWithRemotePartner($localOrderId, $remotePartnerData, $remoteShopBillingAddressData) {
			if (empty($localOrderId)) {
				throw new InvalidArgumentException('Local order Id must not be empty');
			}

			if (empty($remotePartnerData) || !is_array($remotePartnerData)) {
				throw new InvalidArgumentException('Remote partner data must not be empty');
			}

			$customerId = $this->_getCustomerIdForOrder($localOrderId);
			if (empty($customerId) && $customerId != 0) {
				throw new LocalOrderNotFoundException('id', $localOrderId);
			}

			if ($remoteShopBillingAddressData == null) {
				$remoteShopBillingAddressData = array();
			}

			$this->_setOrderCustomerBindingInformation($localOrderId, 
				$customerId, 
				$remotePartnerData, 
				$remoteShopBillingAddressData);

			return $this->getPartnerDataForOrder($localOrderId);
		}

		private function _getCustomerIdForOrder($orderId) {
			return $this->_partnerResolver
				->getCustomerIdForOrder($orderId);
		}

		private function _setOrderCustomerBindingInformation($orderId, 
			$customerId,
			array $remotePartnerData, 
			array $remoteShopBillingAddressData) {
			$this->_createPartnerMarshaller($customerId)
				->setOrderCustomerBindingInformation($orderId, 
					$remotePartnerData, 
					$remoteShopBillingAddressData);
		}

		public function connectOrderFromLocalPartnerConnectionInfo($localOrderId) { 
			if (empty($localOrderId)) {
				throw new InvalidArgumentException('Local order Id must not be empty');
			}

			$customerId = $this->_getCustomerIdForOrder($localOrderId);
			if (empty($customerId)) {
				throw new LocalOrderNotFoundException('id', $localOrderId);
			}

			$this->_importOrderCustomerBindingInformation($customerId, 
				$localOrderId);
		}

		private function _importOrderCustomerBindingInformation($customerId, $orderId) {
			$this->_createPartnerMarshaller($customerId)
				->importOrderCustomerBindingInformation($orderId);
		}

		public function getPartnerDataForOrder($localOrderId) { 
			if (empty($localOrderId)) {
				throw new InvalidArgumentException('Local order Id must not be empty');
			}

			$order = $this->_getOrder($localOrderId);
			if (empty($order)) {
				throw new LocalOrderNotFoundException('id', $localOrderId);
			}

			$customerId = intval($order['customer_id']);
			if (empty($customerId) && $customerId != 0) {
				throw new LocalPartnerNotFoundException('id', $localOrderId);
			}

			$bindingInformation = $this->_getOrderCustomerRemotePartnerBindingInformation($localOrderId, 
				$customerId);

			if ($bindingInformation == null) {
				$bindingInformation = array();
			}

			$customAddressData = $this->_getOrderCustomerBillingAddressInformation($localOrderId, 
				$customerId);

			if ($customAddressData == null) {
				$customAddressData = array();
			}

			$customerData = array(
				'id' => $customerId,
				'email' => $order['email'],
				'first_name' => $order['firstname'],
				'last_name' => $order['lastname'],
				'code' => !empty($bindingInformation) 
					? $bindingInformation['remote_partner_code'] 
					: null,
				'address' => array(
					'address_lines_1' => $order['payment_address_1'],
					'address_lines_2' => $order['payment_address_2'],
					'address_country_name' => $order['payment_country'],
					'address_country_id' => $order['payment_country_id'],
					'address_county_name' => $order['payment_zone'],
					'address_county_id' => $order['payment_zone_id'],
					'address_city_name' => $order['payment_city'],
					'address_city_id' => null,
					'address_postal_code' => $order['payment_postcode'],
					'address_phone' => $order['telephone'],
					'address_company_name' => $order['payment_company'],
					'address_first_name' => $order['payment_firstname'],
					'address_last_name' => $order['payment_lastname'],
					'address_email' => $order['email'],
					'address_partner_worksite_id' => !empty($bindingInformation) 
						? $bindingInformation['remote_partner_addr_worksite_id'] 
						: null
				)
			);

			$customerData['address'] = array_merge($customerData['address'], 
				$customAddressData);

			return $customerData;
		}

		private function _getOrder($orderId) {
			return $this->_orderResolver
				->getOrder($orderId);
		}

		private function _getOrderCustomerBillingAddressInformation($orderId, $customerId) {
			return $this->_orderPartnerResolver
				->getOrderCustomerBillingAddressInformation($orderId, 
					$customerId);
		}

		private function _getOrderCustomerRemotePartnerBindingInformation($orderId, $customerId) {
			return $this->_orderPartnerResolver
				->getOrderCustomerRemotePartnerBindingInformation($orderId, 
					$customerId);
		}

		public function getPartnerData($localPartnerId) { 
			if (empty($localPartnerId)) {
				throw new InvalidArgumentException('Local partner Id must not be empty');
			}

			$customer = $this->_getCustomerById($localPartnerId);
			if ($customer == null) {
				return null;
			}

			$bindingInformation = $this->_getRemotePartnerBindingInformation($localPartnerId);
			if ($bindingInformation == null) {
				$bindingInformation = array();
			}

			$customAddressData = $this->_getCustomerBillingAddressInformation($localPartnerId);
			if ($customAddressData == null) {
				$customAddressData = array();
			}

			$addressId = intval($customer['address_id']);
			$address = $this->_getCustomerAddress($addressId);
			if ($address == null) {
				$address = array();
			}

			$customerData = array(
				'id' => $customer['customer_id'],
				'email' => $customer['email'],
				'first_name' => $customer['firstname'],
				'last_name' => $customer['lastname'],
				'code' => !empty($bindingInformation) 
					? $bindingInformation['remote_partner_code'] 
					: null,
				'address' => array(
					'address_lines_1' => !empty($address) 
						? $address['address_1'] 
						: '',
					'address_lines_2' => !empty($addres)
						? $address['address_2']
						: '',
					'address_country_name' => !empty($address)
						? $address['country']
						: '',
					'address_country_id' => !empty($address) 
						? $address['country_id'] 
						: null,
					'address_county_name' =>  !empty($address)
						? $address['zone']
						: '',
					'address_county_id' => !empty($address) 
						? $address['zone_id'] 
						: null, 
					'address_city_name' => !empty($address) 
						? $address['city'] 
						: '',
					'address_city_id' => null,
					'address_postal_code' => !empty($address)
						? $address['postcode']
						: '',
					'address_phone' => $customer['telephone'],
					'address_company_name' => !empty($address) 
						? $address['company'] 
						: null,
					'address_first_name' => !empty($address) 
						? $address['firstname'] 
						: null,
					'address_last_name' => !empty($address)
						? $address['lastname']
						: null,
					'address_email' => '',
					'address_partner_worksite_id' => !empty($bindingInformation) 
						? $bindingInformation['remote_partner_addr_worksite_id'] 
						: null
				)
			);

			if (empty($customerData['address']['address_email'])) {
				$customerData['address']['address_email'] = $customerData['email'];
			}

			if (empty($customerData['address']['address_first_name'])) {
				$customerData['address']['address_first_name'] = $customerData['first_name'];
			}

			if (empty($customerData['address']['address_last_name'])) {
				$customerData['address']['address_last_name'] = $customerData['last_name'];
			}

			$customerData['address'] = array_merge($customerData['address'], 
				$customAddressData);

			return $customAddressData;
		}
		
		private function _getCustomerById($customerId) {
			return $this->_partnerResolver
				->getCustomer($customerId);
		}

		private function _getCustomerBillingAddressInformation($customerId) {
			return $this->_partnerResolver
				->getCustomerBillingAddressInformation($customerId);
		}

		private function _getCustomerAddress($addressId) {
			return $this->_partnerResolver
				->getCustomerAddress($addressId);
		}

		private function _getRemotePartnerBindingInformation($customerId) {
			return $this->_partnerResolver
				->getRemotePartnerBindingInformation($customerId);
		}
	}
}