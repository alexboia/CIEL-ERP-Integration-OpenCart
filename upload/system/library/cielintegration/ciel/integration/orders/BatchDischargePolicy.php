<?php
namespace Ciel\Api\Integration\Orders {
	interface BatchDischargePolicy {
		function determineBatchesToDischarge($code);
	}
}