<?php
namespace CielIntegration\Integration\Admin\Article\Model {

    use CielIntegration\CielModel;

	class LocalProduct extends CielModel {
		public function getProduct($productId) {
			$result = null;
			
			if (!empty($productId)) {
				$db = $this->_getDb();
				$result = $db->query("SELECT DISTINCT *, (SELECT keyword FROM " . DB_PREFIX . "url_alias WHERE query = 'product_id=" . (int)$productId . "') AS keyword FROM " . DB_PREFIX . "product p LEFT JOIN " . DB_PREFIX . "product_description pd ON (p.product_id = pd.product_id) WHERE p.product_id = '" . (int)$productId . "' AND pd.language_id = '" . (int)$this->config->get('config_language_id') . "'");
				if (!empty($result) && !empty($result->row)) {
					$result = $result->row;
				}
			}

			return $result;
		}
		
		public function getProducts($data = array()) {
			$db = $this->_getDb();
			$query = "SELECT * FROM " . DB_PREFIX . "product p LEFT JOIN " . DB_PREFIX . "product_description pd ON (p.product_id = pd.product_id) WHERE pd.language_id = '" . (int)$this->_getCurrentLanguageId() . "'";
	
			if (!empty($data['filter_name'])) {
				$query .= " AND pd.name LIKE '" . $db->escape($data['filter_name']) . "%'";
			}
	
			if (!empty($data['filter_model'])) {
				$query .= " AND p.model LIKE '" . $db->escape($data['filter_model']) . "%'";
			}
	
			if (isset($data['filter_price']) && !is_null($data['filter_price'])) {
				$query .= " AND p.price LIKE '" . $db->escape($data['filter_price']) . "%'";
			}
	
			if (isset($data['filter_quantity']) && !is_null($data['filter_quantity'])) {
				$query .= " AND p.quantity = '" . (int)$data['filter_quantity'] . "'";
			}
	
			if (isset($data['filter_status']) && !is_null($data['filter_status'])) {
				$query .= " AND p.status = '" . (int)$data['filter_status'] . "'";
			}
	
			if (isset($data['filter_image']) && !is_null($data['filter_image'])) {
				if ($data['filter_image'] == 1) {
					$query .= " AND (p.image IS NOT NULL AND p.image <> '' AND p.image <> 'no_image.png')";
				} else {
					$query .= " AND (p.image IS NULL OR p.image = '' OR p.image = 'no_image.png')";
				}
			}
	
			$query .= " GROUP BY p.product_id";
	
			$sort_data = array(
				'pd.name',
				'p.model',
				'p.price',
				'p.quantity',
				'p.status',
				'p.sort_order'
			);
	
			if (isset($data['sort']) && in_array($data['sort'], $sort_data)) {
				$query .= " ORDER BY " . $data['sort'];
			} else {
				$query .= " ORDER BY pd.name";
			}
	
			if (isset($data['order']) && ($data['order'] == 'DESC')) {
				$query .= " DESC";
			} else {
				$query .= " ASC";
			}
	
			if (isset($data['start']) || isset($data['limit'])) {
				if ($data['start'] < 0) {
					$data['start'] = 0;
				}
	
				if ($data['limit'] < 1) {
					$data['limit'] = 20;
				}
	
				$query .= " LIMIT " . (int)$data['start'] . "," . (int)$data['limit'];
			}
	
			$result = $db->query($query);
			return $result->rows;
		}

		private function _getCurrentLanguageId() {
			return (int)$this->config->get('config_language_id');
		}

		public function productExits($productId) {
			if (empty($productId)) {
				return false;
			}

			$db = $this->_getDb();
			$result = $db->query('SELECT COUNT(product_id) as product_count FROM `' . DB_PREFIX . 'product` WHERE product_id = "' . intval($productId) . '"');
			
			$row = $result->row;
			return !empty($row) && !empty($row['product_count'])
				? intval($row['product_count']) > 0
				: false;
		}

		public function lookupProductId($sku) {
			if (empty($sku)) {
				return 0;
			}

			$db = $this->_getDb();
			$result = $db->query('SELECT product_id FROM `' . DB_PREFIX . 'product` WHERE sku = "' . $db->escape($sku) . '" LIMIT 1');

			$row = $result->row;
			return !empty($row) && !empty($row['product_id'])
				? intval($row['product_id'])
				: 0;
		}

		public function lookupProductSku($productId) {
			if (empty($productId)) {
				return null;
			}

			$db = $this->_getDb();
			$result = $db->query('SELECT sku 
				FROM `' . DB_PREFIX . 'product` 
				WHERE product_id = "' . intval($productId) . '"');

			$row = $result->row;
			return !empty($row) && !empty($row['sku'])
				? $row['sku']
				: null;
		}

		public function getHighestPriorityTaxRateInfo($productId) {
			if (empty($productId)) {
				return null;
			}

			$db = $this->_getDb();
			$query = 'SELECT p.tax_class_id product_tax_class_id, rt.rate AS product_main_tax_rate
				FROM ' . DB_PREFIX . 'product p
					LEFT JOIN ' . DB_PREFIX . 'tax_rule r 
							ON r.tax_class_id = p.tax_class_id 
					LEFT JOIN ' . DB_PREFIX . 'tax_rate rt 
							ON rt.tax_rate_id = r.tax_rate_id
				WHERE p.product_id = "' . intval($productId) . '" AND r.based = "payment" AND rt.type = "P"
				ORDER BY r.priority ASC
				LIMIT 1';

			$result = $db->query($query);
			$row = $result->row;

			return !empty($row)
				? $row
				: null;
		}

		public function getCategories($productId) {
			if (empty($productId)) {
				return null;
			}

			$db = $this->_getDb();
			$query = 'SELECT pc.category_id, cd.name AS category_name 
				FROM ' . DB_PREFIX . 'product_to_category pc
					LEFT JOIN ' . DB_PREFIX . 'category c 
						ON c.category_id = pc.category_id
					LEFT JOIN ' . DB_PREFIX . 'category_description cd 
						ON cd.category_id = pc.category_id
					LEFT JOIN ' . DB_PREFIX . 'language l 
						ON l.language_id = cd.language_id
				WHERE pc.product_id = "' . intval($productId) . '" 
					AND l.language_id = "' . $this->_getCurrentLanguageId() . '"';

			$result = $db->query($query);
			return !empty($result->rows)
				? $result->rows
				: null;
		}
	}
}