<?php
use CielIntegration\Bootstrapper;

class ControllerStartupCielCatalog extends Controller {
	public function index() {
		if (function_exists('set_time_limit')) {
			set_time_limit(0);
		}
		Bootstrapper::bootstrap();
	}
}