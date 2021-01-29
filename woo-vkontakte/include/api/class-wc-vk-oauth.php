<?php


if ( ! class_exists( 'VKOAuth' ) ) {

	/**
	 * Class VKOAuth
	 */
	class VKOAuth {
//		const VERSION = '5.101';
		const VERSION = '5.131';

		const PARAM_VERSION = 'v';
		const PARAM_CLIENT_ID = 'client_id';
		const PARAM_REDIRECT_URI = 'redirect_uri';
		const PARAM_GROUP_IDS = 'group_ids';
		const PARAM_DISPLAY = 'display';
		const PARAM_SCOPE = 'scope';
		const PARAM_RESPONSE_TYPE = 'response_type';
		const PARAM_STATE = 'state';
		const PARAM_CLIENT_SECRET = 'client_secret';
		const PARAM_CODE = 'code';
		const PARAM_REVOKE = 'revoke';

		const RESPONSE_KEY_ERROR = 'error';
		const RESPONSE_KEY_ERROR_DESCRIPTION = 'error_description';

		const HOST = 'https://oauth.vk.com';
		const ENDPOINT_AUTHORIZE = '/authorize';
		const ENDPOINT_ACCESS_TOKEN = '/access_token';

		const CONNECTION_TIMEOUT = 10;
		const HTTP_STATUS_CODE_OK = 200;

		/**
		 * @var VKHttpClient
		 */
		private $http_client;

		/**
		 * @var string
		 */
		private $version;

		/**
		 * @var string
		 */
		private $host;

		/**
		 * VKOAuth constructor.
		 *
		 * @param string $version
		 */
		public function __construct( $version = self::VERSION ) {

			if ( ! class_exists( 'VKHttpClient' ) ) {
				include_once( __DIR__ . '/class-wc-vk-http-client.php' );
			}

			if ( ! class_exists( 'VKClientException' ) ) {
				include_once( __DIR__ . '/class-wc-vk-client-exception.php' );
			}

			if ( ! class_exists( 'VKOAuthException' ) ) {
				include_once( __DIR__ . '/class-wc-vk-oauth-exception.php' );
			}

			$this->http_client = new VKHttpClient( static::CONNECTION_TIMEOUT );
			$this->version     = $version;
			$this->host        = static::HOST;
		}

		/**
		 * Get authorize url
		 *
		 * @param string $response_type
		 * @param int $client_id
		 * @param string $redirect_uri
		 * @param string $display
		 * @param int[] $scope
		 * @param string $state
		 * @param int[] $group_ids
		 * @param bool $revoke
		 *
		 * @return string
		 * @see VKOAuthResponseType
		 * @see VKOAuthDisplay
		 * @see VKOAuthGroupScope
		 * @see VKOAuthUserScope
		 */
		public function getAuthorizeUrl(
			$response_type, $client_id, $redirect_uri, $display,
			$scope = null, $state = null, $group_ids = null, $revoke = false
		) {
			$scope_mask = 0;
			foreach ( $scope as $scope_setting ) {
				$scope_mask |= $scope_setting;
			}

			$params = array(
				static::PARAM_CLIENT_ID     => $client_id,
				static::PARAM_REDIRECT_URI  => $redirect_uri,
				static::PARAM_DISPLAY       => $display,
				static::PARAM_SCOPE         => $scope_mask,
				static::PARAM_STATE         => $state,
				static::PARAM_RESPONSE_TYPE => $response_type,
				static::PARAM_VERSION       => $this->version,
			);

			if ( $group_ids ) {
				$params[ static::PARAM_GROUP_IDS ] = implode( ',', $group_ids );
			}

			if ( $revoke ) {
				$params[ static::PARAM_REVOKE ] = 1;
			}

			return $this->host . static::ENDPOINT_AUTHORIZE . '?' . http_build_query( $params );
		}

		/**
		 * @param int $client_id
		 * @param string $client_secret
		 * @param string $redirect_uri
		 * @param string $code
		 *
		 * @return mixed
		 * @throws VKClientException
		 * @throws VKOAuthException
		 */
		public function getAccessToken( $client_id, $client_secret, $redirect_uri, $code ) {
			$params = array(
				static::PARAM_CLIENT_ID     => $client_id,
				static::PARAM_CLIENT_SECRET => $client_secret,
				static::PARAM_REDIRECT_URI  => $redirect_uri,
				static::PARAM_CODE          => $code,
			);

			try {
				$response = $this->http_client->get( $this->host . static::ENDPOINT_ACCESS_TOKEN, $params );
			} catch ( VKTransportRequestException $e ) {
				#todo catch
				throw new VKClientException( $e );
			}

			return $this->checkOAuthResponse( $response );
		}

		/**
		 * Decodes the authorization response and checks its status code and whether it has an error.
		 *
		 * @param VKTransportClientResponse $response
		 *
		 * @return mixed
		 *
		 * @throws VKClientException
		 * @throws VKOAuthException
		 */
		protected function checkOAuthResponse( VKTransportClientResponse $response ) {
			$this->checkHttpStatus( $response );

			$body        = $response->getBody();
			$decode_body = $this->decodeBody( $body );

			if ( isset( $decode_body[ static::RESPONSE_KEY_ERROR ] ) ) {
				throw new VKOAuthException( "{$decode_body[static::RESPONSE_KEY_ERROR_DESCRIPTION]}. OAuth error {$decode_body[static::RESPONSE_KEY_ERROR]}" );
			}

			return $decode_body;
		}

		/**
		 * Decodes body.
		 *
		 * @param string $body
		 *
		 * @return mixed
		 */
		protected function decodeBody( $body ) {
			$decoded_body = json_decode( $body, true );

			if ( $decoded_body === null || ! is_array( $decoded_body ) ) {
				$decoded_body = [];
			}

			return $decoded_body;
		}

		/**
		 * @param VKTransportClientResponse $response
		 *
		 * @throws VKClientException
		 */
		protected function checkHttpStatus( VKTransportClientResponse $response ) {
			if ( (int) $response->getHttpStatus() !== static::HTTP_STATUS_CODE_OK ) {
				throw new VKClientException( "Invalid http status: {$response->getHttpStatus()}" );
			}
		}
	}
}

