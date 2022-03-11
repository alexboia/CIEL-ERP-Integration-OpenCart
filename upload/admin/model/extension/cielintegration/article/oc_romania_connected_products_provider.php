<?php
namespace CielIntegration\Integration\Admin\Article {
    use CielIntegration\Integration\Admin\IntegrationService;

	/**
	 * @property \DB $db
	 */
	class OcRomaniaConnectedProductsProvider extends IntegrationService {
		public function getSyncedProducts() {
			$syncedProducts = array();

			$db = $this->_getDb();
			$result = $db->query('SELECT * FROM `' . DB_PREFIX . 'product` WHERE `status` = 1');

			if (!empty($result->rows)) {
				foreach ($result->rows as $row) {
					if (!empty($row['ciel_sync']) 
						&& intval($row['ciel_sync']) == 1 
						&& !empty($row['sku'])) {
						$syncedProducts[] = array(
							'id' => intval($row['product_id']),
							'sku' => $row['sku'],
							'ciel_id_intern' => isset($row['ciel_id_intern']) 
								? intval($row['ciel_id_intern']) 
								: 0
						);
					}
				}
			}

			return $syncedProducts;
		}

		/**
		 * @return \DB
		 */
		private function _getDb() {
			return $this->db;
		}
	}
}