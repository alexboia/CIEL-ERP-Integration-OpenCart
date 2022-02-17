<?php
namespace Ciel\Api\Request\Parameters {

	use Ciel\Api\Request\CielRequestParameters;

	class SelectFromViewRequestParameters extends CielRequestParameters {
		private $_viewName;

		private $_query;

		public function setViewName($val) {
			$this->_viewName = $val;
			return $this;
		}

		public function setQuery($val) {
			$this->_query = $val;
			return $this;
		}

		public function getParams() {
			$params = array(
				'viewName' => $this->_viewName
			);

			if (!empty($this->_query)) {
				$params['query'] = $this->_query;
			}

			return $params;
		}
	}
}