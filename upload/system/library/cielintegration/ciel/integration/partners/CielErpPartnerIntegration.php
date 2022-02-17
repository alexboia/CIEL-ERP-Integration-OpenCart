<?php
namespace Ciel\Api\Integration\Partners {

	use Ciel\Api\Exception\LocalPartnerExportFailedException;
	use Ciel\Api\Exception\LocalPartnerNotFoundException;
	use Ciel\Api\Integration\Binding\CielErpToStoreBinding;
	use Ciel\Api\Integration\Partners\Providers\CielErpLocalPartnerAdapter;
	use Ciel\Api\Request\Parameters\AddPartnerRequestParameters;
	use Ciel\Api\Request\Parameters\GetAllPartnersRequestParameters;
	use Ciel\Api\Request\Parameters\GetPartnerByCodeRequestParameters;
	use Ciel\Api\Request\Parameters\GetPartnerByIdRequestParameters;
	use Ciel\Api\Request\Parameters\UpdatePartnerRequestParameters;
	use InvalidArgumentException;

	class CielErpPartnerIntegration {
		/**
		 * @var \Ciel\Api\Integration\Binding\CielErpToStoreBinding
		 */
		private $_storeBinding;

		/**
		 * @var \Ciel\Api\Integration\Partners\Providers\CielErpLocalPartnerAdapter
		 */
		private $_adapter;

		public function __construct(CielErpToStoreBinding $storeBinding, 
			CielErpLocalPartnerAdapter $adapter) {
			$this->_storeBinding = $storeBinding;
			$this->_adapter = $adapter;
		}

		/**
		 * @return \Ciel\Api\CielClient 
		 */
		private function _getCielClientAndLogon() {
			return $this->_storeBinding->getAmbientCielClient(true);
		}

		public function removeCustomAddressBillingDataForAllConnectedPartners() {
			$this->_adapter->removeCustomAddressBillingDataForAllCustomers();
		}

		public function getDefaultPartnerExportParameters() {
			return new CielErpPartnerExportParameters();
		}

		public function exportPartnersForCielImport(CielErpPartnerExportParameters $parameters) {
			$localPartners = $this->_getPartnersForExport();
			$exporter = new CielImportPartnersExporter($localPartners, $parameters);
			return $exporter->makeCsv();
		}

		private function _getPartnersForExport() {
			return $this->_adapter->getAllLocalPartnersForExport();
		}

		public function tryAutoConnectPartner($localPartnerId) {
			if (empty($localPartnerId)) {
				throw new InvalidArgumentException('Local partner Id must not be empty');
			}

			$partnerMatch = null;
			$localPartnerData = $this->_getLocalPartnerData($localPartnerId);

			if (!$localPartnerData->exists()) {
				throw new LocalPartnerNotFoundException('id', $localPartnerId);
			}

			if (!$localPartnerData->hasRemotePartnerCode()) {
				//Fetch all partners, and attempt a match 
				//  with the customer's data
				$remotePartnersData = $this->_getAllRemotePartners();

				//Try find a matching partner within the remote data
				if (!empty($remotePartnersData)) {
					$partnerMatch = $this->_findMatchingRemotePartner(
						$localPartnerData->getData(), 
						$remotePartnersData
					);
				}

				if ($partnerMatch->isMatch()) {
					$this->_adapter->connectWithRemotePartner(
						$localPartnerId, 
						$partnerMatch->getRemotePartnerData(), 
						$partnerMatch->getRemotePartnerShopBillingAddressData()
					);
				}
			}

			return new PartnerConnectionResult($localPartnerData, 
				$partnerMatch);
		}

		public function exportLocalPartner($localPartnerId) {
			if (empty($localPartnerId)) {
				throw new InvalidArgumentException('Local partner Id must not be empty');
			}

			$exportResult = null;
			$localPartnerData = $this->_getLocalPartnerData($localPartnerId);
			
			if (!$localPartnerData->exists()) {
				throw new LocalPartnerNotFoundException('id', $localPartnerId);
			}

			if (!$localPartnerData->hasRemotePartnerCode()) {
				$exportResult = $this->_createRemotePartnerFromLocalPartnerData($localPartnerData);
			} else {
				$exportResult = $this->_updateRemotePartnerFromLocalPartnerData($localPartnerData);
			}

			$this->_adapter->connectWithRemotePartner($localPartnerId, 
				$exportResult->getRemotePartnerData(), 
				$exportResult->getRemotePartnerShopBillingAddressData());
		}

