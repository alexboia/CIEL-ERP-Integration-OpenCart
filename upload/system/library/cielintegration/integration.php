<?php
namespace CielIntegration {
	class Integration {
		use WithBootstrapper;

		public function __construct() {
			$this->_bootstrap();
		}
	}
}