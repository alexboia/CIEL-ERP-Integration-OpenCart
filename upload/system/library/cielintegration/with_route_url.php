<?php
namespace CielIntegration {
	/**
	 * @property \Url $url
	 * @property \Session $session
	 */
	trait WithRouteUrl {
		protected function _createRouteUrl($route, array $params = array()) {
			$params['token'] = $this->_getCurrentSessionToken();
			return $this->_createRouteUrlRaw($route, 
				$params);
		}

		protected function _createRouteUrlRaw($route, array $params = array()) {
			$paramsParts = array();
			foreach ($params as $p => $v) {
				$paramsParts[] = sprintf('%s=%s', $p, $v);
			}

			$strParams = join('&', $paramsParts);
			return $this->url->link($route, 
				$strParams, 
				true);
		}

		protected function _getCurrentSessionToken() {
			return $this->session
				->data['token'];
		}
	}
}