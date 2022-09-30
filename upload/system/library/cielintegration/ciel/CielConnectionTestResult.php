<?php
namespace Ciel\Api {
	class CielConnectionTestResult {
		const ERR_CONNECTION_TEST_NOT_BOUND = -1;
		
		const ERR_CONNECTION_TEST_FAIL_ENDPOINT = -2;

		const ERR_CONNECTION_TEST_FAIL_AUTH = -3;

		const ERR_CONNECTION_TEST_GENERIC = -4;

		const ERR_CONNECTION_TEST_OK = 0;

		public static function isSuccessful($result) {
			return $result == self::ERR_CONNECTION_TEST_OK;
		}
	}
}