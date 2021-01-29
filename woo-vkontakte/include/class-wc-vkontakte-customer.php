<?php

if ( ! class_exists( 'WC_VK_Customer' ) ) :


	class WC_VK_Customer {

		private $model;

		public function __construct( $model ) {
			$this->model = $model;
		}

		/**
		 * Create customer in WC
		 *
		 * @param $customer
		 * @param $address
		 *
		 * @return int
		 */
		public function createCustomer( $customer, $address ) {
			$new_customer = new WC_Customer();

			$new_customer->set_date_created( date( 'Y-m-d H:i:s' ) );
			$new_customer->set_password( 'tmppass' );
			$new_customer->set_first_name( $customer['firstName'] );
			$new_customer->set_last_name( $customer['lastName'] );
			$new_customer->set_email( $customer['email'] );
			$new_customer->set_billing_phone( $customer['phone'] );
			$new_customer->set_shipping_country( $address['country'] );
			$new_customer->set_shipping_city( $address['city'] );
			$new_customer->set_shipping_state( $address['region'] );
			$new_customer->set_shipping_postcode( $address['postcode'] );
			$new_customer->set_shipping_address( $address['address'] );

			$new_customer_id = $new_customer->save();

			if ( $new_customer_id ) {
				$this->model->customers()->insert( array(
					$this->model->prefix . 'id' => $new_customer_id,
					'vk_id'                     => $customer['id']
				) );
			}

			return $new_customer_id;
		}
	}

endif;