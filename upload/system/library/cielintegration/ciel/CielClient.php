<?php
namespace Ciel\Api {
	use Ciel\Api\Exception\WebserviceCommunicationException;
	use Ciel\Api\Exception\MissingWebserviceResponseBodyException;
	use Ciel\Api\Exception\RequestAuthenticationRequiredException;
	use Ciel\Api\Exception\WebserviceErrorException;
	use Ciel\Api\Exception\WebserviceResponseFormatException;
	use Ciel\Api\Request\Parameters\AddArticleRequestParameters;
	use Ciel\Api\Request\Parameters\AddAssociationRequestParameters;
	use Ciel\Api\Request\Parameters\AddDocumentSeriesRequestParameters;
	use Ciel\Api\Request\Parameters\AddOrUpdatePartnerRequestParameters;
	use Ciel\Api\Request\Parameters\AddPartnerRequestParameters;
	use Ciel\Api\Request\Parameters\AddSaleInvoiceRequestParameters;
	use Ciel\Api\Request\Parameters\AddSaleOrderRequestParameters;
	use Ciel\Api\Request\Parameters\DeleteDocumentRequestParams;
	use Ciel\Api\Request\Parameters\DeletePartnerByIdRequestParameters;
	use Ciel\Api\Request\Parameters\GetAllArticlesRequestParameters;
	use Ciel\Api\Request\Parameters\GetAllDocumentSeriesAssociationRequestParameters;
	use Ciel\Api\Request\Parameters\GetAllDocumentSeriesRequestParameters;
	use Ciel\Api\Request\Parameters\GetAllPartnersRequestParameters;
	use Ciel\Api\Request\Parameters\GetAllStocksForArticlesRequestParameters;
	use Ciel\Api\Request\Parameters\GetAllWarehousesRequestParameters;
	use Ciel\Api\Request\Parameters\GetArticleByCodeRequestParameters;
	use Ciel\Api\Request\Parameters\GetArticleByIdRequestParameters;
	use Ciel\Api\Request\Parameters\GetLicenseNumberRequestParameters;
	use Ciel\Api\Request\Parameters\GetPartnerByCodeRequestParameters as GetPartnerByCodeRequestParameters;
	use Ciel\Api\Request\Parameters\GetPartnerByIdRequestParameters;
	use Ciel\Api\Request\Parameters\GetStocksForArticleByCodeRequestParameters;
	use Ciel\Api\Request\Parameters\SelectFromViewRequestParameters;
	use Ciel\Api\Request\Parameters\UpdateArticleRequestParameters;
	use Ciel\Api\Request\Parameters\UpdatePartnerRequestParameters;
	use Ciel\Api\Session\CielClientSessionCredentials;
	use Ciel\Api\Session\CielClientSessionProvider;
	use Ciel\Api\Session\InMemoryCielClientSessionProvider;
	use InvalidArgumentException;

	class CielClient {
		const ERR_WEBSERVICE_CALL_FAILED = -1;

		/**
		 * @var string
		 */
		private $_endpoint;

		/**
		 * @var string
		 */
		private $_currentAuthenticationToken;

		/**
		 * @var int[]
		 */
		private $_successfulHttpStatusCodes = array(
			200
		);

		/**
		 * @var CielClientSessionProvider
		 */
		private $_sessionProvider;

		public function __construct($serviceUrl) {
			if (empty($serviceUrl)) {
				throw new InvalidArgumentException('Service URL must not be empty');
			}

			$this->_endpoint = $serviceUrl;
			$this->_sessionProvider = new InMemoryCielClientSessionProvider();
		}

		public function setSessionProvider(CielClientSessionProvider $sessionProvider) {
			$this->_sessionProvider = $sessionProvider;
			return $this;
		}

		private function _isSuccessfulHttpStatusCode($statusCode) {
			return in_array($statusCode, $this->_successfulHttpStatusCodes);
		}

		private function _assertAuthenticatedOrThrow() {
			if (!$this->isAuthenticated()) {
				throw new RequestAuthenticationRequiredException();
			}
		}

		private function _assertSuccessfulResponseOrThrow(CielResponse $response) {
			if ($response->hasError()) {
				throw new WebserviceErrorException($response->getError());
			}
		}

		private function _callAuthenticatedWebserviceMethod($method, $params) {
			$this->_assertAuthenticatedOrThrow();
			return $this->_callAuthenticatedWebserviceMethodWithToken($method, 
				$params, 
				$this->_currentAuthenticationToken);
		}

		private function _callAuthenticatedWebserviceMethodWithToken($method, $params, $token) {
			$request = new CielAuthenticatedRequest($method, 
				$token ,
				$params);

			$response = $this->_doRequest($request);
			$this->_assertSuccessfulResponseOrThrow($response);

			return $response->getResult();
		}

