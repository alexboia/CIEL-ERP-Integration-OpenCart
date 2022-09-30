<?php
namespace Ciel\Api\Integration\Binding {
	use Ciel\Api\CielClient;
    use Ciel\Api\CielConnectionTesterService;
    use Ciel\Api\CielConnectionTestResult;
    use Ciel\Api\CielLogger;
    use Ciel\Api\Data\ArticleSelectionType;
	use Ciel\Api\Data\DocumentStatusType;
	use Ciel\Api\Data\WarehouseType;
    use Ciel\Api\DefaultCielClientFactory;
    use Ciel\Api\Exception\StoreNotBoundException;
	use Ciel\Api\Exception\VatQuotaValueNotSupportedException;
	use Ciel\Api\Integration\Binding\Providers\CielErpToStoreBindingAdapter;
    use Ciel\Api\Integration\Binding\RemoteDataProviders\VatQuotaDataProvider;
    use Ciel\Api\Integration\Binding\RemoteDataProviders\WarehouseDataProvider;
    use Ciel\Api\NullCielLogger;
    use Ciel\Api\Request\Parameters\AddArticleRequestParameters;
	use Ciel\Api\Request\Parameters\GetAllArticlesRequestParameters;
	use Ciel\Api\Request\Parameters\GetArticleByCodeRequestParameters;
    use InvalidArgumentException;

	class CielErpToStoreBinding {
		const ERR_CONNECTION_TEST_NOT_BOUND = 
			CielConnectionTestResult::ERR_CONNECTION_TEST_NOT_BOUND;
		
		const ERR_CONNECTION_TEST_FAIL_ENDPOINT = 
			CielConnectionTestResult::ERR_CONNECTION_TEST_FAIL_ENDPOINT;

		const ERR_CONNECTION_TEST_FAIL_AUTH = 
			CielConnectionTestResult::ERR_CONNECTION_TEST_FAIL_AUTH;

		const ERR_CONNECTION_TEST_OK = 
			CielConnectionTestResult::ERR_CONNECTION_TEST_OK;

		/**
		 * @var \Ciel\Api\Integration\Binding\Providers\CielErpToStoreBindingAdapter
		 */
		private $_adapter;

		/**
		 * @var \Ciel\Api\CielClient
		 */
		private $_ambientCielClient;

		private $_data = null;

		private $_articleServiceTypeId = null;

		/**
		 * @var DefaultCielClientFactory
		 */
		private $_cielClientFactory;

		/**
		 * @var CielLogger
		 */
		private $_logger;

		public function __construct(CielErpToStoreBindingAdapter $adapter) {
			$this->_adapter = $adapter;
			$this->_logger = new NullCielLogger();
			$this->_cielClientFactory = new DefaultCielClientFactory(
				$adapter->getClientSessionProvider(),
				new CielErpToStoreBindingAmbientConnectionInfoProvider(
					$this
				)
			);
		}

		private function _loadIfNeeded() {
			if ($this->_data === null) {
				$this->_data = $this->_adapter->getBindingData();
				if (!is_array($this->_data)) {
					$this->_data = array();
				}

				$this->_data = array_merge($this->_getDefaultBindingData(), 
					$this->_data);
			}
		}

		private function _getDefaultBindingData() {
			return array(
				'endpoint' => null,
				'credentials' => array(
					'username' => null,
					'password' => null,
					'society' => null
				),
				'timeoutSeconds' => null,
				'warehouse' => array(
					'id' => null,
					'code' => null,
					'type' => null
				),
				'orderPolicy' => array(
					'issueDocumentType' => null,
					'issueDocumentWhen' => array(),
					'cancelDocumentWhen' => array(),
					'issueDocumentWithStatus' => DocumentStatusType::Temporary,
					'issueDocumentWithSeries' => null,
					'issueDocumentWithDueDays' => 15,
					'useExtendedCompanyBillingFields' => true,
					'shippingVatQuotaValue' => null,
					'shippingVatQuotaName' => null
				),
				'articlesPolicy' => array(
					'matchVariations' => false,
					'syncPrices' => true,
					'syncStocks' => true
				),
				'stockPolicy' => array(
					'stockUpdateMode' => null,
					'useStockType' => 'physical'
				),
				'_shippingSetup' => false,
				'_documentSeriesSetup' => false
			);
		}

