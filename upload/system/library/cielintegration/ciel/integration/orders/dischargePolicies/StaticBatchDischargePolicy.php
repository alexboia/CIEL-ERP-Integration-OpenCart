<?php
namespace Ciel\Api\Integration\Orders\DischargePolicies {

	use Ciel\Api\Integration\Orders\BatchDischargePolicy;
    use InvalidArgumentException;

	class StaticBatchDischargePolicy implements BatchDischargePolicy {

		private $_perArticleBatchDischarges;

		public function __construct(array $perArticleBatchDischarges) {
			$this->_perArticleBatchDischarges = $perArticleBatchDischarges;
		}

		public function determineBatchesToDischarge($code) { 
			if (empty($code)) {
				throw  new InvalidArgumentException('Article code may not be empty.');
			}

			return !empty($this->_perArticleBatchDischarges[$code])
				? $this->_perArticleBatchDischarges[$code]
				: array();
		}
	}
}