<?php

if ( ! class_exists( 'WC_VK_Order' ) ) :


	class WC_VK_Order extends WC_VKontakte_Base {
		/**
		 * @param $orderFromVk
		 *
		 * @return bool|int
		 *
		 * @throws VKApiException
		 * @throws VKClientException
		 * @throws WC_Data_Exception
		 */
		public function createOrder_WC( $orderFromVk ) {

			if ( $this->model->orders()->get( $orderFromVk['id'], 'vk_id' ) ) {
				return false;
			}

			if ( isset( $orderFromVk['delivery']['type'] ) ) {
				$address         = $this->getAddress( $orderFromVk['delivery'] );
				$deliveryCode_WC = $this->getDeliveryCode_WC( $orderFromVk['delivery']['type'] );
			} else {
				$address         = array( 'address' => $orderFromVk['delivery']['address'] );
				$deliveryCode_WC = $this->getDeliveryCode_WC();
			}

			$customer   = $this->getDataCustomer( $orderFromVk );
			$customerId = $this->model->customers()->get( $orderFromVk['user_id'], 'vk_id', $this->model->prefix . 'id' );

			if ( ! $customerId ) {
				$customer_builder = new WC_VK_Customer( $this->model );
				$customerId       = $customer_builder->createCustomer( $customer, $address );
			}

			$args = array(
				'status'      => $this->getStatus( $orderFromVk['status'], true ),
				'customer_id' => $customerId
			);

			/** @var WC_Order|WP_Error $wc_order */
			$wc_order = wc_create_order( $args );
			$wc_order->set_date_created( date( 'Y-m-d H:i:s', $orderFromVk['date'] ) );

			if ( $wc_order instanceof WP_Error ) {
				$this->logger->write( sprintf(
					'[%d] error while creating order: %s',
					$orderFromVk['id'],
					print_r( $wc_order->get_error_messages(), true )
				) );

				return false;
			}

			$comment_default = sprintf(
				'Ссылка на страницу покупателя в вконтакте - https://vk.com/id%s</n></r>
			Номер заказа в вконтакте - №%s</n></r>',
				$orderFromVk['user_id'],
				$orderFromVk['display_order_id']
			);

			$wc_order->add_order_note( $comment_default, 0, false );

			if ( ! empty( $orderFromVk['comment'] ) ) {
				$wc_order->set_customer_note( $orderFromVk['comment'] );
			}

			$address_shipping = array(
				'first_name' => isset( $customer['firstName'] ) ? $customer['firstName'] : '',
				'last_name'  => isset( $customer['lastName'] ) ? $customer['lastName'] : '',
				'company'    => '',
				'address_1'  => isset( $address['address'] ) ? $address['address'] : '',
				'address_2'  => '',
				'city'       => isset( $address['city'] ) ? $address['city'] : '',
				'state'      => isset( $address['region'] ) ? $address['region'] : '',
				'postcode'   => isset( $address['postcode'] ) ? $address['postcode'] : '',
				'country'    => isset( $address['country'] ) ? $address['country'] : ''
			);

			$address_billing = array(
				'first_name' => isset( $customer['firstName'] ) ? $customer['firstName'] : '',
				'last_name'  => isset( $customer['lastName'] ) ? $customer['lastName'] : '',
				'company'    => '',
				'email'      => isset( $customer['email'] ) ? $customer['email'] : '',
				'phone'      => isset( $customer['phone'] ) ? $customer['phone'] : '',
				'address_1'  => isset( $address['address'] ) ? $address['address'] : '',
				'address_2'  => '',
				'city'       => isset( $address['city'] ) ? $address['city'] : '',
				'state'      => isset( $address['region'] ) ? $address['region'] : '',
				'postcode'   => isset( $address['postcode'] ) ? $address['postcode'] : '',
				'country'    => isset( $address['country'] ) ? $address['country'] : ''
			);

			$payment           = WC_Payment_Gateways::instance();
			$payment_types     = $payment->payment_gateways();
			$payments_settings = static::get_integration_options( static::TYPES_OF_PAYMENTS, true );

			if ( ! empty( $payments_settings ) && isset( $payment_types[ $payments_settings ] ) ) {
				$wc_order->set_payment_method( $payment_types[ $payments_settings ] );
			}

			$wc_order->set_address( $address_billing, 'billing' );
			$wc_order->set_address( $address_shipping, 'shipping' );

			$product_data = $this->getProductsOrder( $orderFromVk );

			if ( $product_data ) {
				foreach ( $product_data as $key => $product ) {
					$wc_item_id = $this->model->products()->get( $product['item_id'], 'vk_id', $this->model->prefix . 'id' );

					if ( ! $wc_item_id ) {
						$this->logger->write(
							sprintf( 'Product not found in VK table by ' . $product['item_id'] )
						);

						continue;
					}

					$item = wc_get_product( $wc_item_id );

					if ( ! $item ) {
						$this->logger->write(
							sprintf( 'Product not found in Woocommerce by ' . $wc_item_id )
						);

						continue;
					}

					$wc_order->add_product(
						$item,
						$product['quantity'],
						array(
							'subtotal' => wc_get_price_excluding_tax(
								$item,
								array(
									'price' => isset( $product['item']['price']['old_amount'] ) ? $product['item']['price']['old_amount'] / 100 : $product['item']['price']['amount'] / 100,
									'qty'   => $product['quantity'],
								)
							),
							'total'    => wc_get_price_excluding_tax(
								$item,
								array(
									'price' => $product['item']['price']['amount'] / 100,
									'qty'   => $product['quantity'],
								)
							),
						)
					);
				}
			}

			$shipping         = new WC_Order_Item_Shipping();
			$shipping_methods = VK_get_wc_shipping_methods();
			$shipping->set_method_title( $shipping_methods[ $deliveryCode_WC ]['name'] );
			$shipping->set_method_id( $deliveryCode_WC );

			$shipping_price = $this->getShippingPrice( $orderFromVk['price_details'] );

			if ( $shipping_price ) {
				$shipping->set_total( $shipping_price );
			}

			$shipping->set_order_id( $wc_order->get_id() );

			$shipping->save();
			$wc_order->add_item( $shipping );

			$data_for_base = array(
				$this->model->prefix . 'id'     => $wc_order->get_id(),
				$this->model->prefix . 'status' => $wc_order->get_status(),
				'vk_id'                         => $orderFromVk['id'],
				'vk_status'                     => $orderFromVk['status'],
				'vk_user_id'                    => $orderFromVk['user_id'],
				'json_last_event'               => json_encode( $orderFromVk )
			);

			$wc_order->save();
			$this->model->orders()->insert( $data_for_base );

			return $wc_order->get_id();
		}

		/**
		 * @param $orderFromVk
		 *
		 * @return bool|int
		 */
		public function updateOrder_WC( $orderFromVk ) {
			$data_from_base = $this->model->orders()->get( $orderFromVk['id'], 'vk_id' );
			$wc_order       = wc_get_order( $data_from_base[ $this->model->prefix . 'id' ] );

			if ( ! $wc_order instanceof WC_Order ) {
				return false;
			}

			$changes = array(
				'vk_id'           => $orderFromVk['id'],
				'json_last_event' => json_encode( $orderFromVk )
			);

			$order_status_wc = $this->getStatus( $orderFromVk['status'] );

			if ( $orderFromVk['status'] != $data_from_base['vk_status'] && ! empty( $order_status_wc ) ) {
				$wc_order->update_status( $order_status_wc );
				$changes['vk_status']                       = $orderFromVk['status'];
				$changes[ $this->model->prefix . 'status' ] = $order_status_wc;
			}

			if ( $this->check_track_number( $orderFromVk, $data_from_base ) ) {
				$delivery_info = sprintf(
					'Трек номер - %s</n>
				Ссылка для отслеживания - %s</n>',
					$orderFromVk['delivery']['track_number'],
					$orderFromVk['delivery']['track_link']
				);

				$wc_order->add_order_note( $delivery_info, 0, false );
			}

			$wc_order->save();
			$this->model->orders()->edit( $changes );

			return $wc_order->get_id();
		}

		/**
		 * @param $order_id_wc
		 * @param $new_status_wc
		 *
		 * @return bool|mixed
		 *
		 * @throws VKApiException
		 * @throws VKClientException
		 */
		public function UpdateStatus_VK( $order_id_wc, $new_status_wc ) {
			$order_data = $this->model->orders()->get( $order_id_wc, $this->model->prefix . 'id' );

			if ( ! $order_data ) {
				return false;
			}

			$status_settings = static::get_integration_options( self::ORDER_STATUSES );

			if ( in_array( $new_status_wc, $status_settings ) ) {
				$new_status_vk = array_search( $new_status_wc, $status_settings );

				$response = $this->apiClient->methods()->market_editOrder(
					array(
						'user_id'  => (int) $order_data['vk_user_id'],
						'order_id' => (int) $order_data['vk_id'],
						'status'   => $new_status_vk
					)
				);

				if ( $response ) {
					$this->model->orders()->edit(
						array(
							'vk_id'     => $order_data['vk_id'],
							'vk_status' => $new_status_vk
						)
					);

					return $order_id_wc;
				}
			}

			return false;
		}

		/**
		 * Get address
		 *
		 * @param $delivery
		 *
		 * @return array
		 *
		 * @throws VKApiException
		 * @throws VKClientException
		 */
		private function getAddress( $delivery ) {
			$data = array();

			switch ( $delivery['type'] ) {
				case 'Самовывоз':

					break;
				case 'Доставка в пункт выдачи Boxberry':
					$county  = $this->apiClient->methods()->database_getCountriesById( [ 'country_ids' => $delivery['delivery_point']['address']['country_id'] ] );
					$city    = $this->apiClient->methods()->database_getCitiesById( [ 'city_ids' => $delivery['delivery_point']['address']['city_id'] ] );
					$address = explode( ',', $delivery['delivery_point']['address']['address'] );

					$data['country']  = $county[0]['title'];
					$data['city']     = $city[0]['title'];
					$data['region']   = isset( $city[0]['region'] ) ? $city[0]['region'] : '';
					$data['postcode'] = ctype_digit( trim( $address[0] ) ) ? trim( $address[0] ) : '';
					$data['address']  = trim( implode( ',', array_slice( $address, 2 ) ) );

					break;
				case 'Доставка в пункт выдачи СДЭК':
					$county = $this->apiClient->methods()->database_getCountriesById( [ 'country_ids' => $delivery['delivery_point']['address']['country_id'] ] );
					$city   = $this->apiClient->methods()->database_getCitiesById( [ 'city_ids' => $delivery['delivery_point']['address']['city_id'] ] );

					$data['country']  = $county[0]['title'];
					$data['city']     = $city[0]['title'];
					$data['region']   = isset( $city[0]['region'] ) ? $city[0]['region'] : '';
					$data['postcode'] = '';
					$data['address']  = trim( $delivery['delivery_point']['address']['address'] );

					break;
				case 'В ближайшее почтовое отделение':
					$address = explode( ',', $delivery['address'] );

					if ( ctype_digit( trim( $address[0] ) ) ) {
						$data['country']  = trim( $address[1] );
						$data['city']     = trim( $address[2] );
						$data['region']   = '';
						$data['postcode'] = ctype_digit( trim( $address[0] ) ) ? trim( $address[0] ) : '';
						$data['address']  = trim( implode( ',', array_slice( $address, 3 ) ) );
					} else {
						$data['country']  = trim( $address[0] );
						$data['city']     = trim( $address[1] );
						$data['region']   = '';
						$data['postcode'] = '';
						$data['address']  = trim( implode( ',', array_slice( $address, 2 ) ) );
					}

					break;
				case 'Курьерская доставка':
					$data['country']  = '';
					$data['city']     = '';
					$data['region']   = '';
					$data['postcode'] = '';
					$data['address']  = $delivery['address'];
			}

			return $data;
		}

		/**
		 * Get delivery code from wordpress
		 *
		 * @param string $orderDeliverType
		 *
		 * @return string
		 */
		private function getDeliveryCode_WC( $orderDeliverType = null ) {
			$delivery_settings = static::get_integration_options( self::TYPES_OF_DELIVERIES );

			if ( $orderDeliverType == null ) {

				return $delivery_settings['default'];
			}

			$vkDeliveryTypes = $this->references->VK_getDeliveryTypes();
			$key             = array_search( $orderDeliverType, $vkDeliveryTypes );

			if ( $key !== false && key_exists( $key, $delivery_settings ) ) {
				$deliveryCode = $delivery_settings[ $key ];
			} else {
				$deliveryCode = $delivery_settings['default'];
			}

			return $deliveryCode;
		}

		/**
		 * Get data customer
		 *
		 * @param array $orderFromVk
		 *
		 * @return array
		 */
		private function getDataCustomer( $orderFromVk ) {
			$fullName = explode( ' ', ( $orderFromVk['recipient']['name'] ) );

			switch ( count( $fullName ) ) {
				case 1:
					$firstname = $fullName[0];

					break;
				case 2:
					$firstname = $fullName[1];
					$lastname  = $fullName[0];

					break;
				case 3:
					$firstname = $fullName[1] . ' ' . $fullName[2];
					$lastname  = $fullName[0];

					break;
				default:
					$firstname = $orderFromVk['recipient']['name'];
			}

			$customer = array(
				'id'        => $orderFromVk['user_id'],
				'firstName' => $firstname,
				'lastName'  => isset( $lastname ) ? $lastname : '',
				'email'     => 'id' . $orderFromVk['user_id'] . '@vk.com',
				'phone'     => ! empty( $orderFromVk['recipient']['phone'] ) ? $orderFromVk['recipient']['phone'] : 80000000000
			);

			return $customer;
		}

		/**
		 * Get status id from wordpress
		 *
		 * @param $statusIdFromVk
		 * @param bool $default
		 *
		 * @return mixed|string
		 */
		private function getStatus( $statusIdFromVk, $default = false ) {
			$wc_status       = '';
			$status_settings = static::get_integration_options( static::ORDER_STATUSES );

			if ( key_exists( $statusIdFromVk, $status_settings ) ) {
				$wc_status = $status_settings[ $statusIdFromVk ];
			} elseif ( $default ) {
				$wc_status = $status_settings['default'];
			}

			return $wc_status;
		}

		/**
		 * @param $orderFromVk
		 *
		 * @return mixed
		 *
		 * @throws VKApiException
		 * @throws VKClientException
		 */
		private function getProductsOrder( $orderFromVk ) {
			if ( count( $orderFromVk ) == 0 ) {
				return array();
			}

			if ( count( $orderFromVk['preview_order_items'] ) < 5 ) {
				$product_data = $orderFromVk['preview_order_items'];
			} else {
				$responseGetItemsOrder = $this->apiClient->methods()->market_getOrderItems(
					array(
						'user_id'  => $orderFromVk['user_id'],
						'order_id' => $orderFromVk['id'],
						'count'    => 50
						// 'offset' =>
					)
				);

				$product_data = isset( $responseGetItemsOrder['items'] )
					? $responseGetItemsOrder['items']
					: $orderFromVk['preview_order_items'];
			}

			return $product_data;
		}

		/**
		 * @param $price_details
		 *
		 * @return bool|float|int
		 */
		private function getShippingPrice( $price_details ) {
			foreach ( $price_details as $price_detail ) {
				if ( $price_detail['title'] == 'Стоимость доставки' ) {

					return $price_detail['price']['amount'] / 100;
				}
			}

			return false;
		}

		/**
		 * @param $orderFromVk
		 * @param $dataOrderFromBase
		 *
		 * @return bool
		 */
		private function check_track_number( $orderFromVk, $dataOrderFromBase ) {
			$last_event = json_decode( $dataOrderFromBase['json_last_event'], true );

			if ( ! isset( $last_event['delivery']['track_number'] )
			     && ! isset( $last_event['delivery']['track_link'] )
			     && isset( $orderFromVk['delivery']['track_number'] )
			     && isset( $orderFromVk['delivery']['track_link'] )
			) {

				return true;
			}

			return false;
		}
	}

endif;