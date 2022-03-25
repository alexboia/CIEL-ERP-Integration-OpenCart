<?php
namespace CielIntegration {
	class AnafApiClient {
		const ANAF_VAT_PAYER_API_URL = "https://webservicesp.anaf.ro/PlatitorTvaRest/api/v6/ws/tva";

		public function getVatInfoByVatCode($vatCode) {
			if (empty($vatCode)) {
				return null;
			}
	
			$result = null;
			$vatCode = $this->_prepareVatCode($vatCode);
			if (empty($vatCode)) {
				return null;
			}
			
			$lookupData = $this->_buildVatInfoClookupData($vatCode);
			$response = $this->_sendHttpPostRequest(self::ANAF_VAT_PAYER_API_URL, 
				$lookupData);

			if (!empty($response['found']) && !empty($response['found'][0])) {
				$result = new AnafApiClientVatPayerData($response['found'][0]);
			}

			return $result;
		}

		private function _prepareVatCode($vatCode) {
			$vatCodeParts = myc_extract_vat_code_parts($vatCode);
			if (preg_match('/^([0-9]+)$/', $vatCodeParts['code'])) {
				return $vatCodeParts['code'];
			} else {
				return null;
			}
		}

		private function _buildVatInfoClookupData($vatCode) {
			return array(
				array(
					'cui' => $vatCode, 
					'data' => date('Y-m-d')
				)
			);
		}

		private function _sendHttpPostRequest($url, $data) {
			$response = null;
			
			$ch = $this->_createHttpPostCurlChannel($url, $data);
			$responseContents = curl_exec($ch);
			
			$status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
			curl_close($ch);
	
			if ($status == 200) {
				$response = json_decode($responseContents, true);
			}
	
			return $response;
		}

 		private function _createHttpPostCurlChannel($url, $data) {
			$ch = curl_init($url);
			
			curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
			curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
			
			curl_setopt($ch, CURLOPT_HEADER, 0);
			curl_setopt($ch, CURLOPT_VERBOSE, 0);
			curl_setopt($ch, CURLOPT_TIMEOUT, 30);

			if (!empty($data)) {
				$headers[] = "Content-Type: application/json";
				curl_setopt($ch, CURLOPT_POST, 1);
				curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
			}

			curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
			return $ch;
    	}
	}
}