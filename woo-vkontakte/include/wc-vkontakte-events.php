<?php

require_once( $_SERVER['DOCUMENT_ROOT'] . '/wp-load.php' );


$data          = json_decode( file_get_contents( 'php://input' ), true );
$options_oauth = get_option( 'vkontakte_oauth_settings' );

if ( $data['group_id'] != (int) $options_oauth['id_group'] || $data['secret'] != $options_oauth['secret_key'] ) {
	die();
}

switch ( $data['type'] ) {
	case 'confirmation':
		$code = get_option( 'vkontakte_events_code' );
		delete_option( 'vkontakte_events_code' );
		echo $code;

		break;
	case 'market_order_new':
		$data['object']['type_method'] = 'create';

		do_action( 'vk_market_order_event', $data['object'] );
		echo( 'ok' );

		break;
	case 'market_order_edit':
		$data['object']['type_method'] = 'update';

		do_action( 'vk_market_order_event', $data['object'] );
		echo( 'ok' );

		break;
	default:
		echo( 'ok' );

		break;
}