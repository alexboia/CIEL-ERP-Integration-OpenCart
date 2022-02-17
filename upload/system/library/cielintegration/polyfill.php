<?php
if (!function_exists('str_contains')) {
	function str_contains($haystack, $needle) {
		return ('' === $needle || false !== strpos($haystack, $needle));
	}
}