<?php
namespace Ciel\Api\Session {
	interface CielClientSessionProvider {
		function setup();

		function registerSessionToken(CielClientSessionCredentials $credentials, $token);

		function resolveSessionToken(CielClientSessionCredentials $credentials);

		function clearSessionToken(CielClientSessionCredentials $credentials);

		function clearSessionTokenByTokenValue($token);

		function isSupported();
	}
}