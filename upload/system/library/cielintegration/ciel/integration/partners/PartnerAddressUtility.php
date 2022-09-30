<?php
namespace Ciel\Api\Integration\Partners {
	use InvalidArgumentException;
	use Ciel\Api\Data\PartnerAddressType;

	class PartnerAddressUtility {
		public static function determineExternalAddressKey(array $localCustomerData) {
			$externalKey = null;
			if (!empty($localCustomerData['address']) 
				&& !empty($localCustomerData['address']['address_external_key'])) {
				$externalKey = $localCustomerData['address']['address_external_key'];
			} else {
				$parts = array();
				
				$email = !empty($localCustomerData['email'])
					? $localCustomerData['email']
					: '';

				$phone = !empty($localCustomerData['address'])
						&& !empty($localCustomerData['address']['address_phone'])
					? $localCustomerData['address']['address_phone']
					: '';

				if (!empty($email)) {
					$parts[] = $email;
				}

				if (!empty($phone)) {
					$parts[] = $phone;
				}
				
				if (!empty($parts)) {
					$externalKey = self::deriveExternalAddressKeyFromParts($parts);
				}
			}

			return $externalKey;
		}

		public static function deriveExternalAddressKeyFromParts($parts) {
			$rawIdentifier = join('-', $parts);
			$localPartnerIdentifier = self::_prepareAddressKeyIdentifier($rawIdentifier);
			return self::_deriveExternalAddressKey($localPartnerIdentifier);
		}

		private static function _prepareAddressKeyIdentifier($rawIdentifier) {
			$identifier = trim(strtolower($rawIdentifier));
			return preg_replace('/[\s]/mi', '', $identifier);
		}

		private static function _deriveExternalAddressKey($localPartnerIdentifier) {
			$actualIdentifiers = array();
			$allIdentifiers = func_get_args();

			foreach ($allIdentifiers as $identifier) {
				$normalizedIdentifier = self::_prepareAddressKeyIdentifier($identifier);
				if (!empty($normalizedIdentifier)) {
					$actualIdentifiers[] = $normalizedIdentifier;
				}
			}

			if (empty($actualIdentifiers)) {
				throw new InvalidArgumentException('No valid local partner identifiers given');
			}

			$externalKeyPartnerIdentifierPart = 
				join('-', $actualIdentifiers);

			return sprintf('%s-%s', 
				$externalKeyPartnerIdentifierPart, 
				self::_computeExternalAddressSuffix());
		}

		private static function _computeExternalAddressSuffix() {
			return sprintf('%s-ceshop', PartnerAddressType::Worksite);
		}
	}
}