<?php
namespace Ciel\Api\Integration\Orders {
	interface RemoteDiscountArticleResolver {
		function getDiscountArticleForVatQuotaValue($vatQuotaValue);
	}
}