		private function _getArticleServiceTypeId(CielClient $client) {
			if ($this->_articleServiceTypeId === null) {
				$typeId = false;
				$getSampleServiceArticleParams = (new GetAllArticlesRequestParameters())
					->setArticleType(ArticleSelectionType::ServicesOnly)
					->setPagination(1, 1);

				$result = $client->getAllArticles($getSampleServiceArticleParams);
				if (!empty($result) && count($result) == 1 && isset($result[0]['ArticleTypeId'])) {
					$typeId = intval($result[0]['ArticleTypeId']);
				}

				if ($typeId !== false) {
					$this->_articleServiceTypeId = $typeId;
				}
			}

			return $this->_articleServiceTypeId;
		}

		private function _getArticleByCode(CielClient $client, $code) {
			$getArticleParams = (new GetArticleByCodeRequestParameters())
				->setCode($code);
			return $client->getArticleByCode($getArticleParams);
		}

		private function _createShippingArticleIfNeeded($vatQuotaName) {
			$client = $this->getAmbientCielClient(true);
			$code = $this->getShippingArticleCode();

			$existingArticle = $this->_getArticleByCode($client, $code);
			if (empty($existingArticle)) {
				$addArticleParams = (new AddArticleRequestParameters())
					->setArticle(array(
						'Code' => $code,
						'VatOutQuotaName' => $vatQuotaName,
						'Name' => 'Transport / Livrare',
						'ArticleTypeId' => $this->_getArticleServiceTypeId($client)
					));

				$client->addArticle($addArticleParams);
				$existingArticle = $this->_getArticleByCode($client, $code);
			}

			return $existingArticle;
		}

		private function _createDiscountArticleIfNeeded($vatQuotaValue) {
			$client = $this->getAmbientCielClient(true);
			$code = $this->getDiscountArticleCode($vatQuotaValue);
			$name = $this->getDiscountArticleName($vatQuotaValue);

			$existingArticle = $this->_getArticleByCode($client, $code);
			if (empty($existingArticle)) {
				$vatQuotaName = $this->_lookupVatQuotaNameByValue($vatQuotaValue);
				if (!empty($vatQuotaName)) {
					$addArticleParams = (new AddArticleRequestParameters())
						->setArticle(array(
							'Code' => $code,
							'VatOutQuotaName' => $vatQuotaName,
							'Name' => $name,
							'ArticleTypeId' => $this->_getArticleServiceTypeId($client)
						));

					$client->addArticle($addArticleParams);
					$existingArticle = $this->_getArticleByCode($client, $code);
				} else {
					throw new VatQuotaValueNotSupportedException($vatQuotaValue);
				}
			}

			return $existingArticle;
		}

		private function _lookupVatQuotaNameByValue($vatQuotaValue) {
			$allVatQuotas = $this->getAvailableVatQuotas();
			foreach ($allVatQuotas as $name => $value) {
				if ($value == $vatQuotaValue) {
					return $name;
				}
			}
			return null;
		}

		private function _setOption($key, $value) {
			$this->_loadIfNeeded();
			$this->_data[$key] = $value;
		}

		private function _getOption($key, $default) {
			$this->_loadIfNeeded();
			$optionValue = isset($this->_data[$key]) 
				? $this->_data[$key] 
				: $default;

			$this->_data[$key] = $optionValue;
			return $optionValue;
		}

		private function _setShippingSetup($val) {
			$this->_setOption('_shippingSetup', $val);
			return $this;
		}

		private function _getShippingSetup() {
			return $this->_getOption('_shippingSetup', false);
		}

		private function _setDocumentSeriesSetup($val) {
			$this->_setOption('_documentSeriesSetup', $val);
		}

		private function _getDocumentSeriesSetup() {
			return $this->_getOption('_documentSeriesSetup', false);
		}

		public function setEndpoint($endpoint) {
			$this->_setOption('endpoint', $endpoint);
			return $this;
		}

		public function getEndpoint() {
			return $this->_getOption('endpoint', null);
		}

		public function setCredentials($userName, $password, $society) {
			$credentials = array(
				'username' => $userName,
				'password' => $password,
				'society' => $society
			);
			$this->_setOption('credentials', $credentials);
			return $this;
		}

		public function getCredentials() {
			return $this->_getOption('credentials', array(
				'username' => null,
				'password' => null,
				'society' => null
			));
		}

