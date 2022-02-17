<?php
namespace Ciel\Api\Integration\Binding {
	use Ciel\Api\CielClient;
	use Ciel\Api\Data\ArticleSelectionType;
	use Ciel\Api\Data\DocumentStatusType;
	use Ciel\Api\Data\DocumentType;
	use Ciel\Api\Data\WarehouseType;
	use Ciel\Api\Exception\StoreNotBoundException;
	use Ciel\Api\Exception\VatQuotaValueNotSupportedException;
	use Ciel\Api\Exception\WebserviceCommunicationException;
	use Ciel\Api\Exception\WebserviceErrorException;
	use Ciel\Api\Integration\Binding\Providers\CielErpToStoreBindingAdapter;
	use Ciel\Api\Request\Parameters\AddArticleRequestParameters;
	use Ciel\Api\Request\Parameters\AddAssociationRequestParameters;
	use Ciel\Api\Request\Parameters\AddDocumentSeriesRequestParameters;
	use Ciel\Api\Request\Parameters\GetAllArticlesRequestParameters;
	use Ciel\Api\Request\Parameters\GetAllDocumentSeriesAssociationRequestParameters;
	use Ciel\Api\Request\Parameters\GetAllDocumentSeriesRequestParameters;
	use Ciel\Api\Request\Parameters\GetAllWarehousesRequestParameters;
	use Ciel\Api\Request\Parameters\GetArticleByCodeRequestParameters;
	use Ciel\Api\Request\Parameters\SelectFromViewRequestParameters;
    use InvalidArgumentException;

	class CielErpToStoreBinding {
		const ERR_CONNECTION_TEST_NOT_BOUND = -1;
		
		const ERR_CONNECTION_TEST_FAIL_ENDPOINT = -2;

		const ERR_CONNECTION_TEST_FAIL_AUTH = -3;

		const ERR_CONNECTION_TEST_OK = 0;

		/**
		 * @var \Ciel\Api\Integration\Binding\Providers\CielErpToStoreBindingAdapter
		 */
		private $_adapter;

		/**
		 * @var \Ciel\Api\CielClient
		 */
		private $_activeCielClient;

		private $_data = null;

		private $_articleServiceTypeId = null;

		public function __construct(CielErpToStoreBindingAdapter $adapter) {
			$this->_adapter = $adapter;
		}

		private function _getCachedWarehouses() {
			return $this->_adapter
				->getCache()
				->get('ciel_warehouses');
		}

		private function _setCachedWarehouses($warehouses) {
			$cacheDuration = $this
				->getConfig()
				->getWarehousesCacheDuration();

			$this->_adapter
				->getCache()
				->set('ciel_warehouses', $warehouses, $cacheDuration);
		}

		private function _getCachedVatQuotas() {
			return $this->_adapter
				->getCache()
				->get('ciel_vat_quotas');
		}

