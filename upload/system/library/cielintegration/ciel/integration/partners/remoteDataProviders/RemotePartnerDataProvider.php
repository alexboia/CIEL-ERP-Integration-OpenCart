<?php
namespace Ciel\Api\Integration\Partners\RemoteDataProviders {

    use Ciel\Api\CielClientFactory;
    use Ciel\Api\Request\Parameters\AddPartnerRequestParameters;
    use Ciel\Api\Request\Parameters\GetAllPartnersRequestParameters;
    use Ciel\Api\Request\Parameters\GetPartnerByCodeRequestParameters;
    use Ciel\Api\Request\Parameters\GetPartnerByIdRequestParameters;
    use Ciel\Api\Request\Parameters\UpdatePartnerRequestParameters;

	class RemotePartnerDataProvider {
		/**
		 * @var CielClientFactory
		 */
		private $_cielClientFactory;

		public function __construct(CielClientFactory $cielClientFactory) {
			$this->_cielClientFactory = $cielClientFactory;
		}

		public function getAllRemotePartners() {
			$client = $this->_getCielClientAndLogon();
			return $client->getAllPartners(
				$this->_getGetAllPartnersRequestParams()
			);
		}

		private function _getCielClientAndLogon() {
			return $this->_cielClientFactory
				->getAmbientCielClient();
		}

		private function _getGetAllPartnersRequestParams() {
			return (new GetAllPartnersRequestParameters())
				->setIncludeAddresses(true)
				->setIncludeOnlyUnblockedPartners(true);
		}

		public function createRemotePartner(array $remoteData) {
			$client = $this->_getCielClientAndLogon();
			return $client->addPartner(
				$this->_getAddPartnerRequestParams(
					$remoteData
				)
			);
		}

		private function _getAddPartnerRequestParams(array $remoteData) {
			return (new AddPartnerRequestParameters())
				->setPartner($remoteData);
		}

		public function updateRemotePartner($code, array $remoteData) {
			$client = $this->_getCielClientAndLogon();
			return $client->updatePartner(
				$this->_getUpdatePartnerRequestParams(
					$code,
					$remoteData
				)
			);
		}

		private function _getUpdatePartnerRequestParams($code, array $remoteData) {
			$propertyPairsToUpdate = $this
				->_getPartnerPropertyPairsForUpdate($remoteData);
			$addressesToUpdate = $this
				->_getPartnerAddressesToUpdate($remoteData);

			return (new UpdatePartnerRequestParameters())
				->setPartnerCode($code)
				->setPropertiesToUpdate($propertyPairsToUpdate)
				->setAddressesToUpdate($addressesToUpdate);
		}

		private function _getPartnerPropertyPairsForUpdate(array $remoteData) {
			$propertyPairsToUpdate = array();
			$propNamesForUpdate = $this->_getPropNamesForUpdate();

			foreach ($propNamesForUpdate as $pName) {
				$propertyPairsToUpdate[] = array(
					'Name' => $pName,
					'Value' => $remoteData[$pName]
				);
			}

			return $propertyPairsToUpdate;
		}

		private function _getPropNamesForUpdate() {
			return array(
				'Name',
				'Code',
				'TaxCode',
				'TaxAttribute',
				'TradeRegisterNumber',
				'Bank',
				'IBAN'
			);
		}

		private function _getPartnerAddressesToUpdate(array $remoteData) {
			$addressesToUpdate = array();

			if (!empty($remoteData['Addresses'])) {
				foreach ($remoteData['Addresses'] as $remoteAddrData) {
					$addressesToUpdate[] = $this->_getPartnerAddressToUpdate($remoteAddrData);
				}
			}

			return $addressesToUpdate;
		}

		private function _getPartnerAddressToUpdate(array $remoteAddressData) {
			$addressPropertyPairsToUpdate = array();
			foreach ($remoteAddressData as $pName => $value) {
				if ($pName != 'ExternalKey') {
					$addressPropertyPairsToUpdate[] = array(
						'Name' => $pName,
						'Value' => $value
					);
				}
			}

			return array(
				'ExternalKey' => $remoteAddressData['ExternalKey'],
				'PropertiesToUpdate' => $addressPropertyPairsToUpdate
			);
		}

		public function getRemotePartnerByCode($remotePartnerCode) {
			$client = $this->_getCielClientAndLogon();
			return $client->getPartnerByCode(
				$this->_getGetRemotePartnerByCodeRequestParameters(
					$remotePartnerCode
				)
			);
		}

		private function _getGetRemotePartnerByCodeRequestParameters($remotePartnerCode) {
			return (new GetPartnerByCodeRequestParameters())
				->setIncludeAddresses(true)
				->setCode($remotePartnerCode);
		}

		public function getRemotePartnerById($remotePartnerId) {
			$client = $this->_getCielClientAndLogon();
			return $client->getPartnerById(
				$this->_getGetRemotePartnerByIdRequestParams(
					$remotePartnerId
				)
			);
		}

		private function _getGetRemotePartnerByIdRequestParams($remotePartnerId) {
			return (new GetPartnerByIdRequestParameters())
				->setIncludeAddresses(true)
				->setId($remotePartnerId);
		}
	}
}