		public function setTimeoutSeconds($timeoutSeconds) {
			$this->_setOption('timeoutSeconds', $timeoutSeconds);
		}

		public function getTimeoutSeconds() {
			return $this->_getOption('timeoutSeconds', 10);
		}

		public function getUserName() {
			$credentials = $this->getCredentials();
			return $credentials['username'];
		}

		public function getPassword() {
			$credentials = $this->getCredentials();
			return $credentials['password'];
		}

		public function getSociety() {
			$credentials = $this->getCredentials();
			return $credentials['society'];
		}

		public function setWarehouse($id, $code, $type) {
			$warehouse = array(
				'id' => $id,
				'type' => $type,
				'code' => $code
			);

			$this->_setOption('warehouse', $warehouse);
			return $this;
		}

		public function getWarehouse() {
			return $this->_getOption('warehouse', array(
				'id' => null,
				'code' => null,
				'type' => null
			));
		}

		public function getWarehouseId() {
			$warehouse = $this->getWarehouse();
			return $warehouse['id'];
		}

		public function getWarehouseCode() {
			$warehouse = $this->getWarehouse();
			return $warehouse['code'];
		}

		public function getWarehouseType() {
			$warehouse = $this->getWarehouse();
			return $warehouse['type'];
		}

		public function isBoundToEnDetailWarehouse() {
			$type = $this->_getWarehouseType();
			return $type != null && $type->enDetail();
		}

		public function isBoundToEnGrossWarehouse() {
			$type = $this->_getWarehouseType();
			return $type != null && $type->enGross();
		}

		/**
		 * @return WarehouseType|null
		 */
		private function _getWarehouseType() {
			$typeCode = $this->getWarehouseType();
			return !empty($typeCode) 
				? WarehouseType::parse($typeCode) 
				: null;
		}

		public function setOrderPolicy($issueDocumentType, 
			array $issueDocumentWhen, 
			array $cancelDocumentWhen, 
			$issueDocumentWithStatus,
			$issueDocumentWithSeries,
			$issueDocumentWithDueDays,
			$useExtendedCompanyBillingFields,
			$shippingVatQuotaValue, 
			$shippingVatQuotaName) {

			$this->_setOption('orderPolicy', array(
				'issueDocumentType' => $issueDocumentType,
				'issueDocumentWhen' => $issueDocumentWhen,
				'cancelDocumentWhen' => $cancelDocumentWhen,
				'issueDocumentWithStatus' => $issueDocumentWithStatus,
				'issueDocumentWithSeries' => $issueDocumentWithSeries,
				'issueDocumentWithDueDays' => $issueDocumentWithDueDays,
				'useExtendedCompanyBillingFields' => $useExtendedCompanyBillingFields == true,
				'shippingVatQuotaValue' => $shippingVatQuotaValue,
				'shippingVatQuotaName' => $shippingVatQuotaName
			));

			return $this;
		}

		public function getOrderPolicy() {
			return $this->_getOption('orderPolicy', array(
				'issueDocumentType' => null,
				'issueDocumentWhen' => array(),
				'cancelDocumentWhen' => array(),
				'issueDocumentWithStatus' => DocumentStatusType::Temporary,
				'issueDocumentWithSeries' => null,
				'issueDocumentWithDueDays' => 15,
				'useExtendedCompanyBillingFields' => true,
				'shippingVatQuotaValue' => null,
				'shippingVatQuotaName' => null
			));
		}

		public function getIssueDocumentType() {
			$orderPolicy = $this->getOrderPolicy();
			return isset($orderPolicy['issueDocumentType']) 
				? $orderPolicy['issueDocumentType'] 
				: null;
		}

		public function getIssueDocumentWhen() {
			$orderPolicy = $this->getOrderPolicy();
			return isset($orderPolicy['issueDocumentWhen']) 
				? $orderPolicy['issueDocumentWhen'] 
				: array();
		}

		public function getCancelDocumentWhen() {
			$orderPolicy = $this->getOrderPolicy();
			return isset($orderPolicy['cancelDocumentWhen'])
				? $orderPolicy['cancelDocumentWhen']
				: array();
		}