		private function _setCachedVatQuotas($vatQuotas) {
			$cacheDuration = $this
				->getConfig()
				->getVatQuotasCacheDuration();

			$this->_adapter
				->getCache()
				->set('ciel_vat_quotas', $vatQuotas, $cacheDuration);
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

		private function _getDocumentSeriesByPrefix(CielClient $client, $prefix) {
			$found = null;
			$prefix = strtoupper($prefix);
			$allSeries = $client->getAllDocumentSeries(new GetAllDocumentSeriesRequestParameters());

			if (!empty($allSeries)) {
				foreach ($allSeries as $series) {
					if (strtoupper($series['Prefix']) == $prefix) {
						$found = $series;
						break;
					}
				}
			}

			return $found;
		}

		private function _createDocumentSeriesWithPrefix(CielClient $client, $prefix) {
			$addSeriesParams = (new AddDocumentSeriesRequestParameters())
				->setBlocked(false)
				->setPrefix(strtoupper($prefix))
				->setStartNumber(1)
				->setNextNumber(1)
				->setEndNumber(1000000)
				->setDescription("Automatically created from connected e-shop");

			return $client->addDocumentSeries($addSeriesParams);
		}

		private function _getAssociatedSeriesIdsForDocumentTypeIds(CielClient $client) {
			$seriesIdsPerDocTypeIds = array();
			$documentSeriesAssociations = $client->getAllDocumentSeriesAssociations(new GetAllDocumentSeriesAssociationRequestParameters());

			if (!empty($documentSeriesAssociations)) {
				foreach ($documentSeriesAssociations as $seriesAssoc) {
					$documentTypeId = $seriesAssoc['DocumentLookupTypeId'];
					$seriesId = $seriesAssoc['SeriesConfigurationId'];
					if (!isset($seriesIdsPerDocTypeIds[$documentTypeId])) {
						$seriesIdsPerDocTypeIds[$documentTypeId] = array();
					}

					$seriesIdsPerDocTypeIds[$documentTypeId][] = $seriesId;
				}
			}

			return $seriesIdsPerDocTypeIds;
		}

		private function _isAssociatedWithDocumentTypeId(CielClient $client, $seriesId, $documentTypeId) {
			$associated = false;
			$seriesIdsPerDocTypeIds = $this->_getAssociatedSeriesIdsForDocumentTypeIds($client);
			
			if (!empty($seriesIdsPerDocTypeIds) && isset($seriesIdsPerDocTypeIds[$documentTypeId])) {
				$associated = in_array($seriesId, $seriesIdsPerDocTypeIds[$documentTypeId]);
			}

			return $associated;
		}

		private function _getDocumentTypeId(CielClient $client, $documentType) {
			return $documentType == DocumentType::SaleInvoice 
				? 42 
				: 43;
		}

		private function _associateSeriesWithDocumentTypeIfNeeded(CielClient $client, $seriesId, $documentType) {
			$documentTypeId = $this->_getDocumentTypeId($client, $documentType);
			if (!$this->_isAssociatedWithDocumentTypeId($client, $seriesId, $documentTypeId)) {
				$this->_associateSeriesWithDocumentType($client, 
					$seriesId, 
					$documentTypeId);
			}
		}

		private function _associateSeriesWithDocumentType(CielClient $client, $seriesId, $documentTypeId) {
			$addAsssociationParams = (new AddAssociationRequestParameters())
				->setDocumentLookupTypeId($documentTypeId)
				->setSeriesConfigurationId($seriesId)
				->setAutomaticGeneration(true)
				->setIsDefault(false);

			return $client->addDocumentSeriesAssociation($addAsssociationParams);
		}

		private function _createAndAssociateDocumentSeriesIfNeeded($prefix, $documentType) {
			$client = $this->getAmbientCielClient(true);
			$series = $this->_getDocumentSeriesByPrefix($client, $prefix);

			if ($series != null) {
				$seriesId = $series['Id'];
			} else {
				$seriesId = $this->_createDocumentSeriesWithPrefix($client, $prefix);
			}

			if ($seriesId > 0) {
				$this->_associateSeriesWithDocumentTypeIfNeeded($client, $seriesId, $documentType);
			}
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

		public function setupDocumentSeriesIfNeeded() {
			$this->_loadIfNeeded();
			if ($this->_hasConnectionInfo()) {
				$issueDocumentType = $this->getIssueDocumentType();
				$issueWithDocumentSeries = $this->getIssueDocumentWithSeries();

				if (!empty($issueWithDocumentSeries) && !empty($issueDocumentType)) {
					$this->_createAndAssociateDocumentSeriesIfNeeded($issueWithDocumentSeries, $issueDocumentType);
				}

				$this->_setDocumentSeriesSetup(true);
				$this->save();
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
				if ($this->_activeCielClient == null) {
					$this->_activeCielClient = $this->createCielClient($this->getEndpoint());
				}

				if ($autologon && !$this->_activeCielClient->isAuthenticated()) {
					$this->_activeCielClient->logon($this->getUserName(), 
						$this->getPassword(), 
						$this->getSociety());
				}

				return $this->_activeCielClient;
			} else {
				throw new StoreNotBoundException();
			}
		}

		public function createCielClient($endpoint) {
			if (empty($endpoint)) {
				throw new InvalidArgumentException('Endpoint may not be empty');
			}

			$sessionProvider = $this->_adapter->getClientSessionProvider();
			$sessionProvider->setup();

			$clientInstance = new CielClient($endpoint);
			$clientInstance->setSessionProvider($sessionProvider);

			return $clientInstance;
		}

		public function closeCielClient() {
			if ($this->_activeCielClient != null) {
				if ($this->_activeCielClient->isAuthenticated()) {
					$this->_activeCielClient->logout();
				}
				$this->_activeCielClient = null;
			}
		}

		public function testConnection() {
			$this->_loadIfNeeded();
			if ($this->_hasConnectionInfo()) {
				$result = null;
				try {
					$client = $this->createCielClient($this->getEndpoint());
					$client->logon($this->getUserName(), 
						$this->getPassword(), 
						$this->getSociety());

					if ($client->isAuthenticated()) {
						$client->logout();
						$result = self::ERR_CONNECTION_TEST_OK;
					} else {
						$result = self::ERR_CONNECTION_TEST_FAIL_AUTH;
					}
				} catch (WebserviceCommunicationException $exc) {
					$result = self::ERR_CONNECTION_TEST_FAIL_ENDPOINT;
				} catch (WebserviceErrorException $exc) {
					$result = self::ERR_CONNECTION_TEST_FAIL_AUTH;
				}

				return $result;
			} else {
				return self::ERR_CONNECTION_TEST_NOT_BOUND;
			}
		}

		public function getAvailableWarehouses() {
			$this->_loadIfNeeded();
			if ($this->_hasConnectionInfo()) {
				$warehouses = $this->_getCachedWarehouses();
				if ($warehouses === false || $warehouses === null) {
					$client = $this->getAmbientCielClient(true);
					$warehousesRaw = $client->getAllWarehouses(new GetAllWarehousesRequestParameters());

					$warehouses = array();
					if (!empty($warehousesRaw)) {
						foreach ($warehousesRaw as $w) {
							$wDropdownInfo = $this->_createWarehouseDropdownInfo($w);
							if ($wDropdownInfo != null) {
								$warehouses[$w['Id']] = $wDropdownInfo;
							}
						}
					}

					$this->_setCachedWarehouses($warehouses);
				}

				return $warehouses;
			} else {
				throw new StoreNotBoundException();
			}
		}

		public function getAvailableVatQuotas() {
			$this->_loadIfNeeded();
			if ($this->_hasConnectionInfo()) {
				$quotas = $this->_getCachedVatQuotas();
				if ($quotas === false || $quotas === null) {
					$client = $this->getAmbientCielClient(true);
					$quotasRaw = $client->selectFromView((new SelectFromViewRequestParameters())
						->setViewName('CIEL_PS_Quotas'));

					$quotas = array();
					if (!empty($quotasRaw) && is_array($quotasRaw)) {
						foreach ($quotasRaw as $q) {
							$quotas[$q['name']] = $q['value'];
						}
					}

					$this->_setCachedVatQuotas($quotas);
				}

				return $quotas;
			} else {
				throw new StoreNotBoundException();
			}
		}

		private function _createWarehouseDropdownInfo($rawWarehouse) {
			$typeId = $rawWarehouse['WarehouseTypeName'];
			$type = WarehouseType::parse($typeId);

			if ($type != null) {
				return array(
					'code' => $rawWarehouse['Code'],
					'name' => $rawWarehouse['Name'],
					'type' => $typeId,
					'displayLabel' => $this->_formatWarehouseName( 
						$rawWarehouse['Name'], 
						$rawWarehouse['Code'], 
						$typeId),
					'properties' => $type->asPlainObject()
				);
			} else {
				return null;
			}
		}

		private function _formatWarehouseName($name, $code, $typeId) {
			$format = $this->getConfig()
				->getWarehouseDisplayLabelFormat();

			return str_replace(array('%name', '%code', '%type_id'), 
				array($name, $code, $typeId), 
				$format);
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

		public function getConfig() {
			return $this->_adapter->getConfig();
		}
	}
}