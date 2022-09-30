<?php
namespace Ciel\Api {
	interface CielClientAmbientConnectionInfoProvider {
		/**
		 * @return CielClientConnectionInfo
		 */
		function getAmbientConnectionInfo();
	}
}