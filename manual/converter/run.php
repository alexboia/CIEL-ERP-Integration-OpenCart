<?php
require_once __DIR__ . '/autoload.php';
require_once __DIR__ . '/convert.php';

myc_build_manual(isset($GLOBALS['argv']) 
	? $GLOBALS['argv'] 
	: array());