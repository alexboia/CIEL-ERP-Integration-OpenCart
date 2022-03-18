<?php
namespace CielIntegration\Integration\Admin\Partner\Model {

    use CielIntegration\CielModel;

	class LocalCustomer extends CielModel {
		public function getCustomer($customerId) {
			$result = null;

			if (!empty($customerId)) {
				$db = $this->_getDb();
				$query = $db->query("SELECT DISTINCT * FROM " . DB_PREFIX . "customer WHERE customer_id = '" . (int)$customerId . "'");	
				if (!empty($query) && !empty($query->row)) {
					$result = $query->row;
				}
			}
			
			return $result;
		}

		public function getAddress($addressId) {
			$result = null;

			if (!empty($addressId)) {
				$db = $this->_getDb();
				$addressQuery = $db->query("SELECT * FROM " . DB_PREFIX . "address WHERE address_id = '" . (int)$addressId . "'");

				if ($addressQuery->num_rows) {
					$countryQuery = $db->query("SELECT * FROM `" . DB_PREFIX . "country` WHERE country_id = '" . (int)$addressQuery->row['country_id'] . "'");

					if ($countryQuery->num_rows) {
						$country = $countryQuery->row['name'];
						$iso_code_2 = $countryQuery->row['iso_code_2'];
						$iso_code_3 = $countryQuery->row['iso_code_3'];
						$address_format = $countryQuery->row['address_format'];
					} else {
						$country = '';
						$iso_code_2 = '';
						$iso_code_3 = '';
						$address_format = '';
					}

					$zoneQuery = $db->query("SELECT * FROM `" . DB_PREFIX . "zone` WHERE zone_id = '" . (int)$addressQuery->row['zone_id'] . "'");

					if ($zoneQuery->num_rows) {
						$zone = $zoneQuery->row['name'];
						$zone_code = $zoneQuery->row['code'];
					} else {
						$zone = '';
						$zone_code = '';
					}

					$result = array(
						'address_id' => $addressQuery->row['address_id'],
						'customer_id' => $addressQuery->row['customer_id'],
						'firstname' => $addressQuery->row['firstname'],
						'lastname' => $addressQuery->row['lastname'],
						'company' => $addressQuery->row['company'],
						'address_1' => $addressQuery->row['address_1'],
						'address_2' => $addressQuery->row['address_2'],
						'postcode' => $addressQuery->row['postcode'],
						'city' => $addressQuery->row['city'],
						'zone_id' => $addressQuery->row['zone_id'],
						'zone' => $zone,
						'zone_code' => $zone_code,
						'country_id' => $addressQuery->row['country_id'],
						'country' => $country,
						'iso_code_2' => $iso_code_2,
						'iso_code_3' => $iso_code_3,
						'address_format' => $address_format,
						'custom_field' => json_decode($addressQuery->row['custom_field'], 
							true)
					);
				}
			}

			return $result;
		}

		public function getCustomerIdForOrder($orderId) {
			if (empty($orderId)) {
				return null;
			}

			$db = $this->_getDb();
			$result = $db->query('SELECT customer_id FROM `' . DB_PREFIX . 'order` WHERE order_id = "' . intval($orderId) . '"');

			$row = $result->row;
			return !empty($row) && !empty($row['customer_id'])
				? intval($row['customer_id'])
				: 0;
		}

		public function customerExists($customerId) {
			if (empty($customerId)) {
				return false;
			}

			$db = $this->_getDb();
			$result = $db->query('SELECT COUNT(customer_id) as customer_count FROM `' . DB_PREFIX . 'customer` WHERE customer_id = "' . intval($customerId) . '"');
			
			$row = $result->row;
			return !empty($row) && !empty($row['customer_count'])
				? intval($row['customer_count']) > 0
				: false;
		}
	}
}