		private function _doRequest(CielRequest $request) {
			$requestData = $this->_buildRequestData($request);
			$responseDataJson = $this->_sendRequestData($requestData);

			if (!empty($responseDataJson)) {
				return $this->_processResponseData($responseDataJson);
			} else {
				throw new MissingWebserviceResponseBodyException('Webservice call returned with empty response body');
			}
		}

		private function _buildRequestData(CielRequest $request) {
			$requestData = array(
				'Method' => $request->getMethod(),
				'Params' => $request->getParameters()
			);

			if ($request->isAuthenticationRequired()) {
				$requestData['AuthenticationToken'] = $request->getAuthenticationToken();
			}

			return $requestData;
		}

		private function _sendRequestData($requestData) {
			if (empty($requestData['Params'])) {
				$requestDataJson = json_encode($requestData, JSON_FORCE_OBJECT);
			} else {
				$requestDataJson = json_encode($requestData);
			}

			$channel = curl_init($this->_endpoint);
			curl_setopt($channel, CURLOPT_CUSTOMREQUEST, 'POST');
			curl_setopt($channel, CURLOPT_FOLLOWLOCATION, true);
			curl_setopt($channel, CURLOPT_SSL_VERIFYPEER, false);
			curl_setopt($channel, CURLOPT_RETURNTRANSFER, true);
			curl_setopt($channel, CURLOPT_POSTFIELDS, $requestDataJson);
			curl_setopt($channel, CURLOPT_HTTPHEADER, array(
				'Content-Length: ' . function_exists('mb_strlen') 
					? mb_strlen($requestDataJson) 
					: strlen($requestDataJson),
				'Content-Type: text/plain'
			));

			$responseDataJson = curl_exec($channel);
			if ($responseDataJson === false) {
				throw new WebserviceCommunicationException(
					self::ERR_WEBSERVICE_CALL_FAILED, 
					sprintf('Webservice call failed - cURL error was: %s (%s)',
						curl_error($channel),
						curl_errno($channel))
				);
			}

			$statusCode = curl_getinfo($channel,  CURLINFO_HTTP_CODE);
			curl_close($channel);

			if (!$this->_isSuccessfulHttpStatusCode($statusCode)) {
				throw new WebserviceCommunicationException(
					$statusCode, 
					sprintf('Webservice call failed - HTTP status code was: %s.', 
						$statusCode)
				);
			}

			return $responseDataJson;
		}

		private function _processResponseData($responseDataJson) {
			$response = json_decode($responseDataJson, true, 512);
			if ($response !== null) {
				$serviceError = isset($response['Error']) 
					? $response['Error'] 
					: null;
				
				$serviceResult = isset($response['Result']) 
					? $response['Result'] 
					: null;
				
				return new CielResponse($serviceResult, $serviceError);
			} else {
				throw new WebserviceResponseFormatException(
					json_last_error_msg(), 
					json_last_error()
				);
			}
		}

		public function logon($userName, $password, $societyCode) {
			$credentials = new CielClientSessionCredentials($userName, 
				$password, 
				$societyCode);

			$authenticationToken = $this->_sessionProvider->resolveSessionToken($credentials);
			if (!empty($authenticationToken)) {
				$authenticationToken = $this->_testRequestAuthenticationToken($authenticationToken)
					? $authenticationToken 
					: null;
			}

			if (empty($authenticationToken)) {
				$authenticationToken = $this->_requestAuthenticationToken($userName, 
					$password, 
					$societyCode);

				if (!empty($authenticationToken)) {
					$this->_sessionProvider->registerSessionToken($credentials, $authenticationToken);
				}
			}

			$this->_currentAuthenticationToken = $authenticationToken;
			return $this->_currentAuthenticationToken;
		}

		private function _requestAuthenticationToken($userName, $password, $societyCode) {
			$request = new CielRequest('GetAuthenticationToken', array(
				'UserName' => $userName,
				'Password' => $password,
				'Database' => $societyCode
			));

			$response = $this->_doRequest($request);
			$this->_assertSuccessfulResponseOrThrow($response);
			return $response->getResult();
		}

		private function _testRequestAuthenticationToken($token) {
			$isTokenValid = false;

			try {
				$licenseNumber = $this->_callAuthenticatedWebserviceMethodWithToken('GetLicenseNumber',
					array(), 
					$token);
				$isTokenValid = !empty($licenseNumber);
			} catch (WebserviceErrorException $exc) {
				$isTokenValid = false;
			}

			return $isTokenValid;
		}

		public function logout() {
			if (!empty($this->_currentAuthenticationToken)) {
				$result = $this->_callAuthenticatedWebserviceMethod('Logout', array());
				$this->_sessionProvider->clearSessionTokenByTokenValue($this->_currentAuthenticationToken);
				$this->_currentAuthenticationToken = null;
			} else {
				$result = false;
			}

			return $result;
		}

