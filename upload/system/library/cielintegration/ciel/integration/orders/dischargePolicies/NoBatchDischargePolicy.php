<?php
namespace Ciel\Api\Integration\Orders\DischargePolicies {
	use Ciel\Api\Integration\Orders\BatchDischargePolicy;

	class NoBatchDischargePolicy implements BatchDischargePolicy {
		public function determineBatchesToDischarge($code) { 
			return array();
		}
	}
}