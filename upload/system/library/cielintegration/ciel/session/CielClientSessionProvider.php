<?php
namespace Ciel\Api\Session {
	interface CielClientSessionProvider {
		function setup();

		/**
		 * @param CielClientSessionCredentials $credentials 
		 * @param string $token 
		 * @return string
		 */
		function registerSessionToken(CielClientSessionCredentials $credentials, $token);

		/**
		 * @param CielClientSessionCredentials $credentials 
		 * @return string
		 */
		function resolveSessionToken(CielClientSessionCredentials $credentials);

		function clearSessionToken(CielClientSessionCredentials $credentials);

		function clearSessionTokenByTokenValue($token);

		/**
		 * @return boolean
		 */
		function isSupported();
	}
}