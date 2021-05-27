<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

/**
 * @return mixed|void
 */
function VK_get_wc_shipping_methods() {
	$wc_shipping      = WC_Shipping::instance();
	$shipping_methods = $wc_shipping->get_shipping_methods();

	$result = array();

	foreach ( $shipping_methods as $code => $shipping ) {
		$result[ $code ] = array(
			'name'        => $shipping->method_title,
			'enabled'     => $shipping->enabled,
			'description' => $shipping->method_description,
			'title'       => $shipping->title ? $shipping->title : $shipping->method_title
		);
	}

	return apply_filters( 'retailcrm_shipping_list', WC_VKontakte_Plugin::clearArray( $result ) );
}

/**
 * Request for sending statistics
 *
 * @param $data
 *
 * @return string
 */
function VK_push_statistic($data)
{
	$data['platform'] = 'woocommerce';

	$ch = curl_init();

	$setopt = [
		CURLOPT_URL => 'https://dev.crmagent.ru/vk/push',
		CURLOPT_RETURNTRANSFER => 1,
		CURLOPT_POST => true,
		CURLOPT_POSTFIELDS => json_encode($data),
	];

	curl_setopt_array($ch, $setopt);
	$response = curl_exec($ch);

	curl_close($ch);

	return $response;
}
