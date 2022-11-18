<?php
function myc_clean_repair_html($html) {
	if (empty($html)) {
		return '';
	}

	if (function_exists('tidy_parse_string')) {
		$tidy = tidy_parse_string($html, array(
			'drop-empty-elements' => false,
			'drop-empty-paras' => false,
			'escape-scripts' => false,
			'tidy-mark' => false
		));

		if ($tidy) {
			$tidy->cleanRepair();
			$html = tidy_get_output($tidy);
		}
	}

	return $html;
}