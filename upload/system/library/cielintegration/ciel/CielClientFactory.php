<?php
namespace Ciel\Api {
	interface CielClientFactory {
		function createCielClientForEndpointAndOptions($endpoint, array $options);

		function createCielClientForConnectionInfo(CielClientConnectionInfo $info);

		function getAmbientCielClient();
	}
}