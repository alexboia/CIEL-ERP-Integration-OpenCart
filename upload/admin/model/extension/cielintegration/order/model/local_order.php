<?php
namespace CielIntegration\Integration\Admin\Order\Model {
    use CielIntegration\CielModel;

	class LocalOrder extends CielModel {
		public function getOrder($orderId) {
			$db = $this->_getDb();
			$orderQuery = $db->query("SELECT *, (SELECT CONCAT(c.firstname, ' ', c.lastname) FROM " . DB_PREFIX . "customer c WHERE c.customer_id = o.customer_id) AS customer, (SELECT os.name FROM " . DB_PREFIX . "order_status os WHERE os.order_status_id = o.order_status_id AND os.language_id = '" . (int)$this->config->get('config_language_id') . "') AS order_status FROM `" . DB_PREFIX . "order` o WHERE o.order_id = '" . (int)$orderId . "'");
	
			if ($orderQuery->num_rows) {
				//Payment country information
				$countryQuery = $db->query("SELECT * FROM `" . DB_PREFIX . "country` WHERE country_id = '" . (int)$orderQuery->row['payment_country_id'] . "'");
				if ($countryQuery->num_rows) {
					$paymentIsoCode2 = $countryQuery->row['iso_code_2'];
					$paymentIsoCode3 = $countryQuery->row['iso_code_3'];
				} else {
					$paymentIsoCode2 = '';
					$paymentIsoCode3 = '';
				}
	
				//Payment zone information
				$zoneQuery = $db->query("SELECT * FROM `" . DB_PREFIX . "zone` WHERE zone_id = '" . (int)$orderQuery->row['payment_zone_id'] . "'");
				if ($zoneQuery->num_rows) {
					$paymentZoneCode = $zoneQuery->row['code'];
				} else {
					$paymentZoneCode = '';
				}
	
				//Shipping country information
				$countryQuery = $db->query("SELECT * FROM `" . DB_PREFIX . "country` WHERE country_id = '" . (int)$orderQuery->row['shipping_country_id'] . "'");
				if ($countryQuery->num_rows) {
					$shippingIsoCode2 = $countryQuery->row['iso_code_2'];
					$shippingIsoCode3 = $countryQuery->row['iso_code_3'];
				} else {
					$shippingIsoCode2 = '';
					$shippingIsoCode3 = '';
				}
	
				//Shipping zone information
				$zoneQuery = $db->query("SELECT * FROM `" . DB_PREFIX . "zone` WHERE zone_id = '" . (int)$orderQuery->row['shipping_zone_id'] . "'");
				if ($zoneQuery->num_rows) {
					$shippingZoneCode = $zoneQuery->row['code'];
				} else {
					$shippingZoneCode = '';
				}
	
				$reward = 0;
				$orderProductQuery = $db->query("SELECT * FROM " . DB_PREFIX . "order_product WHERE order_id = '" . (int)$orderId . "'");
				foreach ($orderProductQuery->rows as $product) {
					$reward += $product['reward'];
				}

				$languageInfo = $this->_getLanguageLocalisationModel()
					->getLanguage($orderQuery->row['language_id']);
	
				if ($languageInfo) {
					$languageCode = $languageInfo['code'];
				} else {
					$languageCode = $this->config->get('config_language');
				}
	
				return array(
					'order_id' => $orderQuery->row['order_id'],
					'invoice_no' => $orderQuery->row['invoice_no'],
					'invoice_prefix' => $orderQuery->row['invoice_prefix'],
					'store_id' => $orderQuery->row['store_id'],
					'store_name' => $orderQuery->row['store_name'],
					'store_url' => $orderQuery->row['store_url'],
					'customer_id' => $orderQuery->row['customer_id'],
					'customer' => $orderQuery->row['customer'],
					'customer_group_id' => $orderQuery->row['customer_group_id'],
					'firstname' => $orderQuery->row['firstname'],
					'lastname' => $orderQuery->row['lastname'],
					'email' => $orderQuery->row['email'],
					'telephone' => $orderQuery->row['telephone'],
					'fax' => $orderQuery->row['fax'],
					'custom_field' => json_decode($orderQuery->row['custom_field'], true),
					'payment_firstname' => $orderQuery->row['payment_firstname'],
					'payment_lastname' => $orderQuery->row['payment_lastname'],
					'payment_company' => $orderQuery->row['payment_company'],
					'payment_address_1' => $orderQuery->row['payment_address_1'],
					'payment_address_2' => $orderQuery->row['payment_address_2'],
					'payment_postcode' => $orderQuery->row['payment_postcode'],
					'payment_city' => $orderQuery->row['payment_city'],
					'payment_zone_id' => $orderQuery->row['payment_zone_id'],
					'payment_zone' => $orderQuery->row['payment_zone'],
					'payment_zone_code' => $paymentZoneCode,
					'payment_country_id' => $orderQuery->row['payment_country_id'],
					'payment_country' => $orderQuery->row['payment_country'],
					'payment_iso_code_2' => $paymentIsoCode2,
					'payment_iso_code_3' => $paymentIsoCode3,
					'payment_address_format' => $orderQuery->row['payment_address_format'],
					'payment_custom_field' => json_decode($orderQuery->row['payment_custom_field'], true),
					'payment_method' => $orderQuery->row['payment_method'],
					'payment_code' => $orderQuery->row['payment_code'],
					'shipping_firstname' => $orderQuery->row['shipping_firstname'],
					'shipping_lastname' => $orderQuery->row['shipping_lastname'],
					'shipping_company' => $orderQuery->row['shipping_company'],
					'shipping_address_1' => $orderQuery->row['shipping_address_1'],
					'shipping_address_2' => $orderQuery->row['shipping_address_2'],
					'shipping_postcode' => $orderQuery->row['shipping_postcode'],
					'shipping_city' => $orderQuery->row['shipping_city'],
					'shipping_zone_id' => $orderQuery->row['shipping_zone_id'],
					'shipping_zone' => $orderQuery->row['shipping_zone'],
					'shipping_zone_code' => $shippingZoneCode,
					'shipping_country_id' => $orderQuery->row['shipping_country_id'],
					'shipping_country' => $orderQuery->row['shipping_country'],
					'shipping_iso_code_2' => $shippingIsoCode2,
					'shipping_iso_code_3' => $shippingIsoCode3,
					'shipping_address_format' => $orderQuery->row['shipping_address_format'],
					'shipping_custom_field'  => json_decode($orderQuery->row['shipping_custom_field'], true),
					'shipping_method' => $orderQuery->row['shipping_method'],
					'shipping_code' => $orderQuery->row['shipping_code'],
					'comment' => $orderQuery->row['comment'],
					'total' => $orderQuery->row['total'],
					'reward' => $reward,
					'order_status_id' => $orderQuery->row['order_status_id'],
					'order_status' => $orderQuery->row['order_status'],
					'commission' => $orderQuery->row['commission'],
					'language_id' => $orderQuery->row['language_id'],
					'language_code' => $languageCode,
					'currency_id' => $orderQuery->row['currency_id'],
					'currency_code'  => $orderQuery->row['currency_code'],
					'currency_value' => $orderQuery->row['currency_value'],
					'ip' => $orderQuery->row['ip'],
					'forwarded_ip' => $orderQuery->row['forwarded_ip'],
					'user_agent' => $orderQuery->row['user_agent'],
					'accept_language' => $orderQuery->row['accept_language'],
					'date_added' => $orderQuery->row['date_added'],
					'date_modified' => $orderQuery->row['date_modified']
				);
			} else {
				return null;
			}
		}

