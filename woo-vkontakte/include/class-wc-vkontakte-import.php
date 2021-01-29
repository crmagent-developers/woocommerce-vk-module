<?php


if ( ! class_exists( 'WC_VKontakte_Import' ) ) :


	/**
	 * Class WC_VKontakte_Export
	 */
	class WC_VKontakte_Import extends WC_VKontakte_Base {

		/**
		 * @throws VKApiException
		 * @throws VKClientException
		 */
		public function import() {
			$offset = 0;

			do {
				$productsIteration = $this->apiClient->methods()->market_get( [
					'owner_id' => static::$options_oauth['-id_group'],
					'count'    => 200,
					'offset'   => $offset
				] );

				if ( count( $productsIteration['items'] ) > 0 ) {
					foreach ( $productsIteration['items'] as $item ) {
						try {
							$this->addProduct( $item );
						} catch ( Exception $e ) {
							$this->logger->write( sprintf(
								'Import < addProduct() > - [%s] %s',
								$item['id'],
								$e->getMessage()
							) );
						}
					}
				}

				$offset += 200;
			} while ( count( $productsIteration['items'] ) > 0 );
		}

		/**
		 * @param $item
		 *
		 * @throws WC_Data_Exception
		 */
		private function addProduct( $item ) {
			$wc_product_id = $this->model->products()->get( $item['id'], 'vk_id', $this->model->prefix . 'id' );

			if ( $wc_product_id ) {
				return;
			}

			$wc_product = new WC_Product();

			$wc_product->set_date_created( date( 'Y-m-d H:i:s' ) );
			$wc_product->set_status( static::$options['item_status_import'] );
			$wc_product->set_name( $item['title'] );
			$wc_product->set_description( $item['description'] );
			$wc_product->set_regular_price( $item['price']['amount'] / 100 );

			if ( isset( $item['price']['old_amount'] ) && $item['price']['old_amount'] != 0 ) {
				$wc_product->set_regular_price( $item['price']['old_amount'] / 100 );
				$wc_product->set_sale_price( $item['price']['amount'] / 100 );
			}

			$wc_product->set_sku( $item['sku'] );
			$wc_product->set_category_ids( array( 0 ) );

			$image_id = media_sideload_image( $item['thumb_photo'], 0, null, 'id' );

			if ( $image_id instanceof WP_Error ) {
				$image_id = 0;
			}

			$wc_product->set_image_id( $image_id );
			$wc_product_id = $wc_product->save();

			$this->model->products()->insert(
				array(
					$this->model->prefix . 'id' => $wc_product_id,
					'vk_id'                     => $item['id'],
					'categories_albums'         => 0,
					'offer'                     => 0,
				)
			);
		}
	}

endif;