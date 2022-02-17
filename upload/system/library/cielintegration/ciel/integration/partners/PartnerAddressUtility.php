<?php
namespace Ciel\Api\Integration\Partners {
	use InvalidArgumentException;
	use Ciel\Api\Data\PartnerAddressType;

	class PartnerAddressUtility {
		public static function computeExternalAddressSuffix() {
			return sprintf('%s-ceshop', PartnerAddressType::Worksite);
		}

		public static function prepareAddressKeyIdentifier($rawIdentifier) {
			$identifier = trim(strtolower($rawIdentifier));
			return preg_replace('/[\s]/mi', '', $identifier);
		}

		public static function prepareAddressKeyEmailIdentifier($rawIdentifier) {
			return self::prepareAddressKeyIdentifier($rawIdentifier);
		}

		public static function prepareAddressKeyPhoneIdentifier($rawIdentifier) {
			$identifier = self::prepareAddressKeyIdentifier($rawIdentifier);
			return preg_replace('/[^0-9]/mi', '', $identifier);
		}

		public static function isExternalAddressKey($addressKey) {
			$suffix = self::computeExternalAddressSuffix();
			if (!empty($addressKey)) {
				return strtolower(stristr($addressKey, $suffix)) === strtolower($suffix);
			} else {
				return false;
			}
		}

		public static function deriveExternalAddressKeyFromEmail($email) {
			$emailIdentifier = self::prepareAddressKeyEmailIdentifier($email);
			return self::deriveExternalAddressKey($emailIdentifier);
		}

		public static function deriveExternalAddressKeyFromPhone($phone) {
			$phoneIdentifier = self::prepareAddressKeyPhoneIdentifier($phone);
			return self::deriveExternalAddressKey($phoneIdentifier);
		}

		public static function deriveExternalAddressKey($localPartnerIdentifier) {
			$actualIdentifiers = array();
			$allIdentifiers = func_get_args();

			foreach ($allIdentifiers as $identifier) {
				$normalizedIdentifier = self::prepareAddressKeyIdentifier($identifier);
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
				self::computeExternalAddressSuffix());
		}

		public static function findRemotePartnerBillingAddressData(array $remotePartnerData, $externalKey) {
			$foundAddress = null;

			if (!empty($remotePartnerData) && !empty($remotePartnerData['Addresses'])) {
				foreach ($remotePartnerData['Addresses'] as $remotePartnerAddressData) {
					if (self::_isRemotePartnerShopBillingAddress($remotePartnerAddressData, $externalKey)) {
						$foundAddress = $remotePartnerAddressData;
						break;
					}
				}
			}

			return $foundAddress;
		}

		private static function _isRemotePartnerShopBillingAddress(array $remotePartnerAddressData, $externalKey) {
			return !empty($remotePartnerAddressData['ExternalKey']) 
				&& $remotePartnerAddressData['ExternalKey'] == $externalKey
				&& !empty($remotePartnerAddressData['AddressType'])
				&& $remotePartnerAddressData['AddressType'] == PartnerAddressType::Worksite;
		}
	}
}