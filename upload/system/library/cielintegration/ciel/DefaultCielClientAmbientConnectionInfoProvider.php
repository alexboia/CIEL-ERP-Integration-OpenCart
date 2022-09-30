<?php
namespace Ciel\Api {
	class DefaultCielClientAmbientConnectionInfoProvider implements CielClientAmbientConnectionInfoProvider {
		/**
		 * @var CielClientConnectionInfo
		 */
		private $_options;

		public function __construct(CielClientConnectionInfo $options) {
			$this->_options = $options;
		}

		public function getAmbientConnectionInfo() { 
			return $this->_options;
		}
	}
}