		public function getIssueDocumentWithStatus() {
			$orderPolicy = $this->getOrderPolicy();
			return isset($orderPolicy['issueDocumentWithStatus'])
				? $orderPolicy['issueDocumentWithStatus'] 
				: DocumentStatusType::Temporary;
		}

		public function getIssueDocumentWithSeries() {
			$orderPolicy = $this->getOrderPolicy();
			return isset($orderPolicy['issueDocumentWithSeries'])
				? $orderPolicy['issueDocumentWithSeries']
				: null;
		}

		public function getIssueDocumentWithDueDays() {
			$orderPolicy = $this->getOrderPolicy();
			return isset($orderPolicy['issueDocumentWithDueDays'])
				? $orderPolicy['issueDocumentWithDueDays']
				: 15;
		}

		public function getShippingVatQuotaName() {
			$orderPolicy = $this->getOrderPolicy();
			return isset($orderPolicy['shippingVatQuotaName']) 
				? $orderPolicy['shippingVatQuotaName'] 
				: null;
		}

		public function getShippingVatQuotaValue() {
			$orderPolicy = $this->getOrderPolicy();
			return isset($orderPolicy['shippingVatQuotaValue']) 
				? $orderPolicy['shippingVatQuotaValue'] 
				: null;
		}

		public function getUseExtendedCompanyBillingFields() {
			$orderPolicy = $this->getOrderPolicy();
			return isset($orderPolicy['useExtendedCompanyBillingFields']) 
				? $orderPolicy['useExtendedCompanyBillingFields'] 
				: false;
		}

		public function setArticlePolicy($matchVariations) {
			$this->_setOption('articlesPolicy', array(
				'matchVariations' => $matchVariations == true
			));
			return $this;
		}

		public function getArticlePolicy() {
			return $this->_getOption('articlesPolicy', array(
				'matchVariations' => false,
				'syncPrices' => true,
				'syncStocks' => true
			));
		}

		public function getMatchArticleVariations() {
			$articlePolicy = $this->getArticlePolicy();
			return isset($articlePolicy['matchVariations']) 
				? $articlePolicy['matchVariations'] 
				: false;
		}

		public function setStockPolicy($stockUpdateMode) {
			$this->_setOption('stockPolicy', array(
				'stockUpdateMode' => $stockUpdateMode
			));
			return $this;
		}

		public function getStockPolicy() {
			return $this->_getOption('stockPolicy', array(
				'stockUpdateMode' => null,
				'useStockType' => 'physical'
			));
		}

		public function getStockUpdateMode() {
			$stockPolicy = $this->getStockPolicy();
			return isset($stockPolicy['stockUpdateMode']) 
				? $stockPolicy['stockUpdateMode'] 
				: null;
		}

		public function save() {
			$this->_loadIfNeeded();
			$this->_adapter->saveBindingData($this->_data);
		}

		public function setupShippingIfNeeded() {
			$this->_loadIfNeeded();
			if ($this->_hasConnectionInfo()) {
				$shippingArticle = $this->_createShippingArticleIfNeeded($this->getShippingVatQuotaName());
				$this->_setShippingSetup(true);
				$this->save();
				return $shippingArticle;
			} else {
				throw new StoreNotBoundException();
			}
		}

		public function setupDiscountForVatQuotaValueIfNeeded($vatQuotaValue) {
			$this->_loadIfNeeded();
			if ($this->_hasConnectionInfo()) {
				$discountArticle = $this->_createDiscountArticleIfNeeded($vatQuotaValue);
				return $discountArticle;
			} else {
				throw new StoreNotBoundException();
			}
		}

		public function clear() {
			$this->_adapter->clearBindingData();
			$this->_allVatQuotas = null;
			$this->_allWarehouses = null;
			$this->_data = null;
		}

		public function refresh() {
			$this->_data = null;
			$this->_loadIfNeeded();
		}

		public function getAmbientCielClient($autologon = true) {
			$this->_loadIfNeeded();
			if ($this->_hasConnectionInfo()) {
				if ($this->_ambientCielClient == null) {
					$this->_ambientCielClient = $this->_cielClientFactory
						->getAmbientCielClient();
				}

				return $this->_ambientCielClient;
			} else {
				throw new StoreNotBoundException();
			}
		}

		public function createCielClient($endpoint, array $options = array()) {
			if (empty($endpoint)) {
				throw new InvalidArgumentException('Endpoint may not be empty');
			}

			return $this->_cielClientFactory
				->createCielClientForEndpointAndOptions($endpoint, 
					$options);
		}

