<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

if ( ! class_exists( 'WC_VKontakte_Plugin' ) ) :

	class WC_VKontakte_Plugin {

		public $file;

		private static $instance = null;

		public static function getInstance( $file ) {
			if ( self::$instance === null ) {
				self::$instance = new self( $file );
			}

			return self::$instance;
		}

		private function __construct( $file ) {
			$this->file = $file;
		}

		/**
		 * Registering the module activation method
		 */
		public function register_activation_hook() {
			register_activation_hook( $this->file, array( $this, 'activate' ) );
		}

		/**
		 * Registering a module deactivation method
		 */
		public function register_deactivation_hook() {
			register_deactivation_hook( $this->file, array( $this, 'deactivate' ) );
		}

		/**
		 * Activation method
		 */
		public function activate() {
			if ( ! class_exists( 'WC_Integration' ) ) {
				add_action( 'admin_notices', array( new WC_Integration_VKontakte(), 'woocommerce_missing_notice' ) );

				return;
			}

			if ( ! class_exists( 'WC_VKontakte_Base' ) ) {
				require_once( dirname( __FILE__ ) . '/class-wc-vkontakte-base.php' );
			}

			if ( ! get_option( 'vkontakte_db_version' ) ) {

				if ( ! class_exists( 'WC_VKontakte_Model' ) ) {
					require_once( dirname( __FILE__ ) . '/models/class-wc-vkontakte-model.php' );
				}

				$vk_model = new WC_VKontakte_Model;
				$vk_model->createTables();
			}

			update_option( 'vkontakte_events', array( 'status' => 0 ) );
		}

		/**
		 * Deactivation method
		 */
		public function deactivate() {
			do_action( 'vkontakte_deactivate' );

			if ( wp_next_scheduled( 'vkontakte_import' ) ) {
				wp_clear_scheduled_hook( 'vkontakte_import' );
			}

			if ( wp_next_scheduled( 'vkontakte_export' ) ) {
				wp_clear_scheduled_hook( 'vkontakte_export' );
			}
		}

		/**
		 * Unset empty fields
		 *
		 * @param array $arr input array
		 *
		 * @return array
		 */
		public static function clearArray(array $arr)
		{
			if (!is_array($arr)) {
				return $arr;
			}

			$result = array();

			foreach ($arr as $index => $node) {
				$result[$index] = (is_array($node))
					? self::clearArray($node)
					: $node;

				if ($result[$index] === ''
				    || $result[$index] === null
				    || (is_array($result[$index]) && count($result[$index]) < 1)
				) {
					unset($result[$index]);
				}
			}

			return $result;
		}
	}

endif;