		private function _getLocalPartnerData($localPartnerId) {
			return new LocalPartnerData($this->_adapter->getPartnerData($localPartnerId));
		}

		public function tryAutoConnectOrderPartner($localOrderId) {
			if (empty($localOrderId)) {
				throw new InvalidArgumentException('Local order Id must not be empty');
			}

			$partnerMatch = null;
			$localPartnerData = $this->_getLocalPartnerDataForOrder($localOrderId);

			if (!$localPartnerData->exists()) {
				throw new LocalPartnerNotFoundException('orderId', $localOrderId);
			}

			if (!$localPartnerData->hasRemotePartnerCode()) {
				//Fetch all partners, and attempt a match 
				//  with the customer's data
				$remotePartnersData = $this->_getAllRemotePartners();

				//Try find a matching partner within the remote data
				if (!empty($remotePartnersData)) {
					$partnerMatch = $this->_findMatchingRemotePartner(
						$localPartnerData->getData(), 
						$remotePartnersData
					);
				}

				if ($partnerMatch->isMatch()) {
					if ($localPartnerData->isRegisteredLocalUser()) {
						$this->_adapter->connectWithRemotePartner(
							$localPartnerData->getLocalPartnerId(), 
							$partnerMatch->getRemotePartnerData(), 
							$partnerMatch->getRemotePartnerShopBillingAddressData()
						);
					}

					$this->_adapter->connectOrderWithRemotePartner(
						$localOrderId, 
						$partnerMatch->getRemotePartnerData(),
						$partnerMatch->getRemotePartnerShopBillingAddressData()
					);
				}
			} else if ($localPartnerData->isRegisteredLocalUser()) {
				$this->_adapter->connectOrderFromLocalPartnerConnectionInfo($localOrderId);
			}

			return new PartnerConnectionResult($localPartnerData, 
				$partnerMatch);
		}

		private function _getAllRemotePartners() {
			$client = $this->_getCielClientAndLogon();
			return $client->getAllPartners(
				$this->_getGetAllPartnersRequestParams()
			);
		}

		private function _getGetAllPartnersRequestParams() {
			return (new GetAllPartnersRequestParameters())
				->setIncludeAddresses(true)
				->setIncludeOnlyUnblockedPartners(true);
		}

		private function _findMatchingRemotePartner(array $customerData, array $remotePartnersData) {
			return $this
				->_getRemotePartnerFinder($customerData)
				->findMatchingRemotePartner($remotePartnersData);
		}

		private function _getRemotePartnerFinder(array $customerData) {
			return (new CielErpRemotePartnerFinder($customerData))
				->setUsePhoneForPartnerMatching($this->_usePhoneForPartnerMatching());
		}

		public function exportLocalOrderPartner($localOrderId) {
			if (empty($localOrderId)) {
				throw new InvalidArgumentException('Local partner Id must not be empty');
			}

			$exportResult = null;
			$localPartnerData = $this->_getLocalPartnerDataForOrder($localOrderId);
			
			if (!$localPartnerData->exists()) {
				throw new LocalPartnerNotFoundException('orderId', $localOrderId);
			}

			if (!$localPartnerData->hasRemotePartnerCode()) {
				$exportResult = $this->_createRemotePartnerFromLocalPartnerData($localPartnerData);
			} else {
				$exportResult = $this->_updateRemotePartnerFromLocalPartnerData($localPartnerData);
			}

			if ($localPartnerData->isRegisteredLocalUser()) {
				$this->_adapter->connectWithRemotePartner(
					$localPartnerData->getLocalPartnerId(), 
					$exportResult->getRemotePartnerData(), 
					$exportResult->getRemotePartnerShopBillingAddressData()
				);
			}

			$this->_adapter->connectOrderWithRemotePartner(
				$localOrderId, 
				$exportResult->getRemotePartnerData(),
				$exportResult->getRemotePartnerShopBillingAddressData());
		}

