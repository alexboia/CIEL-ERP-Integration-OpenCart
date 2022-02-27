<?php
/**
 * @param array $source 
 * @param array|null $fallback 
 * @return array 
 */
function myc_merge_if_value_empty(array $source, $fallback) {
	foreach ($source as $key => $val) {
		if (empty($val) 
			&& !empty($fallback) 
			&& !empty($fallback[$key])) {
			$source[$key] = $fallback[$key];
		}
	}
	return $source;
}