		public function closeAmbientCielClient() {
			if ($this->_ambientCielClient != null) {
				if ($this->_ambientCielClient->isAuthenticated()) {
					$this->_ambientCielClient->logout();
				}
				$this->_ambientCielClient = null;
			}
		}

		public function testConnection() {
			$this->_loadIfNeeded();
			if ($this->_hasConnectionInfo()) {
				$connectionTesterService = $this->createConnectionTesterService();
				return $connectionTesterService->testConnection(
					$this->getEndpoint(),
					$this->getUserName(),
					$this->getPassword(),
					$this->getSociety(),
					$this->getTimeoutSeconds()
				);
			} else {
				return CielConnectionTestResult::ERR_CONNECTION_TEST_NOT_BOUND;
			}
		}

		public function createConnectionTesterService() {
			return new CielConnectionTesterService(
				$this->_cielClientFactory, 
				$this->_logger
			);
		}

		public function getAvailableWarehouses() {
			$this->_loadIfNeeded();
			if ($this->_hasConnectionInfo()) {
				$dataProvider = $this->_getWarehouseDataProvider();
				return $dataProvider->getAvailableWarehouses();
			} else {
				throw new StoreNotBoundException();
			}
		}

		private function _getWarehouseDataProvider() {
			return new WarehouseDataProvider(
				$this->_cielClientFactory, 
				$this->getConfig(), 
				$this->getCache(), 
				$this->_logger
			);
		}

		public function getAvailableVatQuotas() {
			$this->_loadIfNeeded();
			if ($this->_hasConnectionInfo()) {
				$dataProvider = $this->_getVatQuotaDataProvider();
				return $dataProvider->getAvailableVatQuotas();
			} else {
				throw new StoreNotBoundException();
			}
		}

		private function _getVatQuotaDataProvider() {
			return new VatQuotaDataProvider(
				$this->_cielClientFactory, 
				$this->getConfig(), 
				$this->getCache(), 
				$this->_logger
			);
		}

		private function _hasConnectionInfo() {
			return !empty($this->_data['endpoint'])
				&& !empty($this->_data['credentials'])
				&& !empty($this->_data['credentials']['username'])
				&& !empty($this->_data['credentials']['password'])
				&& !empty($this->_data['credentials']['society']);
		}

		private function _hasWarehouseInfo() {
			return !empty($this->_data['warehouse'])
				&& !empty($this->_data['warehouse']['id'])
				&& !empty($this->_data['warehouse']['type']);
		}

		public function isBound() {
			$this->_loadIfNeeded();
			return $this->_hasConnectionInfo()
				&& $this->_hasWarehouseInfo()
				&& $this->_getShippingSetup() == true;
		}

		public function hasConnectionInfo() {
			$this->_loadIfNeeded();
			return $this->_hasConnectionInfo();
		}

		public function isShippingSetup() {
			$this->_loadIfNeeded();
			return $this->_getShippingSetup() == true;
		}

		public function isDocumentSeriesSetup() {
			$this->_loadIfNeeded();
			return $this->_getDocumentSeriesSetup();
		}

		public function getDiscountArticleCode($vatQuotaValue) {
			$format = $this
				->getConfig()
				->getDiscountArticleCodeFormat();

			return str_replace('%quota_value', 
				$vatQuotaValue, 
				$format);
		}

		public function getDiscountArticleName($vatQuotaValue) {
			$format = $this
				->getConfig()
				->getDiscountArticleNameFormat();

			return str_replace('%quota_value', 
				$vatQuotaValue, 
				$format);
		}

		public function getShippingArticleCode() {
			return $this
				->getConfig()
				->getShippingArticleCode();
		}

		public function usePhoneForPartnerMatching() {
			return $this
				->getConfig()
				->usePhoneForPartnerMatching();
		}

		public function useNameForPartnerMatching() {
			return $this
				->getConfig()
				->useNameForPartnerMatching();
		}

		public function getCielClientFactory() {
			return $this->_cielClientFactory;
		}

		public function getConfig() {
			return $this->_adapter->getConfig();
		}

		public function getCache() {
			return $this->_adapter->getCache();
		}

		public function getLogger() {
			return $this->_logger;
		}
	}
}