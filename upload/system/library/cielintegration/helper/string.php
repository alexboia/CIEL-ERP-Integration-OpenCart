<?php
function myc_underscorize($value) {
	if (empty($value)) {
		return $value;
	}

	$returnParts = array();
	$parts = preg_split('/([A-Z]{1}[^A-Z]*)/', $value, -1, PREG_SPLIT_NO_EMPTY | PREG_SPLIT_DELIM_CAPTURE);

	foreach ($parts as $p) {
		$returnParts[] = strtolower($p);
	}

	$returnValue = join('_', $returnParts);
	return $returnValue;
}