		/**
		 * @return \ModelLocalisationLanguage
		 */
		private function _getLanguageLocalisationModel() {
			$this->load->model('localisation/language');
			return $this->model_localisation_language;
		}

		public function getOrderProducts($orderId) {
			$result = array();

			if (!empty($orderId)) {
				$db = $this->_getDb();
				$query = $db->query("SELECT * FROM " . DB_PREFIX . "order_product WHERE order_id = '" . (int)$orderId . "'");
				$result = !empty($query->rows) 
					? $query->rows 
					: array();
			}

			return $result;
		}

		public function getOrderTotals($orderId) {
			$result = array();

			if (!empty($orderId)) {
				$db = $this->_getDb();
				$query = $db->query("SELECT * FROM " . DB_PREFIX . "order_total WHERE order_id = '" . (int)$orderId . "' ORDER BY sort_order");
				$result = !empty($query->rows) 
					? $query->rows 
					: array();
			}

			return $result;
		}

		public function orderExists($orderId) {
			if (empty($orderId)) {
				return false;
			}

			$db = $this->_getDb();
			$query = 'SELECT COUNT(`order_id`) as order_count 
				FROM `' . DB_PREFIX . 'order` 
				WHERE `order_id` = "' . intval($orderId) . '"';

			$result = $db->query($query);
			if (!empty($result) && !empty($result->row)) {
				$row = $result->row;
				return !empty($row) && !empty($row['order_count'])
					? intval($row['order_count']) > 0
					: false;
			} else {
				return 0;
			}
		}

		public function countLocalOrders() {
			$db = $this->_getDb();
			$query = 'SELECT COUNT(`order_id`) as order_count 
				FROM `' . DB_PREFIX . 'order`';

			$result = $db->query($query);
			if (!empty($result) && !empty($result->row)) {
				$row = $result->row;
				return !empty($row) && !empty($row['order_count'])
					? intval($row['order_count'])
					: 0;
			} else {
				return 0;
			}			
		}
	}
}