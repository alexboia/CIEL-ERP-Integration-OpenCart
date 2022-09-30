<?php
namespace Ciel\Api\Integration\Partners {

    use Ciel\Api\CielLogger;
    use Ciel\Api\Exception\LocalPartnerExportFailedException;
	use Ciel\Api\Exception\LocalPartnerNotFoundException;
	use Ciel\Api\Integration\Binding\CielErpToStoreBinding;
	use Ciel\Api\Integration\Partners\Providers\CielErpLocalPartnerAdapter;
    use Ciel\Api\Integration\Partners\RemoteDataProviders\RemotePartnerDataProvider;
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

		/**
		 * @var RemotePartnerDataProvider
		 */
		private $_remoteDataProvider;

		/**
		 * @var CielLogger
		 */
		private $_logger;

		public function __construct(CielErpToStoreBinding $storeBinding, 
			CielErpLocalPartnerAdapter $adapter) {
			$this->_storeBinding = $storeBinding;
			$this->_adapter = $adapter;
			$this->_remoteDataProvider = new RemotePartnerDataProvider($storeBinding->getCielClientFactory());
			$this->_logger = $storeBinding->getLogger();
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
			$localCustomerData = $this->_getLocalPartnerData($localPartnerId);
			
			if (!$localCustomerData->exists()) {
				throw new LocalPartnerNotFoundException('id', $localPartnerId);
			}

			if (!$localCustomerData->hasRemotePartnerCode()) {
				$exportResult = $this->_createRemotePartnerFromLocalPartnerData($localCustomerData);
			} else {
				$exportResult = $this->_updateRemotePartnerFromLocalPartnerData($localCustomerData);
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
			$localCustomerData = $this->_getLocalPartnerDataForOrder($localOrderId);

			if (!$localCustomerData->exists()) {
				throw new LocalPartnerNotFoundException('orderId', $localOrderId);
			}

			if (!$localCustomerData->hasRemotePartnerCode()) {
				//Fetch all partners, and attempt a match 
				//  with the customer's data
				$remotePartnersData = $this->_getAllRemotePartners();

				//Try find a matching partner within the remote data
				if (!empty($remotePartnersData)) {
					$partnerMatch = $this->_findMatchingRemotePartner(
						$localCustomerData->getData(), 
						$remotePartnersData
					);
				}

				if ($partnerMatch->isMatch()) {
					if ($localCustomerData->isRegisteredLocalUser()) {
						$this->_adapter->connectWithRemotePartner(
							$localCustomerData->getLocalPartnerId(), 
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
			} else if ($localCustomerData->isRegisteredLocalUser()) {
				$this->_adapter->connectOrderFromLocalPartnerConnectionInfo($localOrderId);
			}

			return new PartnerConnectionResult($localCustomerData, 
				$partnerMatch);
		}

		private function _getAllRemotePartners() {
			return $this->_remoteDataProvider
				->getAllRemotePartners();
		}

		private function _findMatchingRemotePartner(array $localCustomerData, array $remotePartnersData) {
			$matchingRemotePartnerData = $this->_findMatchingRemotePartnerData($localCustomerData, 
				$remotePartnersData);

			if (!empty($matchingRemotePartnerData)) {
				$matchingRemotePartnerAddressData = $this->_findMatchingRemotePartnerAddressData($localCustomerData, 
					$matchingRemotePartnerData);
			} else {
				$matchingRemotePartnerData = array();
				$matchingRemotePartnerAddressData = array();
			}

			return new RemotePartnerMatch($matchingRemotePartnerData, 
				$matchingRemotePartnerAddressData);
		}

		private function _findMatchingRemotePartnerData(array $localCustomerData, array $remotePartnersData) {
			$finder = $this->_getRemotePartnerFinder($localCustomerData);
			return $finder->findRemotePartner($remotePartnersData);
		}

		private function _getRemotePartnerFinder(array $localCustomerData) {
			$searchInfo = $this->_buildRemotePartnerSearchInfo($localCustomerData);
			$finder = new RemotePartnerFinder($searchInfo, $this->_logger);
			return $finder
				->setUsePhoneForPartnerMatching($this->_usePhoneForPartnerMatching())
				->setUseNameForPartnerMatching($this->_useNameForPartnerMatching());
		}

		private function _buildRemotePartnerSearchInfo(array $localCustomerData) {
			$builder = new RemotePartnerSearchInfoBuilder($localCustomerData);
			return $builder->buildSearchData();
		}

		private function _findMatchingRemotePartnerAddressData(array $localCustomerData, array $remotePartnerData) {
			$finder = $this->_getRemotePartnerAddressFinder($localCustomerData);
			return $finder->findRemotePartnerAddress($remotePartnerData);
		}

		private function _getRemotePartnerAddressFinder(array $localCustomerData) {
			$searchInfo = $this->_buildRemotePartnerAddressSearchInfo($localCustomerData);
			$finder = new RemotePartnerAddressFinder($searchInfo, $this->_logger);
			return $finder;
		}

		private function _buildRemotePartnerAddressSearchInfo(array $localCustomerData) {
			$builder = new RemotePartnerAddressSearchInfoBuilder($localCustomerData);
			return $builder->buildSearchData();
		}

		public function exportLocalOrderPartner($localOrderId) {
			if (empty($localOrderId)) {
				throw new InvalidArgumentException('Local partner Id must not be empty');
			}

			$exportResult = null;
			$localCustomerData = $this->_getLocalPartnerDataForOrder($localOrderId);
			
			if (!$localCustomerData->exists()) {
				throw new LocalPartnerNotFoundException('orderId', $localOrderId);
			}

			if (!$localCustomerData->hasRemotePartnerCode()) {
				$exportResult = $this->_createRemotePartnerFromLocalPartnerData($localCustomerData);
			} else {
				$exportResult = $this->_updateRemotePartnerFromLocalPartnerData($localCustomerData);
			}

			if ($localCustomerData->isRegisteredLocalUser()) {
				$this->_adapter->connectWithRemotePartner(
					$localCustomerData->getLocalPartnerId(), 
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

		private function _createRemotePartnerFromLocalPartnerData(LocalPartnerData $localCustomerData) {
			$remotePartnerData = $this->_getRemotePartnerDataFromLocalCustomerData($localCustomerData->getData(), true);
			$remotePartnerId = $this->_createRemotePartner($remotePartnerData);

			if(!empty($remotePartnerId)) {
				$remotePartnerData = $this->_getRemotePartnerById($remotePartnerId);
				$remotePartnerShopBillingAddressData = $this->_findMatchingRemotePartnerAddressData(
					$localCustomerData->getData(), 
					$remotePartnerData
				);

				return new LocalPartnerExportResult($remotePartnerData, 
					$remotePartnerShopBillingAddressData);
			} else {
				throw new LocalPartnerExportFailedException('create');
			}
		}

		private function _createRemotePartner(array $remoteData) {
			return $this->_remoteDataProvider
				->createRemotePartner($remoteData);
		}

		private function _getRemotePartnerById($remotePartnerId) {
			return $this->_remoteDataProvider
				->getRemotePartnerById($remotePartnerId);
		}

		private function _updateRemotePartnerFromLocalPartnerData(LocalPartnerData $localCustomerData) {
			$remotePartnerData = $this->_getRemotePartnerDataFromLocalCustomerData($localCustomerData->getData(), false);
			$updateResult = $this->_updateRemotePartner($remotePartnerData);

			if ($updateResult) {
				$remotePartnerData = $this->_getRemotePartnerByCode($localCustomerData->getRemotePartnerCode());
				$remotePartnerShopBillingAddressData = $this->_findMatchingRemotePartnerAddressData(
					$localCustomerData->getData(), 
					$remotePartnerData
				);

				return new LocalPartnerExportResult($remotePartnerData, 
					$remotePartnerShopBillingAddressData);
			} else {
				throw new LocalPartnerExportFailedException('update');
			}
		}

		private function _getRemotePartnerDataFromLocalCustomerData(array $localCustomerData, $exportAddressAsDefault) {
			return $this->_getLocalToRemotePartnerDataMarshaller($localCustomerData, $exportAddressAsDefault)
				->getRemotePartnerData();
		}

		private function _getLocalToRemotePartnerDataMarshaller(array $localCustomerData, $exportAddressAsDefault) {
			$marshaller = new LocalToRemotePartnerDataMarshaller($localCustomerData);
			return $marshaller->setMarshalAddressAsDefaultRemotePartnerAddress($exportAddressAsDefault);
		}

		private function _updateRemotePartner(array $remoteData) {
			return $this->_remoteDataProvider
				->updateRemotePartner($remoteData['Code'], 
					$remoteData);
		}

		private function _getRemotePartnerByCode($remotePartnerCode) {
			return $this->_remoteDataProvider
				->getRemotePartnerByCode($remotePartnerCode);
		}

		private function _usePhoneForPartnerMatching() {
			return $this->_storeBinding
				->usePhoneForPartnerMatching();
		}

		private function _useNameForPartnerMatching() {
			return $this->_storeBinding
				->useNameForPartnerMatching();
		}
	}
}