<?php
namespace Ciel\Api {
	use InvalidArgumentException;

	class CielAutoloader {
		private static $_defaultLibDir;

		private static $_initialized = false;

		private static $_prefixConfig = null;

		public static function enable() {
			self::init(__DIR__, array(
				'Ciel\\Api' => array(
					'separator' => '\\',
					'libDir' => __DIR__
				 )
			));
		}

		private static function init($defaultLibDir, $prefixConfig) {
			if (empty($defaultLibDir)) {
				throw new InvalidArgumentException('The default library directory may not be empty.');
			}

			if (empty($prefixConfig) || !is_array($prefixConfig)) {
				throw new InvalidArgumentException('The prefix configuration may not be empty.');
			}

			if (!self::$_initialized) {
				self::$_defaultLibDir = $defaultLibDir;
				self::$_prefixConfig = $prefixConfig;
				self::$_initialized = true;
				spl_autoload_register(array(__CLASS__, 'autoload'));
			}
		}

		private static function autoload($className) {
			$classPath = null;

			foreach (self::$_prefixConfig as $prefix => $config) {
				$fullPrefix = $prefix . $config['separator'];
				if (strpos($className, $fullPrefix) === 0) {
					$classPath = str_replace($fullPrefix, '', $className);
					$classPath = self::_getRelativePath($classPath, $config['separator']);
					$classPath = $config['libDir'] . '/' . $classPath . '.php';
					break;
				}
			}

			if (empty($classPath)) {
				$classPath = self::$_defaultLibDir . '/3rdParty/' . $className . '.php';
			}

			if (!empty($classPath) && file_exists($classPath)) {
				require_once $classPath;
			}
		}

		private static function _getRelativePath($className, $separator) {
			$classPath = array();
			$pathParts = array_filter(explode($separator, $className), function($el) {
				return !empty($el);
			});
			$className = array_pop($pathParts);
			foreach ($pathParts as $namePart) {
				if (!empty($namePart)) {
					$namePart[0] = strtolower($namePart[0]);
					$classPath[] = $namePart;
				}
			}
			$classPath[] = $className;
			return implode('/', $classPath);
		}
	}
}