<?php
namespace CielIntegration {
	use \Loader;
	use \Registry;
	
	/**
	 * @property \Loader $load
	 * @property \Registry $registry
	 */
	class CielModel extends \Model {
		use WithBootstrapper;

		public function __construct($registry) {
			parent::__construct($registry);
			$this->_bootstrap();
		}
	}
}