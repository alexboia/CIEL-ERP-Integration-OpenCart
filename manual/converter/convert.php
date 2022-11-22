<?php
function myc_build_manual(array $args) {
	$inputDir = realpath(__DIR__ . '/../src');
	$outputDir = realpath(__DIR__ . '/../output');

	$processor = new MyClar\ManualBuilder\ManualProcessor($inputDir, $outputDir);
	$processor->process();
}