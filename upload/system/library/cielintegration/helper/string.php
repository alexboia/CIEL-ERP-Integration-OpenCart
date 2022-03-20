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

function myc_extract_vat_code_parts($fullVatCode) {
	$parts = array(
		'attribute' => '',
		'code' => ''
	);

	$fullVatCode = strtoupper($fullVatCode);
	if (preg_match('/^([A-Z]{2})([0-9]{5,})$/i', $fullVatCode)) {
		$parts['attribute'] = substr($fullVatCode, 0, 2);
		$parts['code'] = substr($fullVatCode, 2);
	} else {
		$parts['code'] = $fullVatCode;
	}

	return $parts;
}