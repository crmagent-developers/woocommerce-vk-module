<?php

define( 'SHORTINIT', true );
require_once( $_SERVER['DOCUMENT_ROOT'] . '/wp-load.php' );


if ( empty( $_GET['state'] ) || $_GET['state'] !== get_option( 'vkontakte_state' ) ) {
	wp_die();
}

if ( ! empty( $_GET['code'] ) ) {

	if ( ! class_exists( 'VKOAuth' ) ) {
		include_once( __DIR__ . '/api/class-wc-vk-oauth.php' );
	}

	$options = get_option( 'vkontakte_options_tmp' );

	$vk_oauth = new VKOAuth();

	try {
		$response = $vk_oauth->getAccessToken(
			$options['id_application'],
			$options['secret_key'],
			$options['redirect_uri'],
			$_GET['code']
		);
	} catch ( VKClientException $e ) {
		//todo catch
	}

	if ( isset( $response['access_token'] ) ) {
		update_option( 'vkontakte_token_user', $response['access_token'] );
	}

	if ( isset( $response[ 'access_token_' . $options['id_group'] ] ) ) {
		update_option( 'vkontakte_token_group', $response[ 'access_token_' . $options['id_group'] ] );
	}

	$plagin_settings_url = $options['plugin_url'];

	delete_option( 'vkontakte_state' );
	delete_option( 'vkontakte_options_tmp' );

	header( 'Location: ' . str_replace( array( '&amp;', "\n", "\r" ), array(
			'&',
			'',
			''
		), $plagin_settings_url ), true, 302 );
	exit();
}