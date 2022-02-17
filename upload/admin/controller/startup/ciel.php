<?php
use CielIntegration\Bootstrapper;
class ControllerStartupCiel extends Controller {
	public function index() {
		Bootstrapper::bootstrap();
	}
}