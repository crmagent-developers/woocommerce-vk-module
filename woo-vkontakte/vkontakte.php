<?php

/**
 * Version:                 1.0.0
 * WC requires at least:    3.0
 * WC tested up to:         4.7.1
 * Plugin Name:             WooCommerce VKontakte
 * Description:             Integration plugin for WooCommerce & VKontakte
 * Requires PHP:            5.3
 * Text Domain:             vkontakte
 * Domain Path:             /lang
 */

if ( ! defined( 'ABSPATH' ) || ! function_exists( 'add_action' ) ) {
	exit; // Exit if accessed directly
}

if ( ! class_exists( 'WC_Integration_VKontakte' ) ) :

	/**
	 * Class WC_Integration_VKontakte
	 */
	class WC_Integration_VKontakte {
		const WOOCOMMERCE_SLUG = 'woocommerce';
		const WOOCOMMERCE_PLUGIN_PATH = 'woocommerce/woocommerce.php';

		private static $instance;

		public static function get_instance() {
			if ( null === self::$instance ) {
				self::$instance = new self();
			}

			return self::$instance;
		}

		/**
		 * Construct the plugin.
		 */
		public function __construct() {
			// Подгрузка языковых пакетов
			$this->load_plugin_textdomain();

			// Проверка наличия активированного плагина woocommerce
			if ( class_exists( 'WC_Integration' ) ) {
				self::load_module();
				add_filter( 'woocommerce_integrations', array( $this, 'add_integration' ) );
			} else {
				// в случае отсутствия woocommerce выводим сообщение об ошибке
				add_action( 'admin_notices', array( $this, 'woocommerce_missing_notice' ) );
			}
		}

		/**
		 * Error message output
		 */
		public function woocommerce_missing_notice() {
			if ( static::isWooCommerceInstalled() ) {
				if ( ! is_plugin_active( static::WOOCOMMERCE_PLUGIN_PATH ) ) {
					echo '
                <div class="error">
                    <p>
                        Activate WooCommerce in order to enable VKontakte integration!
                        <a href="' . wp_nonce_url( admin_url( 'plugins.php' ) ) . '" aria-label="Activate WooCommerce">
                            Click here to open plugins manager
                        </a>
                    </p>
                </div>
                ';
				}
			} else {
				echo '
            <div class="error">
                <p>
                    <a href="'
				     . static::generatePluginInstallationUrl( static::WOOCOMMERCE_SLUG )
				     . '" aria-label="Install WooCommerce">Install WooCommerce</a> in order to enable VKontakte integration!
                </p>
            </div>
            ';
			}
		}

		/**
		 * Connecting language packs
		 */
		public function load_plugin_textdomain() {
			load_plugin_textdomain( 'vkontakte', false, dirname( plugin_basename( __FILE__ ) ) . '/lang/' );
		}

		/**
		 * Add a new integration to WooCommerce.
		 *
		 * @param $integrations
		 *
		 * @return array
		 */
		public function add_integration( $integrations ) {
			$integrations[] = 'WC_VKontakte_Base';

			return $integrations;
		}

		/**
		 * Loads module classes.
		 */
		public static function load_module() {
			require_once( self::checkCustomFile( 'include/models/class-wc-vkontakte-model.php' ) );
			require_once( self::checkCustomFile( 'include/api/class-wc-vk-api-client.php' ) );
			require_once( self::checkCustomFile( 'include/class-wc-vkontakte-abstracts-settings.php' ) );
			require_once( self::checkCustomFile( 'include/class-wc-vkontakte-base.php' ) );
			require_once( self::checkCustomFile( 'include/class-wc-vkontakte-customer.php' ) );
			require_once( self::checkCustomFile( 'include/class-wc-vkontakte-export.php' ) );
			require_once( self::checkCustomFile( 'include/class-wc-vkontakte-import.php' ) );
			require_once( self::checkCustomFile( 'include/class-wc-vkontakte-logger.php' ) );
			require_once( self::checkCustomFile( 'include/class-wc-vkontakte-order.php' ) );
			require_once( self::checkCustomFile( 'include/class-wc-vkontakte-references.php' ) );
			require_once( self::checkCustomFile( 'include/class-wc-vkontakte-plugin.php' ) );
			require_once( self::checkCustomFile( 'include/functions.php' ) );
		}

		/**
		 * Check custom file
		 *
		 * @param string $file
		 *
		 * @return string
		 */
		public static function checkCustomFile( $file ) {
			$wooPath        = WP_PLUGIN_DIR . '/woo-vkontakte/' . $file;
			$withoutInclude = WP_CONTENT_DIR . '/vkontakte-custom/' . str_replace( 'include/', '', $file );

			if ( file_exists( $withoutInclude ) ) {
				return $withoutInclude;
			}

			if ( file_exists( $wooPath ) ) {
				return $wooPath;
			}

			return dirname( __FILE__ ) . '/' . $file;
		}

		/**
		 * Returns true if WooCommerce was found in plugin cache
		 *
		 * @return bool
		 */
		private function isWooCommerceInstalled() {
			$plugins = wp_cache_get( 'plugins', 'plugins' );

			if ( ! $plugins ) {
				$plugins = get_plugins();
			} elseif ( isset( $plugins[''] ) ) {
				$plugins = $plugins[''];
			}

			if ( ! isset( $plugins[ static::WOOCOMMERCE_PLUGIN_PATH ] ) ) {
				return false;
			}

			return true;
		}

		/**
		 * Generate plugin installation url
		 *
		 * @param $pluginSlug
		 *
		 * @return string
		 */
		private function generatePluginInstallationUrl( $pluginSlug ) {
			$action = 'install-plugin';

			return wp_nonce_url(
				add_query_arg(
					array(
						'action' => $action,
						'plugin' => $pluginSlug
					),
					admin_url( 'update.php' )
				),
				$action . '_' . $pluginSlug
			);
		}
	}

	if ( ! class_exists( 'WC_VKontakte_Plugin' ) ) {
		require_once( dirname( __FILE__ ) . '/include/class-wc-vkontakte-plugin.php' );
	}

	$plugin = WC_VKontakte_Plugin::getInstance( __FILE__ );
	$plugin->register_activation_hook();
	$plugin->register_deactivation_hook();

	add_action( 'plugins_loaded', array( 'WC_Integration_VKontakte', 'get_instance' ), 0 );


endif;
