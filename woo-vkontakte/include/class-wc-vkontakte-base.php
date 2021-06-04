<?php
/**
 * VKontakte Integration.
 *
 * @package  WC_VKontakte_Base
 * @category Integration
 * @author   VKontakte
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

if ( ! class_exists( 'WC_VKontakte_Base' ) ) {
	if ( ! class_exists( 'WC_VKontakte_Abstracts_Settings' ) ) {
		include_once 'class-wc-vkontakte-abstracts-settings.php';
	}


	/**
	 * Class WC_VKontakte_Base
	 */
	class WC_VKontakte_Base extends WC_VKontakte_Abstracts_Settings {

		const VK_MODULE_VERSION = '1.0';

		/** @var array */
		public static $options;

		/** @var VKApiClient|bool */
		protected $apiClient;

		/** @var WC_VK_References */
		protected $references;

		/** @var WC_VKontakte_Model */
		protected $model;

		/** @var WC_VK_Logger */
		protected $logger;

		/**
		 * Init and hook in the integration.
		 *
		 * @param VKApiClient|bool $vkontakte (default = false)
		 */
		public function __construct( $vkontakte = false ) {
			parent::__construct();

			static::$options = $this->clearOptions( get_option( static::$option_key ) );

			if ( ! class_exists( 'VKApiClient' ) ) {
				include_once( __DIR__ . '/api/class-wc-vk-api-client.php' );
			}

			if ( $vkontakte === false ) {
				$this->apiClient = $this->getApiClient();
			} else {
				$this->apiClient = $vkontakte;
				$this->init_settings_fields();
			}

			$this->references = new WC_VK_References( $this->apiClient );
			$this->model      = new WC_VKontakte_Model();
			$this->logger     = new WC_VK_Logger();

			if ( ! empty( static::$token_user ) && ! empty( static::$token_group ) ) {
				$this->pushStatisticActivation();
			}

			// Actions.

			add_action( 'woocommerce_update_options_integration_' . $this->id, array(
				$this,
				'process_admin_options'
			) );
			add_filter( 'woocommerce_settings_api_sanitized_fields_' . $this->id, array( $this, 'catch_settings' ) );
			add_action( 'admin_bar_menu', array( $this, 'add_vkontakte_button' ), 100 );
			add_action( 'vkontakte_import', array( $this, 'importOffer' ) );
			add_action( 'vkontakte_export', array( $this, 'exportOffer' ) );
			add_action( 'vk_market_order_event', array( $this, 'vk_order_event' ), 11, 1 );
			add_action( 'wp_ajax_get_token_user', array( $this, 'get_token_user' ) );
			add_action( 'wp_ajax_get_token_group', array( $this, 'get_token_group' ) );
			add_action( 'wp_ajax_subscribe_to_vk_events', array( $this, 'subscribe_to_vk_events' ) );
			add_action( 'wp_ajax_unsubscribe_to_vk_events', array( $this, 'unsubscribe_to_vk_events' ) );
			add_action( 'wp_ajax_clear_vk_logs', array( $this, 'clear_vk_logs' ) );
			add_action( 'admin_print_footer_scripts', array( $this, 'ajax_get_token_user' ), 99 );
			add_action( 'admin_print_footer_scripts', array( $this, 'ajax_get_token_group' ), 99 );
			add_action( 'admin_print_footer_scripts', array( $this, 'ajax_subscribe_to_vk_events' ), 99 );
			add_action( 'admin_print_footer_scripts', array( $this, 'ajax_unsubscribe_to_vk_events' ), 99 );
			add_action( 'admin_print_footer_scripts', array( $this, 'ajax_clear_vk_logs' ), 99 );
			add_action( 'woocommerce_order_edit_status', array( $this, 'vk_order_status_update' ), 11, 2 );

			// Deactivate hook

			add_action( 'vkontakte_deactivate', array( $this, 'unsubscribe_to_vk_events' ) );
			add_action( 'vkontakte_deactivate', array( $this, 'pushStatisticDeactivation' ) );
		}

		/**
		 * Get vkontakte api client
		 *
		 * @return false|VKApiClient
		 */
		public function getApiClient() {
			if ( get_option( 'vkontakte_token_user' ) && get_option( 'vkontakte_token_group' ) ) {
				return new VKApiClient(
					get_option( 'vkontakte_token_user' ),
					get_option( 'vkontakte_token_group' )
				);
			}

			return false;
		}

		/**
		 * Options
		 */

		/**
		 * Init settings fields
		 */
		public function init_settings_fields() {
			if ( ! empty( static::$token_user ) && ! empty( static::$token_group ) ) {
				$this->init_form_fields();
			} else {
				$this->init_form_fields_oauth();
			}

			$this->init_settings();
		}

		/**
		 * Interception of plugin settings before saving
		 *
		 * @param $settings
		 *
		 * @return array
		 */
		public function catch_settings( $settings ) {

			$this->trim_settings( $settings );

			if ( $this->check_options_oauth( $settings ) ) {
				$this->update_options_oauth( $settings );
				$settings['-id_group'] = '-' . $settings['id_group'];
			}

			if ( isset( $settings['import'] ) && $settings['import'] == static::YES ) {
				if ( ! wp_next_scheduled( 'vkontakte_import' ) ) {
					wp_schedule_event( time(), 'daily', 'vkontakte_import' );
				}
			} elseif ( isset( $settings['import'] ) && $settings['import'] == static::NO ) {
				wp_clear_scheduled_hook( 'vkontakte_import' );
			}

			if ( isset( $settings['export'] ) && $settings['export'] == static::YES ) {
				if ( ! wp_next_scheduled( 'vkontakte_export' ) ) {
					wp_schedule_event( time(), 'daily', 'vkontakte_export' );
				}
			} elseif ( isset( $settings['export'] ) && $settings['export'] == static::NO ) {
				wp_clear_scheduled_hook( 'vkontakte_export' );
			}

			return $settings;
		}

		/**
		 * Write to yourself options oauth
		 *
		 * @param $settings
		 */
		private function update_options_oauth( $settings ) {
			$oauth_settings = [
				'id_application' => $settings['id_application'],
				'secret_key'     => $settings['secret_key'],
				'id_group'       => $settings['id_group'],
				'-id_group'      => '-' . $settings['id_group']
			];

			update_option( 'vkontakte_oauth_settings', $oauth_settings );
		}

		/**
		 * @param $prefix
		 * @param bool $default
		 *
		 * @return array|mixed
		 */
		public static function get_integration_options( $prefix, $default = false ) {
			$options = array();

			foreach ( static::$options as $key => $value ) {
				if ( strripos( $key, $prefix ) !== false ) {
					$key             = str_replace( $prefix, '', $key );
					$options[ $key ] = $value;
				}
			}

			return $default === true ? $options['default'] : $options;
		}

		/**
		 * Logs
		 */

		/**
		 * Logs for page options
		 *
		 * @return array
		 */
		public static function get_vk_logs() {
			$short_log_path  = WP_PLUGIN_DIR . '/woo-vkontakte/logs/vk_short.log';
			$detail_log_path = WP_PLUGIN_DIR . '/woo-vkontakte/logs/vk_detailed_logs.log';
			$detail_log_url  = WP_PLUGIN_URL . '/woo-vkontakte/logs/vk_detailed_logs.log';

			return array(
				'short_log'       => file_exists( $short_log_path ) ? file_get_contents( $short_log_path ) : '',
				'detail_log_path' => file_exists( $detail_log_path ) && filesize( $detail_log_path ) > 0 ? $detail_log_url : '',
				'error'           => ( file_exists( $short_log_path ) && filesize( $short_log_path ) > 2097152 ) ? __( 'The log file is too large. Click on the clear button (detailed logs are not affected).', 'vkontakte' ) : ''
			);
		}

		/**
		 * Clear short logs
		 */
		public function clear_vk_logs() {
			$handle = fopen( WP_PLUGIN_DIR . '/woo-vkontakte/logs/vk_short.log', 'w+' );
			fclose( $handle );
		}

		/**
		 * Import
		 */

		/**
		 * @throws VKApiException
		 * @throws VKClientException
		 */
		public function importOffer() {
			if ( static::$options['import'] === self::YES ) {

				if ( ! function_exists( 'media_sideload_image' ) ) {
					require_once ABSPATH . 'wp-admin/includes/media.php';
					require_once ABSPATH . 'wp-admin/includes/file.php';
					require_once ABSPATH . 'wp-admin/includes/image.php';
				}

				if ( ! class_exists( 'WC_VKontakte_Import' ) ) {
					include_once( __DIR__ . '/class-wc-vkontakte-import.php' );
				}

				$import = new WC_VKontakte_Import();
				$import->import();

				$this->update_option( 'import', static::NO );
			}
		}

		/**
		 * Export
		 */

		/**
		 * @throws VKApiException
		 * @throws VKClientException
		 */
		public function exportOffer() {
			if ( static::$options['import'] === self::NO && static::$options['export'] === self::YES ) {
				if ( ! class_exists( 'WC_VKontakte_Export' ) ) {
					include_once( __DIR__ . '/class-wc-vkontakte-export.php' );
				}

				$export = new WC_VKontakte_Export();

				$category_list = static::get_integration_options( self::CATEGORY_LIST );

				if ( $this->get_option( 'album_by_parent_export' ) == static::YES ) {
					$category_list = $this->remove_child_categories( $category_list );
				} else {
					$category_list = $this->remove_parent_categories( $category_list );
				}

				if ( $this->checkModifiedCategoryList( $category_list ) || ! $this->checkFileProductsForExport() ) {
					$categories = $export->createAlbums( $category_list );
					$export->addProducts( $categories );
				} else {
					$export->addProducts( $this->checkFileProductsForExport(), 'products' );
				}
			}
		}

		/**
		 * Have settings changed since the last export
		 *
		 * @param $category_list_current
		 *
		 * @return bool
		 */
		private function checkModifiedCategoryList( $category_list_current ) {
			if ( file_exists( WP_PLUGIN_DIR . '/woo-vkontakte/files/category_setting.json' ) ) {
				$category_list = json_decode( file_get_contents( WP_PLUGIN_DIR . '/woo-vkontakte/files/category_setting.json' ), true );
			}

			if ( empty( $category_list ) || $category_list != $category_list_current ) {
				file_put_contents( WP_PLUGIN_DIR . '/woo-vkontakte/files/category_setting.json', json_encode( $category_list_current ) );

				return true;
			}

			return false;
		}

		/**
		 * Deletes categories with all children selected for export
		 *
		 * @param $category_list
		 *
		 * @return mixed
		 */
		private function remove_parent_categories( $category_list ) {
			foreach ( $category_list as $id => $parent_id ) {
				if ( key_exists( $parent_id, $category_list ) ) {

					unset( $category_list[ $parent_id ] );
				}
			}

			return array_keys( $category_list );
		}

		/**
		 * Removes categories of parents selected for export
		 *
		 * @param $category_list
		 *
		 * @return array
		 */
		private function remove_child_categories( $category_list ) {
			$result = array();

			foreach ( $category_list as $id => $parent_id ) {
				if ( ! key_exists( $parent_id, $category_list ) ) {
					$result[] = $id;
				}
			}

			return $result;
		}

		/**
		 * Check products for export from file
		 *
		 * @return array|bool|mixed
		 */
		private function checkFileProductsForExport() {
			if ( file_exists( __DIR__ . '/../files/products_for_export.json' ) ) {
				$products = json_decode( file_get_contents( __DIR__ . '/../files/products_for_export.json' ), true );
			} else {
				$products = array();
			}

			return ! empty( $products ) ? $products : false;
		}

		/**
		 * Orders
		 */

		/**
		 * @param $orderFromVk
		 */
		public function vk_order_event( $orderFromVk ) {
			try {
				$order = new WC_VK_Order();

				if ( $orderFromVk['type_method'] == 'create' ) {
					$wc_order_id = $order->createOrder_WC( $orderFromVk );
				} elseif ( $orderFromVk['type_method'] == 'update' ) {
					$wc_order_id = $order->updateOrder_WC( $orderFromVk );
				}

				$wc_order = wc_get_order( $wc_order_id );

				if ( $wc_order instanceof WC_Order ) {
					$wc_order->calculate_totals();
				}
			} catch ( Exception $exception ) {
				$this->logger->write(
					sprintf(
						"[%s '%s'] - %s",
						$exception->getCode(),
						$exception->getMessage(),
						'Exception in file - ' . $exception->getFile() . ' on line ' . $exception->getLine()
					)
				);
			}
		}

		/**
		 * @param $order_id
		 * @param $new_status
		 *
		 * @throws VKApiException
		 * @throws VKClientException
		 */
		public function vk_order_status_update( $order_id, $new_status ) {
			$order = new WC_VK_Order();

			$wc_order_id = $order->UpdateStatus_VK( $order_id, $new_status );

			if ( $wc_order_id ) {
				$wc_order = wc_get_order( $wc_order_id );

				if ( $wc_order instanceof WC_Order ) {
					$wc_order->calculate_totals();
				}
			}
		}

		/**
		 * Managing VKontakte event settings
		 */

		/**
		 * Subscribe to Vk events
		 *
		 * @throws VKApiException
		 * @throws VKClientException
		 */
		public function subscribe_to_vk_events() {
			$this->getCallbackConfirmationCode();
			$serverId = $this->addCallbackServer();

			sleep( 4 );

			$this->setCallbackSettings( $serverId );

			if ( ! empty( $serverId ) ) {
				static::$options_event['server_id'] = $serverId;
				static::$options_event['status']    = 1;
			}

			update_option( 'vkontakte_events', static::$options_event );

//			echo true;
//			wp_die();
		}

		/**
		 * Unsubscribe to Vk events (Delete callback api server)
		 *
		 * @throws VKApiException
		 * @throws VKClientException
		 */
		public function unsubscribe_to_vk_events() {
			if ( static::$options_event['status'] === 1 && ! empty( static::$options_event['server_id'] ) ) {
				$this->apiClient->methods()->groups_deleteCallbackServer(
					array(
						'group_id'  => static::$options_oauth['id_group'],
						'server_id' => static::$options_event['server_id']
					)
				);

				static::$options_event['status'] = 0;
				unset( static::$options_event['server_id'] );

				update_option( 'vkontakte_events', static::$options_event );
			}

//			echo true;
//			wp_die();
		}

		/**
		 * Get code for subscribe to VK events
		 *
		 * @return mixed
		 * @throws VKClientException
		 *
		 * @throws VKApiException
		 */
		private function getCallbackConfirmationCode() {
			$response = $this->apiClient->methods()->groups_getCallbackConfirmationCode(
				array(
					'group_id' => static::$options_oauth['id_group']
				)
			);

			return update_option( 'vkontakte_events_code', $response['code'] );
		}

		/**
		 * Add callback api server
		 *
		 * @return int
		 * @throws VKClientException
		 *
		 * @throws VKApiException
		 */
		private function addCallbackServer() {
			$response = $this->apiClient->methods()->groups_addCallbackServer(
				array(
					'group_id'   => static::$options_oauth['id_group'],
					'url'        => WP_PLUGIN_URL . '/woo-vkontakte/include/wc-vkontakte-events.php',
					'title'      => substr( get_option( 'blogname' ), 0, 14 ),
					'secret_key' => static::$options_oauth['secret_key']
				)
			);

			return $response['server_id'];
		}

		/**
		 * Set callback server settings
		 *
		 * @param $serverId
		 *
		 * @return int
		 * @throws VKClientException
		 *
		 * @throws VKApiException
		 */
		private function setCallbackSettings( $serverId ) {
			return $this->apiClient->methods()->groups_setCallbackSettings(
				array(
					'group_id'          => static::$options_oauth['id_group'],
					'server_id'         => $serverId,
					'api_version'       => VKApiClient::CALLBACK_API_VERSION,
					'market_order_new'  => 1,
					'market_order_edit' => 1
				)
			);
		}

		/**
		 * Plugin authorization
		 */

		/**
		 * Plugin authorization on VKontakte
		 */
		public function get_token_user() {
			if ( ! class_exists( 'VKOAuthUserScope' ) ) {
				require_once( __DIR__ . '/api/class-wc-vk-oauth-scope.php' );
			}

			$scope = array(
				VKOAuthUserScope::U_OFFLINE,
				VKOAuthUserScope::U_MARKET,
				VKOAuthUserScope::U_PHOTOS,
				VKOAuthUserScope::U_GROUPS
			);

			echo $this->get_browser_url( $scope );
			wp_die();
		}

		/**
		 * Plugin authorization on VKontakte
		 */
		public function get_token_group() {
			if ( ! class_exists( 'VKOAuthUserScope' ) ) {
				require_once( __DIR__ . '/api/class-wc-vk-oauth-scope.php' );
			}

			$scope = array(
				VKOAuthUserScope::G_MANAGE
			);

			echo $this->get_browser_url( $scope, true );
			wp_die();
		}

		/**
		 * Get browser_url
		 *
		 * @param $scope
		 * @param bool $groups
		 *
		 * @return string
		 */
		private function get_browser_url( $scope, $groups = false ) {
			if ( ! class_exists( 'VKOAuth' ) ) {
				require_once( __DIR__ . '/api/class-wc-vk-oauth.php' );
			}

			$options = get_option( 'vkontakte_oauth_settings' );

			$options['redirect_uri'] = WP_PLUGIN_URL . '/woo-vkontakte/include/wc-vkontakte-oauth.php';
			$options['plugin_url']   = get_site_url() . '/wp-admin/admin.php?page=wc-settings&tab=integration&section=integration-vkontakte';

			$response_type = VKOAuthUserScope::CODE;
			$client_id     = $options['id_application'];
			$redirect_uri  = $options['redirect_uri'];
			$display       = VKOAuthUserScope::PAGE;
			$state         = rand( 0, 999999 );

			$groups_ids = $groups
				? array( $options['id_group'] )
				: null;

			update_option( 'vkontakte_state', $state );
			update_option( 'vkontakte_options_tmp', $options );

			$vk_oauth = new VKOAuth();

			return $vk_oauth->getAuthorizeUrl( $response_type, $client_id, $redirect_uri, $display, $scope, $state, $groups_ids );
		}

		/**
		 * Send statistics about module activation
		 */
		public function pushStatisticActivation() {
			if ( ! empty( static::$options_oauth['id_group'] )
			     && (
				     empty( get_option( 'vkontakte_statistic' ) )
				     || self::VK_MODULE_VERSION != get_option( 'vkontakte_module_version' )
			     )
			) {
				$statistic = VK_push_statistic( [
					'shopUrl'  => get_option( 'siteurl' ),
					'groupId'  => static::$options_oauth['id_group'],
					'version'  => self::VK_MODULE_VERSION,
					'isActive' => true
				] );

				update_option( 'vkontakte_statistic', $statistic );
				update_option( 'vkontakte_module_version', self::VK_MODULE_VERSION );
			}
		}

		/**
		 * Send statistics about module deactivation
		 */
		public function pushStatisticDeactivation() {
			VK_push_statistic( [
				'shopUrl'  => get_option( 'siteurl' ),
				'groupId'  => static::$options_oauth['id_group'],
				'version'  => self::VK_MODULE_VERSION,
				'isActive' => false
			] );

			update_option( 'vkontakte_statistic', 0 );
		}
	}
}
