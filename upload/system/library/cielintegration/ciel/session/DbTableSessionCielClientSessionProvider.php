<?php
namespace Ciel\Api\Session {

    use InvalidArgumentException;
    use PDO;

	class DbTableSessionCielClientSessionProvider implements CielClientSessionProvider {
		const PDO_MYSQL_DRIVER_ID = 'mysql';

		const DEFAULT_MYSQL_PORT = 3306;

		const DEFAULT_MYSQL_CHARSET = 'utf8';

		const DEFAULT_TABLE_NAME = 'ciel_sessions';

		const DEFAULT_TABLE_PREFIX = '';

		const DEFAULT_TOKEN_LIFETIME_SECONDS = 2678400;

		/**
		 * @var array
		 */
		private $_connectionInfo;

		private $_tableName;

		private $_tokenUpsertQuery;

		private $_tokenDeleteQuery;

		private $_tokenDeleteByTokenValueQuery;

		private $_tokenSelectQuery;

		private $_checkTableQuery;

		private $_createTableQuery;

		/**
		 * @var PDO
		 */
		private $_db = null;

		private $_tokenLifetimeSeconds;

		public function __construct(array $connectionInfo, 
			array $tokenOptions = array(), 
			array $tableOptions = array()) {
			if (!$this->_validateConnectionInfo($connectionInfo)) {
				throw new InvalidArgumentException('Invalid connection info provided.');
			}

			$this->_connectionInfo = $this->_ensureConnectionInfoDefaults($connectionInfo);
			$this->_tokenLifetimeSeconds = $this->_extractTokenLifetime($tokenOptions);

			$this->_tableName = $this->_buildTableName($tableOptions);

			$this->_checkTableQuery = $this->_buildCheckTableQuery($this->_tableName);
			$this->_createTableQuery = $this->_buildCreateTableQuery($this->_tableName);
			$this->_tokenUpsertQuery = $this->_buildTokenUpsertQuery($this->_tableName);
			$this->_tokenDeleteQuery = $this->_buildTokenDeleteQuery($this->_tableName);
			$this->_tokenDeleteByTokenValueQuery = $this->_buildTokenDeleteByTokenValueQuery($this->_tableName);
			$this->_tokenSelectQuery = $this->_buildTokenSelectQuery($this->_tableName);
		}

		private function _validateConnectionInfo(array $connectionInfo) {
			return !empty($connectionInfo)
				&& !empty($connectionInfo['host'])
				&& !empty($connectionInfo['dbName'])
				&& !empty($connectionInfo['dbUserName']);
		}

		private function _ensureConnectionInfoDefaults(array $connectionInfo) {
			if (empty($connectionInfo['port'])) {
				$connectionInfo['port'] = self::DEFAULT_MYSQL_PORT;
			}

			if (empty($connectionInfo['dbPassword'])) {
				$connectionInfo['dbPassword'] = null;
			}

			if (empty($connectionInfo['charset'])) {
				$connectionInfo['charset'] = self::DEFAULT_MYSQL_CHARSET;
			}

			return $connectionInfo;
		}

		private function _extractTokenLifetime(array $tokenOptions) {
			return isset($tokenOptions['lifetimeSeconds'])
				? max(self::DEFAULT_TOKEN_LIFETIME_SECONDS, intval($tokenOptions['lifetimeSeconds']))
				: self::DEFAULT_TOKEN_LIFETIME_SECONDS;
		}

		private function _buildTableName(array $tableOptions) {
			$tableName = !empty($tableOptions['name'])
				? $tableOptions['name']
				: self::DEFAULT_TABLE_NAME;

			$tablePrefix = !empty($tableOptions['prefix'])
				? $tableOptions['prefix']
				: self::DEFAULT_TABLE_PREFIX;

			return $tablePrefix . $tableName;
		}

		private function _buildCheckTableQuery($tableName) {
			return 'SELECT COUNT(1) as table_count FROM INFORMATION_SCHEMA.tables tinfo
				WHERE tinfo.TABLE_SCHEMA = :check_schema_name 
				AND tinfo.TABLE_NAME = :check_table_name;';
		}

		private function _buildCreateTableQuery($tableName) {
			return 'CREATE TABLE `' . $tableName . '` (
				`session_hash` VARCHAR(150) NOT NULL,
				`session_token` VARCHAR(150) NOT NULL,
				`session_username` VARCHAR(250) NOT NULL,
				`session_society_code` VARCHAR(250) NOT NULL,
				`session_ts_created` DATETIME NOT NULL,
				`session_ts_expiration` DATETIME NOT NULL,
				PRIMARY KEY (`session_hash`)
			);';
		}

		private function _buildTokenUpsertQuery($tableName) {
			return 'INSERT INTO `' . $tableName . '` 
				(session_hash, 
					session_token, 
					session_username, 
					session_society_code, 
					session_ts_created, 
					session_ts_expiration) 
				VALUES
				(:session_hash, 
					:session_token, 
					:session_username, 
					:session_society_code, 
					UTC_TIMESTAMP(), 
					DATE_ADD(UTC_TIMESTAMP(), INTERVAL :session_token_lifetime_seconds SECOND))
				ON DUPLICATE KEY UPDATE 
					session_token = :session_token,
					session_ts_created = UTC_TIMESTAMP(),
					session_ts_expiration = DATE_ADD(UTC_TIMESTAMP(), INTERVAL :session_token_lifetime_seconds SECOND)';
		}