		private function _getLocalPartnerDataForOrder($localOrderId) {
			return new LocalPartnerData($this->_adapter->getPartnerDataForOrder($localOrderId));
		}

		private function _createRemotePartnerFromLocalPartnerData(LocalPartnerData $localPartnerData) {
			$remotePartnerData = $this->_getRemotePartnerDataFromLocalCustomerData($localPartnerData->getData(), true);
			$remotePartnerId = $this->_createRemotePartner($remotePartnerData);

			if(!empty($remotePartnerId)) {
				$remotePartnerData = $this->_getRemotePartnerById($remotePartnerId);
				$remotePartnerShopBillingAddressData = $this->_selectRemotePartnerBillingAddressData($localPartnerData, 
					$remotePartnerData);

				return new LocalPartnerExportResult($remotePartnerData, 
					$remotePartnerShopBillingAddressData);
			} else {
				throw new LocalPartnerExportFailedException('create');
			}
		}

		private function _createRemotePartner(array $remoteData) {
			$client = $this->_getCielClientAndLogon();
			return $client->addPartner(
				$this->_getAddPartnerRequestParams(
					$remoteData
				)
			);
		}

		private function _selectRemotePartnerBillingAddressData(LocalPartnerData $localPartnerData, array $remotePartnerData) {
			return $this->_createRemotePartnerBillingAddressDataSelector($localPartnerData)
				->selectRemoteBillingAddressData($remotePartnerData);
		}

		private function _createRemotePartnerBillingAddressDataSelector(LocalPartnerData $localPartnerData) {
			return (new RemotePartnerBillingAddressDataSelector($localPartnerData))
				->setUsePhoneForPartnerMatching($this->_usePhoneForPartnerMatching());
		}

		private function _getAddPartnerRequestParams(array $remoteData) {
			return (new AddPartnerRequestParameters())
				->setPartner($remoteData);
		}

		private function _getRemotePartnerById($remotePartnerId) {
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

		private function _updateRemotePartnerFromLocalPartnerData(LocalPartnerData $localPartnerData) {
			$remotePartnerData = $this->_getRemotePartnerDataFromLocalCustomerData($localPartnerData->getData(), false);
			$updateResult = $this->_updateRemotePartner($remotePartnerData);

			if ($updateResult) {
				$remotePartnerData = $this->_getRemotePartnerByCode($localPartnerData->getRemotePartnerCode());
				$remotePartnerShopBillingAddressData = $this->_selectRemotePartnerBillingAddressData($localPartnerData, 
					$remotePartnerData);

				return new LocalPartnerExportResult($remotePartnerData, 
					$remotePartnerShopBillingAddressData);
			} else {
				throw new LocalPartnerExportFailedException('update');
			}
		}

		private function _getRemotePartnerDataFromLocalCustomerData(array $customerData, $exportAddressAsDefault) {
			return $this->_getLocalToRemotePartnerDataMarshaller($customerData, $exportAddressAsDefault)
				->getRemotePartnerData();
		}

		private function _getLocalToRemotePartnerDataMarshaller(array $customerData, $exportAddressAsDefault) {
			return (new LocalToRemotePartnerDataMarshaller($customerData))
				->setMarshalAddressAsDefaultRemotePartnerAddress($exportAddressAsDefault)
				->setUsePhoneForPartnerMatching($this->_usePhoneForPartnerMatching());
		}

		private function _updateRemotePartner(array $remoteData) {
			$client = $this->_getCielClientAndLogon();
			return $client->updatePartner(
				$this->_getUpdatePartnerRequestParams(
					$remoteData
				)
			);
		}

		private function _getUpdatePartnerRequestParams(array $remoteData) {
			$propertyPairsToUpdate = $this
				->_getPartnerPropertyPairsForUpdate($remoteData);
			$addressesToUpdate = $this
				->_getPartnerAddressesToUpdate($remoteData);

			return (new UpdatePartnerRequestParameters())
				->setPartnerCode($remoteData['Code'])
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

		private function _getRemotePartnerByCode($remotePartnerCode) {
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

		private function _usePhoneForPartnerMatching() {
			return $this->_storeBinding
				->getConfig()
				->usePhoneForPartnerMatching();
		}
	}
}