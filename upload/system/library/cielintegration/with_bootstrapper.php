<?php
namespace CielIntegration {
	trait WithBootstrapper {
		protected function _bootstrap() {
			Bootstrapper::bootstrap();
		}
	}
}