		private function _buildTokenDeleteQuery($tableName) {
			return 'DELETE FROM `' . $tableName . '` 
				WHERE session_hash = :session_hash 
				LIMIT 1';
		}

		private function _buildTokenDeleteByTokenValueQuery($tableName) {
			return 'DELETE FROM `' . $tableName . '` 
				WHERE session_token = :session_token 
				LIMIT 1';
		}

		private function _buildTokenSelectQuery($tableName) {
			return 'SELECT session_token 
				FROM `' . $tableName . '` 
				WHERE session_hash = :session_hash
					AND session_ts_expiration > UTC_TIMESTAMP()';
		}

		public function setup() {
			if (!$this->_isTableInstalled()) {
				$this->_installTable();
			}
		}

		public function cleanup() {
			$this->_db = null;
		}

		private function _isTableInstalled() {
			$db = $this->_getDb();
			$checkStmt = $db->prepare($this->_checkTableQuery);
			$isInstalled = false;

			if ($checkStmt) {
				$checkStmt->execute(array(
					':check_schema_name' => $this->_connectionInfo['dbName'],
					':check_table_name' => $this->_tableName
				));
				$result = $checkStmt->fetch(PDO::FETCH_ASSOC);
				$checkStmt->closeCursor();

				$isInstalled = !empty($result) 
					&& is_array($result) 
					&& $result['table_count'] > 0;
			}

			return $isInstalled;
		}

		private function _installTable() {
			$db = $this->_getDb();
			$db->exec($this->_createTableQuery);
		}

		private function _getDb() {
			if ($this->_db === null) {
				$this->_db = $this->_createPdoMysql();
				register_shutdown_function(array($this, 'cleanup'));
			}
			return $this->_db;
		}

		private function _createPdoMysql() {
			$dataSourceName = $this->_buildDataSourceName();
			return new PDO($dataSourceName, 
				$this->_connectionInfo['dbUserName'], 
				$this->_connectionInfo['dbPassword'],
				array(
					PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
				)
			);
		}

		private function _buildDataSourceName() {
			return sprintf('%s:host=%s;port=%d;dbname=%s;charset=%s', 
				self::PDO_MYSQL_DRIVER_ID, 
				$this->_connectionInfo['host'], 
				$this->_connectionInfo['port'],
				$this->_connectionInfo['dbName'],
				$this->_connectionInfo['charset']
			);
		}

		public function registerSessionToken(CielClientSessionCredentials $credentials, $token) { 
			if (empty($token)) {
				throw new InvalidArgumentException('Token cannot be empty');
			}

			$db = $this->_getDb();
			$registerStmt = $db->prepare($this->_tokenUpsertQuery);
			$actualInsertedToken = null;

			if ($registerStmt) {
				$registerStmt->execute(array(
					':session_hash' => $credentials->computeHash(),
					':session_token' => $token,
					':session_username' => $credentials->getUserName(),
					':session_society_code' => $credentials->getSocietyCode(),
					':session_token_lifetime_seconds' => $this->_tokenLifetimeSeconds
				));

				$actualInsertedToken = $this->resolveSessionToken($credentials);
			}

			return $actualInsertedToken;
		}

		public function resolveSessionToken(CielClientSessionCredentials $credentials) { 
			$db = $this->_getDb();
			$selectStmt = $db->prepare($this->_tokenSelectQuery);
			$token = null;
			
			if ($selectStmt) {
				$selectStmt->execute(array(
					':session_hash' => $credentials->computeHash()
				));
				$result = $selectStmt->fetch(PDO::FETCH_ASSOC);
				$token = !empty($result) && is_array($result)
					? $result['session_token']
					: null;
			}

			return $token;
		}

		public function clearSessionToken(CielClientSessionCredentials $credentials) {
			$db = $this->_getDb();
			$deleteStmt = $db->prepare($this->_tokenDeleteQuery);
			if ($deleteStmt) {
				$deleteStmt->execute(array(
					':session_hash' => $credentials->computeHash()
				));
			}
		}

		public function clearSessionTokenByTokenValue($token) {
			$db = $this->_getDb();
			$deleteStmt = $db->prepare($this->_tokenDeleteByTokenValueQuery);
			if ($deleteStmt) {
				$deleteStmt->execute(array(
					':session_token' => $token
				));
			}
		}

		public function isSupported() { 
			return $this->_pdoExtensionLoaded() 
				&& $this->_pdoMysqlDriverAvailable();
		}

		private function _pdoExtensionLoaded() {
			return class_exists('PDO');
		}

		private function _pdoMysqlDriverAvailable() {
			return in_array(self::PDO_MYSQL_DRIVER_ID, 
				PDO::getAvailableDrivers());
		}

		public function getTableName() {
			return $this->_tableName;
		}

		public function getTokenLifetimeSeconds() {
			return $this->_tokenLifetimeSeconds;
		}
	}
}