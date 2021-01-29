<?php


if ( ! class_exists( 'VKApiClient' ) ) {

	/**
	 * Class VKApiClient
	 */
	class VKApiClient {

		const API_VERSION = 5.131;
		const CALLBACK_API_VERSION = 5.126;
		const API_HOST = 'https://api.vk.com/method';

		/**
		 * @var VKApiRequest
		 */
		private $request;

		/**
		 * @var VKOAuth
		 */
		private $vkoath;

		/**
		 * @var VKMethods
		 */
		private $methods;

		/**
		 * VKApiClient constructor.
		 *
		 * @param $access_token_user
		 * @param $access_token_group
		 * @param float $api_version
		 * @param null $language
		 */
		public function __construct( $access_token_user, $access_token_group, $api_version = self::API_VERSION, $language = null ) {

			if ( ! class_exists( 'VKApiRequest' ) ) {
				include_once( __DIR__ . '/class-wc-vk-api-request.php' );
			}

			$this->request = new \VKApiRequest( $access_token_user, $access_token_group, $api_version, $language, self::API_HOST );
		}

		/**
		 * @return VKApiRequest
		 */
		public function getRequest() {
			return $this->request;
		}

		/**
		 * @return VKOAuth
		 */
		public function vkoath() {

			if ( ! class_exists( 'VKOAuth' ) ) {
				include_once( __DIR__ . '/class-wc-vk-oauth.php' );
			}

			if ( ! $this->vkoath ) {
				$this->vkoath = new \VKOAuth();
			}

			return $this->vkoath;
		}

		/**
		 * @return VKMethods
		 */
		public function methods() {

			if ( ! class_exists( 'VKMethods' ) ) {
				include_once( __DIR__ . '/class-wc-vk-api-methods.php' );
			}

			if ( ! $this->methods ) {
				$this->methods = new \VKMethods( $this->request );
			}

			return $this->methods;
		}
	}
}