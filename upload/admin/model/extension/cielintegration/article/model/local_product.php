<?php
namespace CielIntegration\Integration\Admin\Article\Model {

    use CielIntegration\CielModel;

	class LocalProduct extends CielModel {
		public function getProduct($productId) {
			$result = null;
			
			if (!empty($productId)) {
				$db = $this->_getDb();
				$query = $db->query("SELECT DISTINCT *, (SELECT keyword FROM " . DB_PREFIX . "url_alias WHERE query = 'product_id=" . (int)$productId . "') AS keyword FROM " . DB_PREFIX . "product p LEFT JOIN " . DB_PREFIX . "product_description pd ON (p.product_id = pd.product_id) WHERE p.product_id = '" . (int)$productId . "' AND pd.language_id = '" . (int)$this->config->get('config_language_id') . "'");
				if (!empty($query) && !empty($query->row)) {
					$result = $query->row;
				}
			}

			return $result;
		}
		
		public function getProducts($data = array()) {
			$db = $this->_getDb();
			$sql = "SELECT * FROM " . DB_PREFIX . "product p LEFT JOIN " . DB_PREFIX . "product_description pd ON (p.product_id = pd.product_id) WHERE pd.language_id = '" . (int)$this->config->get('config_language_id') . "'";
	
			if (!empty($data['filter_name'])) {
				$sql .= " AND pd.name LIKE '" . $db->escape($data['filter_name']) . "%'";
			}
	
			if (!empty($data['filter_model'])) {
				$sql .= " AND p.model LIKE '" . $db->escape($data['filter_model']) . "%'";
			}
	
			if (isset($data['filter_price']) && !is_null($data['filter_price'])) {
				$sql .= " AND p.price LIKE '" . $db->escape($data['filter_price']) . "%'";
			}
	
			if (isset($data['filter_quantity']) && !is_null($data['filter_quantity'])) {
				$sql .= " AND p.quantity = '" . (int)$data['filter_quantity'] . "'";
			}
	
			if (isset($data['filter_status']) && !is_null($data['filter_status'])) {
				$sql .= " AND p.status = '" . (int)$data['filter_status'] . "'";
			}
	
			if (isset($data['filter_image']) && !is_null($data['filter_image'])) {
				if ($data['filter_image'] == 1) {
					$sql .= " AND (p.image IS NOT NULL AND p.image <> '' AND p.image <> 'no_image.png')";
				} else {
					$sql .= " AND (p.image IS NULL OR p.image = '' OR p.image = 'no_image.png')";
				}
			}
	
			$sql .= " GROUP BY p.product_id";
	
			$sort_data = array(
				'pd.name',
				'p.model',
				'p.price',
				'p.quantity',
				'p.status',
				'p.sort_order'
			);
	
			if (isset($data['sort']) && in_array($data['sort'], $sort_data)) {
				$sql .= " ORDER BY " . $data['sort'];
			} else {
				$sql .= " ORDER BY pd.name";
			}
	
			if (isset($data['order']) && ($data['order'] == 'DESC')) {
				$sql .= " DESC";
			} else {
				$sql .= " ASC";
			}
	
			if (isset($data['start']) || isset($data['limit'])) {
				if ($data['start'] < 0) {
					$data['start'] = 0;
				}
	
				if ($data['limit'] < 1) {
					$data['limit'] = 20;
				}
	
				$sql .= " LIMIT " . (int)$data['start'] . "," . (int)$data['limit'];
			}
	
			$query = $db->query($sql);
			return $query->rows;
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
			$result = $db->query('SELECT sku FROM `' . DB_PREFIX . 'product` WHERE product_id = "' . intval($productId) . '"');

			$row = $result->row;
			return !empty($row) && !empty($row['sku'])
				? $row['sku']
				: null;
		}
	}
}