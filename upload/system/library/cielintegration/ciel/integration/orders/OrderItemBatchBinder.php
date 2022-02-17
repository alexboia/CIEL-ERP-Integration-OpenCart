<?php
namespace Ciel\Api\Integration\Orders {
	use Ciel\Api\Integration\Orders\OrderItemBatchConfiguration;
    use DateTime;
    use DateTimeZone;

	class OrderItemBatchBinder {
		const NO_EXPIRATION_DATE = '3000-12-31 23:59:59';

		const EXPRIATION_DATE_FORMAT = 'Y-m-d H:i:s';

		private $_orderLineItemsWithBatches = array();

		public function __construct(array $orderLineItems) {
			foreach ($orderLineItems as $lineItem) {
				if (isset($lineItem['item_batch_tracking_enabled']) 
					&& $lineItem['item_batch_tracking_enabled'] == true) {
					$this->_orderLineItemsWithBatches[$lineItem['item_code']] = $lineItem;
				}
			}
		}

		public function bind(array $warehouseBatches) {
			/** @var OrderItemBatchConfiguration[] $configuration */
			$configuration = array();
			$warehouseBatches = $this->_prepareBatches($warehouseBatches);

			foreach ($warehouseBatches as $wb) {
				$productCode = $wb['TargetArticleCode'];
				if (!isset($this->_orderLineItemsWithBatches[$productCode])) {
					continue;
				}

				if (!isset($configuration[$productCode])) {
					$lineItem = $this->_orderLineItemsWithBatches[$productCode];
					$configuration[$productCode] = new OrderItemBatchConfiguration($lineItem);
				}

				$lineItemConfiguration = $configuration[$productCode];
				$lineItemConfiguration->requestQuantityFromBatch($wb);
			}

			return $configuration;
		}

		private function _prepareBatches(array $warehouseBatches) {
			usort($warehouseBatches, function($wbA, $wbB) {
				$dateA = !empty($wbA['LotExpiryDate']) 
					? $wbA['LotExpiryDate'] 
					: self::NO_EXPIRATION_DATE;

				$dateB = !empty($wbB['LotExpiryDate'])
					? $wbB['LotExpiryDate']
					: self::NO_EXPIRATION_DATE;

				$dateA = DateTime::createFromFormat(self::EXPRIATION_DATE_FORMAT, 
					$dateA, 
					$this->_getTimeZone());

				$dateB = DateTime::createFromFormat(self::EXPRIATION_DATE_FORMAT, 
					$dateB, 
					$this->_getTimeZone());

				$dateATs = $dateA->getTimestamp();
				$dateBTs = $dateB->getTimestamp();

				if ($dateATs < $dateBTs) {
					return -1;
				} else if ($dateATs > $dateBTs) {
					return 1;
				} else {
					return 0;
				}
			});

			return $warehouseBatches;
		}

		private  function _getTimeZone() {
			return new DateTimeZone(date_default_timezone_get());
		}

		public function getRawOrderLineItemsWithBatches() {
			return $this->_orderLineItemsWithBatches;
		}
	}
}