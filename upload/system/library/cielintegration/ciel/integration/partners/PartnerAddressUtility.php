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

				$country = !empty($localCustomerData['address'])
						&& !empty($localCustomerData['address']['address_country_name'])
					? $localCustomerData['address']['address_country_name']
					: '';

				$county = !empty($localCustomerData['address'])
						&& !empty($localCustomerData['address']['address_county_name'])
					? $localCustomerData['address']['address_county_name']
					: '';

				$city = !empty($localCustomerData['address'])
						&& !empty($localCustomerData['address']['address_city_name'])
					? $localCustomerData['address']['address_city_name']
					: '';

				$postalCode = !empty($localCustomerData['address'])
						&& !empty($localCustomerData['address']['address_postal_code'])
					? !empty($localCustomerData['address']['address_postal_code'])
					: '';

				$email = !empty($localCustomerData['email'])
					? $localCustomerData['email']
					: '';

				$phone = !empty($localCustomerData['address'])
						&& !empty($localCustomerData['address']['address_phone'])
					? $localCustomerData['address']['address_phone']
					: '';

				if (!empty($country)) {
					$parts[] = $country;
				} else {
					$parts[] = '[no-country]';
				}

				if (!empty($county)) {
					$parts[] = $county;
				} else {
					$parts[] = '[no-county]';
				}

				if (!empty($city)) {
					$parts[] = $city;
				} else {
					$parts[] = '[no-city]';
				}

				if (!empty($postalCode)) {
					$parts[] = $postalCode;
				} else {
					$parts[] = '[no-postcode]';
				}

				if (!empty($email)) {
					$parts[] = $email;
				} else {
					$parts[] = '[no-email]';
				}

				if (!empty($phone)) {
					$parts[] = $phone;
				} else {
					$parts = '[no-phone]';
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
			$externalKey = self::_deriveExternalAddressKey($localPartnerIdentifier);
			return sha1($externalKey);
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