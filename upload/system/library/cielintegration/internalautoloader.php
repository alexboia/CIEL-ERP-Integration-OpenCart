<?php
namespace CielIntegration {
	use InvalidArgumentException;

	class InternalAutoloader {
		private static $_initialized = false;

		private static $_prefixConfig = null;

		public static function enable() {
			$libDir = self::_getVendorLibDir();
			$adminIntegrationLibDir = self::_getAdminIntegrationLibDir();

			self::init(array(
				'CielIntegration\\Integration\\Admin' => array(
					'separator' => '\\',
					'libDir' => $adminIntegrationLibDir,
					'transform' => 'strtolower'
				),
				'Symfony\\Component\\CssSelector' => array(
					'separator' => '\\',
					'libDir' => $libDir . '/symphony/css-selector'
				),
				'voku\\helper' => array(
					'separator' => '\\',
					'libDir' => $libDir . '/voku/helper'
				 )
			));
		}

		private static function _getVendorLibDir() {
			return  __DIR__ . '/vendor';
		}

		private static function _getAdminIntegrationLibDir() {
			return realpath(DIR_APPLICATION . '/../admin/model/extension/ciel_integration');
		}

		private static function init($prefixConfig) {
			if (empty($prefixConfig) || !is_array($prefixConfig)) {
				throw new InvalidArgumentException('The prefix configuration may not be empty.');
			}

			if (!self::$_initialized) {
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
					if (isset($config['transform']) && is_callable($config['transform'])) {
						$classPath = call_user_func($config['transform'], 
							$classPath);
					}
					break;
				}
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