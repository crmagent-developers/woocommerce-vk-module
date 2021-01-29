<?php


if ( ! class_exists( 'WC_VKontakte_Model' ) ) {

	class WC_VKontakte_Model {

		/**
		 * Table versions
		 */
		const VK_DB_VERSION = "1.0";

		/**
		 * Table names
		 */
		const VK_DB_ALBUMS = 'vk_albums';
		const VK_DB_PRODUCTS = 'vk_products';
		const VK_DB_ORDERS = 'vk_orders';
		const VK_DB_CUSTOMERS = 'vk_customers';
		const VK_DB_IMAGES = 'vk_images';

		/**
		 * @var wpdb
		 */
		protected $wpdb;

		/**
		 * @var string
		 */
		public $prefix;

		public function __construct() {
			global $wpdb;

			$this->wpdb   = $wpdb;
			$this->prefix = $wpdb->prefix;
		}

		/**
		 * @return VK_Model_Albums
		 */
		public function albums() {
			if ( ! class_exists( 'VK_Model_Albums' ) ) {
				require_once( dirname( __FILE__ ) . '/class-wc-vkontakte-model-albums.php' );
			}

			return new VK_Model_Albums();
		}

		/**
		 * @return VK_Model_Products
		 */
		public function products() {
			if ( ! class_exists( 'VK_Model_Products' ) ) {
				require_once( dirname( __FILE__ ) . '/class-wc-vkontakte-model-products.php' );
			}

			return new VK_Model_Products();
		}

		/**
		 * @return VK_Model_Orders
		 */
		public function orders() {
			if ( ! class_exists( 'VK_Model_Orders' ) ) {
				require_once( dirname( __FILE__ ) . '/class-wc-vkontakte-model-orders.php' );
			}

			return new VK_Model_Orders();
		}

		/**
		 * @return VK_Model_Customers
		 */
		public function customers() {
			if ( ! class_exists( 'VK_Model_Customers' ) ) {
				require_once( dirname( __FILE__ ) . '/class-wc-vkontakte-model-customers.php' );
			}

			return new VK_Model_Customers();
		}

		/**
		 * @return VK_Model_Images
		 */
		public function images() {
			if ( ! class_exists( 'VK_Model_Images' ) ) {
				require_once( dirname( __FILE__ ) . '/class-wc-vkontakte-model-images.php' );
			}

			return new VK_Model_Images();
		}

		/**
		 * Creating tables when installing the plugin
		 */
		public function createTables() {
			$this->createTable(
				$this->prefix . self::VK_DB_ALBUMS,
				"CREATE TABLE " . $this->prefix . self::VK_DB_ALBUMS . " (
					" . $this->prefix . "id INT(11),
					" . $this->prefix . "parent_id INT(11),
					" . $this->prefix . "name VARCHAR(255),
					vk_id INT(11),
					`date_added` datetime NOT NULL,
					`date_modified` datetime NOT NULL
				);"
			);

			$this->createTable(
				$this->prefix . self::VK_DB_PRODUCTS,
				"CREATE TABLE " . $this->prefix . self::VK_DB_PRODUCTS . " (
					" . $this->prefix . "id INT(11),
					vk_id INT(11),
					categories_albums TEXT, 
					offer TEXT,
					`date_added` datetime NOT NULL,
					`date_modified` datetime NOT NULL
				);"
			);

			$this->createTable(
				$this->prefix . self::VK_DB_ORDERS,
				"CREATE TABLE " . $this->prefix . self::VK_DB_ORDERS . " (
					" . $this->prefix . "id INT(11),
					" . $this->prefix . "status VARCHAR(255),
					vk_id INT(11), vk_status INT(11),
					vk_user_id INT(11),
					json_last_event MEDIUMTEXT,
					`date_added` datetime NOT NULL,
					`date_modified` datetime NOT NULL
				);"
			);

			$this->createTable(
				$this->prefix . self::VK_DB_CUSTOMERS,
				"CREATE TABLE " . $this->prefix . self::VK_DB_CUSTOMERS . " (
					" . $this->prefix . "id INT(11), 
					vk_id INT(11), 
					`date_added` datetime NOT NULL,
					`date_modified` datetime NOT NULL
				);"
			);

			$this->createTable(
				$this->prefix . self::VK_DB_IMAGES,
				"CREATE TABLE " . $this->prefix . self::VK_DB_IMAGES . " (
					`type` VARCHAR(255),
					" . $this->prefix . "source_id VARCHAR(255),
					" . $this->prefix . "path VARCHAR(255),
					vk_id INT(11),
					`date_added` datetime NOT NULL,
					`date_modified` datetime NOT NULL
				);"
			);

			update_option( "vkontakte_db_version", self::VK_DB_VERSION );
		}

		/**
		 * Creating a table
		 *
		 * @param $table_name
		 * @param $sql
		 */
		private function createTable( $table_name, $sql ) {
			if ( $this->wpdb->get_var( "SHOW TABLES LIKE '$table_name'" ) != $table_name ) {
				require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
				dbDelta( $sql );
			}

			$this->wpdb->query( "ALTER TABLE " . $table_name . " CONVERT TO CHARACTER SET utf8;" );
		}

		/**
		 * Deleting tables when uninstalling a plugin
		 */
		public function dropTables() {
			$this->wpdb->query( "DROP TABLE " . $this->prefix . self::VK_DB_ALBUMS );
			$this->wpdb->query( "DROP TABLE " . $this->prefix . self::VK_DB_PRODUCTS );
			$this->wpdb->query( "DROP TABLE " . $this->prefix . self::VK_DB_ORDERS );
			$this->wpdb->query( "DROP TABLE " . $this->prefix . self::VK_DB_CUSTOMERS );
			$this->wpdb->query( "DROP TABLE " . $this->prefix . self::VK_DB_IMAGES );
		}

		/**
		 * Rebuild the array to return
		 *
		 * @param $query
		 * @param $return
		 *
		 * @return array|bool
		 */
		protected function refactorResult( $query, $return ) {

			if ( empty( $query ) || ! is_array( $query ) ) {
				return null;
			}

			$data = [];

			if ( $return !== 'all' ) {
				foreach ( $query as $row ) {
					if ( key_exists( $return, $row ) ) {
						$data = $row[ $return ];
					}
				}
			} else {
				foreach ( $query as $row ) {
					$data = $row;
				}
			}

			return ! empty( $data ) ? $data : false;
		}
	}
}