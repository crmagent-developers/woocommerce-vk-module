<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

// If uninstall not called from WordPress, then exit.
if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	exit;
}

global $wpdb;

wp_clear_scheduled_hook( 'vkontakte_import' );
wp_clear_scheduled_hook( 'vkontakte_export' );

if ( ! class_exists( 'WC_VKontakte_Model' ) ) {
	require_once( dirname( __FILE__ ) . '/include/models/class-wc-vkontakte-model.php' );
}

$vk_model = new WC_VKontakte_Model;
$vk_model->dropTables();

// Delete options.
$wpdb->query( "DELETE FROM $wpdb->options WHERE option_name = 'woocommerce_integration-vkontakte_settings';" );
$wpdb->query( "DELETE FROM $wpdb->options WHERE option_name = 'vkontakte_token_user';" );
$wpdb->query( "DELETE FROM $wpdb->options WHERE option_name = 'vkontakte_token_group';" );
$wpdb->query( "DELETE FROM $wpdb->options WHERE option_name = 'vkontakte_oauth_settings';" );
$wpdb->query( "DELETE FROM $wpdb->options WHERE option_name = 'vkontakte_events';" );
$wpdb->query( "DELETE FROM $wpdb->options WHERE option_name = 'vkontakte_events_code';" );
$wpdb->query( "DELETE FROM $wpdb->options WHERE option_name = 'vkontakte_db_version';" );
$wpdb->query( "DELETE FROM $wpdb->options WHERE option_name = 'vkontakte_statistic';" );
$wpdb->query( "DELETE FROM $wpdb->options WHERE option_name = 'vkontakte_module_version';" );

// Clear any cached data that has been removed
wp_cache_flush();
