<?php
function myc_manual_builder_autoloader($className) {
	static $prefix = 'MyClar\\ManualBuilder\\';
	static $searchDirectory = __DIR__ . DIRECTORY_SEPARATOR . 'lib';

	if (strpos($className, $prefix) === 0) {
		$actualClassName = str_replace($prefix, '', $className);
		$pathParts = array_filter(explode('\\', $actualClassName), function($el) {
			return !empty($el);
		});

		$className = array_pop($pathParts);
		$classPath = array($searchDirectory);

		foreach ($pathParts as $namePart) {
			$classPath[] = lcfirst($namePart);
		}

		$classPath[] = $className;
		return implode(DIRECTORY_SEPARATOR, $classPath);
	}
}

function myc_manual_builder_autoload_enable() {
	spl_autoload_register('myc_manual_builder_autoloader');
}