		public function getAllWarehouses(GetAllWarehousesRequestParameters $params) {
			return $this->_callAuthenticatedWebserviceMethod('GetAllWarehouses', 
				$params->getParams());
		}

		public function getAllPartners(GetAllPartnersRequestParameters $params) {
			return $this->_callAuthenticatedWebserviceMethod('GetAllPartners', 
				$params->getParams());
		}

		public function getPartnerByCode(GetPartnerByCodeRequestParameters $params) {
			return $this->_callAuthenticatedWebserviceMethod('GetPartnerByCode', 
				$params->getParams());
		}

		public function getPartnerById(GetPartnerByIdRequestParameters $params) {
			return $this->_callAuthenticatedWebserviceMethod('GetPartnerById', 
				$params->getParams());
		}

		public function addPartner(AddPartnerRequestParameters $params) {
			return $this->_callAuthenticatedWebserviceMethod('AddPartner', 
				$params->getParams());
		}

		public function updatePartner(UpdatePartnerRequestParameters $params) {
			return $this->_callAuthenticatedWebserviceMethod('UpdatePartner', 
				$params->getParams());
		}

		public function addOrUpdatePartner(AddOrUpdatePartnerRequestParameters $params) {
			return $this->_callAuthenticatedWebserviceMethod('AddOrUpdatePartner', 
				$params->getParams());
		}

		public function deletePartnerById(DeletePartnerByIdRequestParameters $params) {
			return $this->_callAuthenticatedWebserviceMethod('DeletePartnerById', 
				$params->getParams());
		}

		public function getAllArticles(GetAllArticlesRequestParameters $params) {
			return $this->_callAuthenticatedWebserviceMethod('GetAllArticles', 
				$params->getParams());
		}

		public function getArticleByCode(GetArticleByCodeRequestParameters $params) {
			return $this->_callAuthenticatedWebserviceMethod('GetArticleByCode', 
				$params->getParams());
		}

		public function getArticleById(GetArticleByIdRequestParameters $params) {
			return $this->_callAuthenticatedWebserviceMethod('GetArticleById', 
				$params->getParams());
		}

		public function getAllStocksForArticles(GetAllStocksForArticlesRequestParameters $params) {
			return $this->_callAuthenticatedWebserviceMethod('GetAllStocksForArticles', 
				$params->getParams());
		}

		public function getStocksForArticlesByCode(GetStocksForArticleByCodeRequestParameters $params) {
			return $this->_callAuthenticatedWebserviceMethod('GetStocksForArticleByCode', 
				$params->getParams());
		}

		public function addArticle(AddArticleRequestParameters $params) {
			return $this->_callAuthenticatedWebserviceMethod('AddArticle', 
				$params->getParams());
		}

		public function updateArticle(UpdateArticleRequestParameters $params) {
			return $this->_callAuthenticatedWebserviceMethod('UpdateArticle', 
				$params->getParams());
		}

		public function addSaleOrder(AddSaleOrderRequestParameters $params) {
			return $this->_callAuthenticatedWebserviceMethod('AddSaleOrder', 
				$params->getParams());
		}

		public function addSaleInvoice(AddSaleInvoiceRequestParameters $params) {
			return $this->_callAuthenticatedWebserviceMethod('AddSaleInvoice', 
				$params->getParams());
		}

		public function deleteDocument(DeleteDocumentRequestParams $params) {
			return $this->_callAuthenticatedWebserviceMethod('DeleteDocument', 
				$params->getParams());
		}

		public function selectFromView(SelectFromViewRequestParameters $params) {
			return $this->_callAuthenticatedWebserviceMethod('SelectFromView', 
				$params->getParams());
		}

		public function getAllDocumentSeries(GetAllDocumentSeriesRequestParameters $params) {
			return $this->_callAuthenticatedWebserviceMethod('GetAllDocumentSeries', 
				$params->getParams());
		}

		public function addDocumentSeries(AddDocumentSeriesRequestParameters $params) {
			return $this->_callAuthenticatedWebserviceMethod('AddDocumentSeries', 
				$params->getParams());
		}

		public function getAllDocumentSeriesAssociations(GetAllDocumentSeriesAssociationRequestParameters $params) {
			return $this->_callAuthenticatedWebserviceMethod('GetAllDocumentSeriesAssociation', 
				$params->getParams());
		}

		public function addDocumentSeriesAssociation(AddAssociationRequestParameters $params) {
			return $this->_callAuthenticatedWebserviceMethod('AddAssociation', 
				$params->getParams());
		}

		public function getLicenseNumber(GetLicenseNumberRequestParameters $params) {
			return $this->_callAuthenticatedWebserviceMethod('GetLicenseNumber',
				$params->getParams());
		}

		public function getCurrentAuthenticationToken() {
			return $this->_currentAuthenticationToken;
		}

		public function isAuthenticated() {
			return !empty($this->_currentAuthenticationToken);
		}
	}
}
