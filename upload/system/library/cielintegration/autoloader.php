<?php
namespace CielIntegration {
	use InvalidArgumentException;

	class Autoloader {
		private static $_initialized = false;

		private static $_prefixConfig = null;

		public static function enable() {
			$mainLibDir = __DIR__;
			$vendorLibDir = self::_getVendorLibDir();
			$adminIntegrationLibDir = self::_getAdminIntegrationLibDir();

			self::init(array(
				'CielIntegration\\Integration\\Admin' => array(
					'separator' => '\\',
					'libDir' => $adminIntegrationLibDir,
					'transform' => 'myc_underscorize'
				),
				'CielIntegration' => array(
					'separator' => '\\',
					'libDir' => $mainLibDir,
					'transform' => 'myc_underscorize'
				),
				'Symfony\\Component\\CssSelector' => array(
					'separator' => '\\',
					'libDir' => $vendorLibDir . '/symphony/css-selector'
				),
				'voku\\helper' => array(
					'separator' => '\\',
					'libDir' => $vendorLibDir . '/voku/helper'
				 )
			));
		}

		private static function _getVendorLibDir() {
			return  __DIR__ . '/vendor';
		}

		private static function _getAdminIntegrationLibDir() {
			return realpath(DIR_APPLICATION . '/../admin/model/extension/cielintegration');
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
					
					if (isset($config['transform']) && is_callable($config['transform'])) {
						$classPath = self::_getRelativePath($classPath, 
							$config['separator'], 
							$config['transform']);
					} else {
						$classPath = self::_getRelativePath($classPath, 
							$config['separator']);
					}

					$classPath = $config['libDir'] . '/' . $classPath . '.php';
					break;
				}
			}

			if (!empty($classPath) && file_exists($classPath)) {
				require_once $classPath;
			}
		}

		private static function _getRelativePath($className, $separator, $transform = null) {
			$classPath = array();
			$pathParts = array_filter(explode($separator, $className), function($el) {
				return !empty($el);
			});
			$className = array_pop($pathParts);
			foreach ($pathParts as $namePart) {
				if (!empty($namePart)) {
					if ($transform !== null) {
						$namePart = call_user_func($transform, $namePart);
					}
					
					$classPath[] = $namePart;
				}
			}

			if ($transform !== null) {
				$className = call_user_func($transform, $className);
			}

			$classPath[] = $className;
			return implode('/', $classPath);
		}
	}
}