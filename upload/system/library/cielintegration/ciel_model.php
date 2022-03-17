<?php
namespace CielIntegration {

    use CielIntegration\Wrappers\DbOperations;
    use \Loader;
	use \Registry;
	
	/**
	 * @property \Loader $load
	 * @property \Registry $registry
	 * @property \Config $config
	 * @property \DB $db
	 */
	class CielModel extends \Model {
		use WithBootstrapper;

		public function __construct($registry) {
			parent::__construct($registry);
			$this->_bootstrap();
		}

		/**
		 * @return DbOperations 
		 */
		protected function _getDbOperations() {
			return new DbOperations($this->_getDb());
		}

		/**
		 * @return \DB
		 */
		protected function _getDb() {
			return $this->db;
		}
	}
}