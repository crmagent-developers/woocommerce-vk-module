<?php

if ( ! class_exists( 'WC_VKontakte_Export' ) ) :

	/**
	 * Class WC_VKontakte_Export
	 */
	class WC_VKontakte_Export extends WC_VKontakte_Base {

		/**
		 * CATEGORIES
		 */

		/**
		 * Album creation
		 *
		 * @param $category_list
		 *
		 * @return array
		 *
		 * @throws VKApiException
		 * @throws VKClientException
		 */
		public function createAlbums( $category_list ) {
			$categoriesForExport = $this->getCategoryChecked( $category_list );

			foreach ( $categoriesForExport as &$categoryForExport ) {
				if ( isset( $categoryForExport['album_id'] ) ) {

					continue;
				}

				$data = [
					'owner_id' => static::$options_oauth['-id_group'],
					'title'    => htmlspecialchars_decode( $categoryForExport['name'] ),
					'photo_id' => ! empty( $categoryForExport['pathImage'] ) ? $this->getImageId( $categoryForExport['pathImage'], $categoryForExport['category_id'], 'album' ) : null
//                'main_album' => ''
				];

				$album                         = $this->apiClient->methods()->market_addAlbum( $data );
				$categoryForExport['album_id'] = (int) $album['market_album_id'];

				$this->putTableAlbums( $categoryForExport, $album );
			}

			unset( $categoryForExport );

			return $categoriesForExport;
		}

		/**
		 * Get categories selected for export
		 *
		 * @param $category_list
		 *
		 * @return array
		 *
		 * @throws VKApiException
		 * @throws VKClientException
		 */
		private function getCategoryChecked( $category_list ) {
			$allCategories = $this->references->WC_getCategories( array(
					'custom_fields' => array(
						'term_id',
						'slug',
						'parent',
						'name',
						'picture-path'
					),
					'custom_sort'   => 'key-term_id'
				)
			);

			$categoriesChecked = array();

			if ( ! empty( $category_list ) ) {
				foreach ( $category_list as $category_id ) {

					if ( key_exists( $category_id, $allCategories ) ) {
						$categoriesChecked[ $allCategories[ $category_id ]['term_id'] ] = array(
							'category_id' => (int) $allCategories[ $category_id ]['term_id'],
							'slug'        => $allCategories[ $category_id ]['slug'],
							'parent_id'   => (int) $allCategories[ $category_id ]['parent'],
							'name'        => $allCategories[ $category_id ]['name'],
							'pathImage'   => $allCategories[ $category_id ]['picture-path']
						);
					} else {
						#todo catch
					}
				}
			}

			return $this->checkCategories( $categoriesChecked );
		}

		/**
		 * Checking for the existence of an album
		 *
		 * @param $categoriesChecked
		 *
		 * @return array
		 * @throws VKClientException
		 *
		 * @throws VKApiException
		 */
		private function checkCategories( $categoriesChecked ) {
			$categoriesFromBase = $this->checkAlbumFromVk();

			foreach ( $categoriesFromBase as $categoryFromBase ) {
				if ( ! key_exists( $categoryFromBase[ $this->model->prefix . 'id' ], $categoriesChecked ) ) {
					$this->deleteAlbum( $categoryFromBase );
				} else {
					$categoriesChecked[ $categoryFromBase[ $this->model->prefix . 'id' ] ]['album_id'] = $categoryFromBase['vk_id'];
				}
			}

			return $categoriesChecked;
		}

		/**
		 * Delete album from database and vk
		 *
		 * @param $category
		 *
		 * @throws VKApiException
		 * @throws VKClientException
		 */
		private function deleteAlbum( $category ) {
			$this->apiClient->methods()->market_deleteAlbum( [
				'owner_id' => static::$options_oauth['-id_group'],
				'album_id' => (int) $category['vk_id']
			] );

			$this->model->albums()->delete( $category[ $this->model->prefix . 'id' ], $this->model->prefix . 'id', $category[ $this->model->prefix . 'parent_id' ] );
			$this->model->images()->delete( $category[ $this->model->prefix . 'id' ], 'album' );
		}

		/**
		 * Checking the relevance of the database
		 *
		 * @return array
		 * @throws VKApiException
		 * @throws VKClientException
		 */
		private function checkAlbumFromVk() {
			$albumsFromVk       = $this->getAlbums();
			$categoriesFromBase = $this->model->albums()->getAll();

			if ( ! empty( $categoriesFromBase ) ) {
				foreach ( $categoriesFromBase as $key => $categoryFromBase ) {
					if ( ! key_exists( $categoryFromBase['vk_id'], $albumsFromVk ) ) {
						$this->model->albums()->delete( $categoryFromBase[ $this->model->prefix . 'id' ], $this->model->prefix . 'id' );

						unset( $categoriesFromBase[ $key ] );
					}
				}
			} elseif ( ! empty( $albumsFromVk ) ) {
				$categoriesFromBase = array();

				$allCategories = $this->references->WC_getCategories( array(
					'custom_fields' => array(
						'term_id',
						'parent',
						'name',
						'picture-path'
					)
				) );

				foreach ( $albumsFromVk as $vk_id => $name ) {
					$wp_category = $this->filterNameCategories( $name, $allCategories );

					if ( $wp_category ) {
						$this->putTableAlbums(
							array(
								'category_id' => $wp_category['term_id'],
								'parent_id'   => $wp_category['parent'],
								'name'        => $wp_category['name']
							),
							array(
								'market_album_id' => $vk_id
							)
						);

						$categoriesFromBase[] = array(
							$this->model->prefix . 'id'        => $wp_category['term_id'],
							$this->model->prefix . 'parent_id' => $wp_category['parent'],
							$this->model->prefix . 'name'      => $wp_category['name'],
							'vk_id'                            => $vk_id,
						);
					}
				}
			}

			return $categoriesFromBase;
		}

		/**
		 * Get all albums from vk
		 *
		 * @return array
		 * @throws VKClientException
		 *
		 * @throws VKApiException
		 */
		private function getAlbums() {
			$albums = array();

			$response = $this->apiClient->methods()->market_getAlbums(
				array(
					'owner_id' => static::$options_oauth['-id_group'],
					'count'    => 100,
				)
			);

			foreach ( $response['items'] as $item ) {
				$albums[ $item['id'] ] = $item['title'];
			}

			return $albums;
		}

		/**
		 * @param $name
		 * @param $allCategories
		 *
		 * @return bool|mixed
		 */
		private function filterNameCategories( $name, $allCategories ) {
			foreach ( $allCategories as $category ) {
				if ( $category['name'] == $name ) {

					return $category;
				}
			}

			return false;
		}

		/**
		 * Writing album to the database
		 *
		 * @param $category
		 * @param $album
		 */
		private function putTableAlbums( $category, $album ) {
			if ( isset( $album ) ) {
				$this->model->albums()->insert( array(
					$this->model->prefix . 'id'        => $category['category_id'],
					$this->model->prefix . 'parent_id' => $category['parent_id'],
					$this->model->prefix . 'name'      => $category['name'],
					'vk_id'                            => (int) $album['market_album_id']
				) );
			}
		}

		/**
		 * PRODUCTS
		 */

		/**
		 * Adding products to a new category or updating products in an old category
		 *
		 * @param $inputArray
		 * @param string $flag
		 *
		 * @throws VKApiException
		 * @throws VKClientException
		 */
		public function addProducts( $inputArray, $flag = 'categories' ) {
			if ( $flag == 'categories' ) {
				$productsForExport = $this->getAllProducts( $inputArray );
				$productsForExport = $this->checkProducts( $productsForExport );
			} else {
				$productsForExport = $inputArray;
			}

			$createdGoods = 0;

			foreach ( $productsForExport as $key => $product ) {
				$id_category_vk = $this->get_id_category_vk( $product['category_ids'] );

				foreach ( $product['offers'] as $offer ) {

					$offerId = $offer['productId'] != $offer['offerId'] ? $offer['offerId'] : 0;

					if ( $offer['stock_status'] != 'instock' ) {
						if ( ! empty( $product['offers_vk'] ) && key_exists( $offerId, $product['offers_vk'] ) ) {
							$this->deleteProduct( $product['offers_vk'][ $offerId ]['vk_id'], $product['productId'], $offerId );

							continue;
						} else {

							continue;
						}
					}

					$description = '';

					if ( $offerId != 0 ) {
						$description .= 'Option SKU: ' . $offerId . PHP_EOL . PHP_EOL;

						foreach ( $offer['params'] as $param ) {
							$description .= $param['name'] . ': ' . $param['value'] . PHP_EOL;
						}

						$description .= '________________________________________' . PHP_EOL;
					}

					if ( ! empty( $offer['description'] ) && strlen( $offer['description'] ) < 3000 ) {
						$description .= html_entity_decode( strip_tags( html_entity_decode( $offer['description'] ) ) );
					} elseif ( ! empty( $offer['short_description'] ) ) {
						$description .= html_entity_decode( strip_tags( html_entity_decode( $offer['short_description'] ) ) );
					}

					$data = array(
						'owner_id'         => static::$options_oauth['-id_group'],
						'name'             => html_entity_decode( $offer['offerName'] ),
						'description'      => ! empty( $description ) ? $description : 'Описание товара скоро появится.',
						'category_id'      => $id_category_vk,
						'price'            => $offer['price'],
						'url'              => $offer['url'],
						'dimension_length' => $offer['dimension_length'],
						'dimension_width'  => $offer['dimension_width'],
						'dimension_height' => $offer['dimension_height'],
						'weight'           => $offer['weight'],
						'sku'              => $offer['sku']
					);

					if ( $offer['oldPrice'] > $offer['price'] ) {
						$data['old_price'] = $offer['oldPrice'];
					}

					if ( ! empty( $offer['picture'] ) ) {
						$image_id = (int) $this->getImageId( $offer['picture'], $offer['productId'] . '*' . $offerId, 'product_main_photo_id' );

						if ( $image_id != false ) {
							$data['main_photo_id'] = $image_id;
						} else {
							$data['main_photo_id'] = (int) $this->getImageId( WP_PLUGIN_DIR . '/woo-vkontakte/files/no-photo.png', $offer['productId'] . '*' . $offerId, 'product_main_photo_id' );
						}
					}

					//Если такого варианта нет в вк, то создаем
					if ( empty( $product['offers_vk'] ) || ! key_exists( $offerId, $product['offers_vk'] ) ) {
						$result        = $this->apiClient->methods()->market_add( $data );
						$product_vk_id = isset( $result['market_item_id'] ) ? $result['market_item_id'] : false;

						if ( $product_vk_id != false ) {
							$this->model->products()->insert(
								array(
									$this->model->prefix . 'id' => $offer['productId'],
									'vk_id'                     => $product_vk_id,
									'categories_albums'         => json_encode( $product['category_ids'] ),
									'offer'                     => $offerId
								)
							);
						}

						$createdGoods ++;
						//Если есть, то обновляем
					} else {
						$data['item_id'] = $product['offers_vk'][ $offerId ]['vk_id'];
						$this->apiClient->methods()->market_edit( $data );

						$this->model->products()->edit(
							array(
								'vk_id'             => $product['offers_vk'][ $offerId ]['vk_id'],
								'categories_albums' => json_encode( $product['category_ids'] )
							)
						);
					}

					if ( ! empty( $product_vk_id ) ) {
						$this->apiClient->methods()->market_addToAlbum(
							array(
								'owner_id'  => static::$options_oauth['-id_group'],
								'item_id'   => $product_vk_id,
								'album_ids' => implode( ',', $product['category_ids'] )
							)
						);
					}

					if ( $createdGoods == 6999 ) {

						break;
					}
				}

				if ( $createdGoods == 6999 ) {

					break;
				}

				unset( $productsForExport[ $key ] );
			}

			file_put_contents( WP_PLUGIN_DIR . '/woo-vkontakte/files/products_for_export.json', json_encode( $productsForExport ) );
		}

		/**
		 * Get all products for export
		 *
		 * @param $categories
		 *
		 * @return array
		 */
		private function getAllProducts( $categories ) {
			$allProducts = array();

			foreach ( $categories as $category ) {
				$products = $this->get_wc_products_taxonomies( static::$options['item_status_export'], $category['slug'] );

				foreach ( $products as $offer ) {
					if ( ! key_exists( $offer['productId'], $allProducts ) ) {
						$allProducts[ $offer['productId'] ] = array(
							'category_ids' => array(
								(int) $category['category_id'] => (int) $category['album_id']
							),
							'offers'       => array(
								$offer['offerId'] => $offer
							)
						);
					} else {
						$allProducts[ $offer['productId'] ]['category_ids'][ (int) $category['category_id'] ] = (int) $category['album_id'];
						$allProducts[ $offer['productId'] ]['offers'][ $offer['offerId'] ]                    = $offer;
					}
				}
			}

			return $allProducts;
		}

		/**
		 * Get WC products from category
		 *
		 * @param $status_args
		 * @param $category_slug
		 *
		 * @return array
		 */
		private function get_wc_products_taxonomies( $status_args, $category_slug ) {
			if ( ! $status_args ) {
				$status_args = array( 'publish' );
			}

			$attribute_taxonomies = wc_get_attribute_taxonomies();
			$product_attributes   = array();

			foreach ( $attribute_taxonomies as $product_attribute ) {
				$attribute_id                        = wc_attribute_taxonomy_name_by_id( intval( $product_attribute->attribute_id ) );
				$product_attributes[ $attribute_id ] = $product_attribute->attribute_label;
			}

			$full_product_list = array();

			$products = wc_get_products(
				array(
					'limit'    => - 1,
					'status'   => $status_args,
					'category' => $category_slug
				)
			);

			foreach ( $products as $offer ) {
				if ( $offer->get_type() == 'simple' ) {
					$this->setOffer( $full_product_list, $product_attributes, $offer );
				} elseif ( $offer->get_type() == 'variable' ) {
					foreach ( $offer->get_children() as $child_id ) {
						$child_product = wc_get_product( $child_id );
						if ( ! $child_product ) {
							continue;
						}

						$this->setOffer( $full_product_list, $product_attributes, $child_product, $offer );
					}
				}
			}

			return $full_product_list;
		}

		/**
		 * Set offer
		 *
		 * @param array $full_product_list
		 * @param array $product_attributes
		 * @param WC_Product $product
		 * @param bool | WC_Product_Variable $parent
		 *
		 * @return void
		 */
		private function setOffer( &$full_product_list, $product_attributes, $product, $parent = false ) {
			if ( $parent ) {
				$image = wp_get_original_image_path( $product->get_image_id()/*, 'full'*/ );

				if ( ! $image ) {
					$image = wp_get_original_image_path( $parent->get_image_id()/*, 'full'*/ );
				}

				$attributes = get_post_meta( $parent->get_id(), '_product_attributes' );
			} else {
				$image      = wp_get_original_image_path( $product->get_image_id()/*, 'full'*/ );
				$attributes = get_post_meta( $product->get_id(), '_product_attributes' );
			}

			$attributes = ( isset( $attributes[0] ) ) ? $attributes[0] : $attributes;

			$params = array();

			if ( ! empty( $attributes ) ) {
				foreach ( $attributes as $attribute_name => $attribute ) {
					$attributeValue = $product->get_attribute( $attribute_name );
					if ( $attribute['is_visible'] == 1 && ! empty( $attributeValue ) ) {
						$params[] = array(
							'code'  => $attribute_name,
							'name'  => isset( $product_attributes[ $attribute_name ] ) ? $product_attributes[ $attribute_name ] : $attribute['name'],
							'value' => $attributeValue
						);
					}
				}
			}

			$product_data = array(
				'offerId'           => $product->get_id(),
				'productId'         => ( $product->get_parent_id() > 0 ) ? $parent->get_id() : $product->get_id(),
				'sku'               => $product->get_sku(),
				'offerName'         => $product->get_name(),
				'productName'       => ( $product->get_parent_id() > 0 ) ? $parent->get_title() : $product->get_title(),
				'price'             => $product->get_price(),
				'oldPrice'          => $product->get_regular_price(),
				'picture'           => $image,
				'short_description' => $product->get_short_description(),
				'description'       => $product->get_description(),
				'url'               => ( $product->get_parent_id() > 0 ) ? $parent->get_permalink() : $product->get_permalink(),
				'quantity'          => is_null( $product->get_stock_quantity() ) ? 0 : $product->get_stock_quantity(),
				'stock_status'      => $product->get_stock_status(),
				'dimension_length'  => $product->get_length() != '' ? wc_get_dimension( $product->get_length(), 'mm' ) : null,
				'dimension_width'   => $product->get_width() != '' ? wc_get_dimension( $product->get_width(), 'mm' ) : null,
				'dimension_height'  => $product->get_height() != '' ? wc_get_dimension( $product->get_height(), 'mm' ) : null,
				'weight'            => $product->get_weight() != '' ? wc_get_weight( $product->get_weight(), 'g' ) : null,
				'params'            => array()
			);

			if ( ! empty( $params ) ) {
				$product_data['params'] = $params;
			}

			if ( isset( $product_data ) ) {
				$full_product_list[] = $product_data;
			}

			unset( $product_data );
		}

		/**
		 * @param $categories
		 *
		 * @return mixed
		 */
		private function get_id_category_vk( $categories ) {
			$options_category_conformity = static::get_integration_options( self::CATEGORY_CONFORMITY );
			$wp_ids                      = array_keys( $categories );

			return $options_category_conformity[ array_shift( $wp_ids ) ];
		}

		/**
		 * Checking for the existence of an product
		 *
		 * @param $productsForExport
		 *
		 * @return mixed
		 *
		 * @throws VKApiException
		 * @throws VKClientException
		 */
		private function checkProducts( $productsForExport ) {
			$productsFromBase = $this->actualBase();

			foreach ( $productsFromBase as $wp_product_id => $productFromBase ) {
				if ( ! key_exists( $wp_product_id, $productsForExport ) ) {
					$this->deleteProducts( $productFromBase['offers'], $wp_product_id );
				} else {
					$productsForExport[ $wp_product_id ]['offers_vk'] = $productFromBase['offers'];
				}
			}

			unset( $productFromBase );

			return $productsForExport;
		}

		/**
		 * Checking the relevance of the database
		 *
		 * @return array
		 *
		 * @throws VKApiException
		 * @throws VKClientException
		 */
		private function actualBase() {
			$productsFromBase = $this->model->products()->getAll();
			$productsFromVk   = $this->getProductsFromVk();

			if ( ! empty( $productsFromBase ) && is_array( $productsFromBase ) ) {
				foreach ( $productsFromBase as $wp_product_id => &$product ) {

					foreach ( $product['offers'] as $offerId => $data ) {
						if ( ! key_exists( $data['vk_id'], $productsFromVk ) ) {
							$this->model->products()->delete( $data['vk_id'], 'vk_id' );
							$this->model->images()->delete( $wp_product_id . '*' . $offerId, 'product' );

							unset( $product['offers'][ $offerId ] );
						}
					}

					if ( empty( $product['offers'] ) ) {

						unset( $productsFromBase[ $wp_product_id ] );
					}
				}

				unset( $product );
			}

			return $productsFromBase;
		}

		/**
		 * Get products from vk album
		 *
		 * @return array
		 *
		 * @throws VKApiException
		 * @throws VKClientException
		 */
		private function getProductsFromVk() {
			$productsFromVk = array();

			$offset = 0;

			do {
				$productsIteration = $this->apiClient->methods()->market_get( [
					'owner_id' => static::$options_oauth['-id_group'],
					'count'    => 200,
					'offset'   => $offset
				] );

				if ( count( $productsIteration['items'] ) > 0 ) {
					foreach ( $productsIteration['items'] as $item ) {
						$productsFromVk[ $item['id'] ] = array(
							'name'  => $item['title'],
							'offer' => $this->getOfferId( $item['description'] )
						);
					}
				}

				$offset = $offset + 200;
			} while ( count( $productsIteration['items'] ) > 0 );

			return $productsFromVk;
		}

		/**
		 * Get offer id
		 *
		 * @param $description
		 *
		 * @return string
		 */
		public function getOfferId( $description ) {
			if ( is_string( $description ) && stripos( $description, '________________________________________' ) ) {
				$description       = explode( '________________________________________', $description );
				$descriptionOption = explode( PHP_EOL, $description[0] );

				return str_replace( 'Option SKU: ', '', $descriptionOption[0] );
			} else {

				return 0;
			}
		}

		/**
		 * Delete offers from base and vk
		 *
		 * @param $offers
		 * @param $wp_id
		 *
		 * @throws VKApiException
		 * @throws VKClientException
		 */
		private function deleteProducts( $offers, $wp_id ) {
			foreach ( $offers as $offer ) {
				$this->apiClient->methods()->market_delete( [
					'owner_id' => static::$options_oauth['-id_group'],
					'item_id'  => (int) $offer['vk_id']
				] );

				$this->model->images()->delete( $wp_id . '*' . $offer['offer'], 'product' );
			}

			$this->model->products()->delete( $wp_id, $this->model->prefix . 'id' );
		}

		/**
		 * Delete offer from base and vk
		 *
		 * @param $vk_id
		 * @param $wp_id
		 * @param $offerId
		 *
		 * @throws VKApiException
		 * @throws VKClientException
		 */
		private function deleteProduct( $vk_id, $wp_id, $offerId ) {
			$this->apiClient->methods()->market_delete( [
				'owner_id' => static::$options_oauth['-id_group'],
				'item_id'  => (int) $vk_id
			] );

			$this->model->images()->delete( $wp_id . '*' . $offerId, 'product' );
			$this->model->products()->delete( $wp_id, $this->model->prefix . 'id' );
		}

		/**
		 * IMAGES
		 */

		/**
		 * Get image id
		 *
		 * @param string $pathImage
		 * @param int $source_id
		 * @param string $flag
		 *
		 * @return string|null
		 * @throws VKClientException
		 *
		 * @throws VKApiException
		 */
		private function getImageId( $pathImage, $source_id, $flag ) {
			$id = $this->checkImage( $pathImage, $source_id, $flag );

			if ( isset( $id ) ) {

				return $id;
			}

			$upload_url = $this->getUploadUrl( $flag );
			$request    = $this->loadPhoto( $upload_url, $pathImage );
			$photo      = $this->saveImage( $request, $flag );

			if ( $photo != false ) {
				$id = $this->getImageIdFromResult( $photo );

				$this->model->images()->insert( array(
					'type'                             => $flag == 'album' ? 'album' : 'product',
					$this->model->prefix . 'source_id' => $source_id,
					$this->model->prefix . 'path'      => $pathImage,
					'vk_id'                            => $id
				) );
			}

			return isset( $id ) ? (int) $id : false;
		}

		/**
		 * Check if the picture has been loaded earlier
		 *
		 * @param $pathImage
		 * @param $source_id
		 * @param $type
		 *
		 * @return bool|string
		 */
		private function checkImage( $pathImage, $source_id, $type ) {
			$type  = $type == 'album' ? $type : 'product';
			$image = $this->model->images()->get( $pathImage, $source_id, $type );

			return ! empty( $image ) ? $image[0]['vk_id'] : null;
		}

		/**
		 * Get upload url for load image
		 *
		 * @param $flag
		 *
		 * @return bool|mixed
		 *
		 * @throws VKApiException
		 * @throws VKClientException
		 */
		private function getUploadUrl( $flag ) {
			$upload_url = false;

			switch ( $flag ) {
				case 'album':
					$upload_url = $this->apiClient->methods()->photos_getMarketAlbumUploadServer( [
						'group_id' => (int) static::$options_oauth['id_group']
					] );

					break;
				case 'product_main_photo_id':
					$upload_url = $this->apiClient->methods()->photos_getMarketUploadServer( [
						'group_id'   => (int) static::$options_oauth['id_group'],
						'main_photo' => 1
					] );

					break;
				case 'product_photo_ids':
					$upload_url = $this->apiClient->methods()->photos_getMarketUploadServer( [
						'group_id' => (int) static::$options_oauth['id_group']
					] );

					break;
			}

			return $upload_url;
		}

		/**
		 * Load photo
		 *
		 * @param $upload_url
		 * @param $pathImage
		 *
		 * @return array|bool|mixed|null
		 *
		 * @throws VKApiException
		 * @throws VKClientException
		 */
		private function loadPhoto( $upload_url, $pathImage ) {
			if ( $upload_url != false ) {

				return $this->apiClient->getRequest()->upload(
					$upload_url['upload_url'],
					'photo',
					$pathImage );
			} else {

				return false;
			}
		}

		/**
		 * Save photo for album
		 *
		 * @param $request
		 * @param $flag
		 *
		 * @return bool|mixed
		 *
		 * @throws VKApiException
		 * @throws VKClientException
		 */
		private function saveImage( $request, $flag ) {
			if ( isset( $request['error'] ) || $request == false ) {
				return false;
			}

			$response = false;

			switch ( $flag ) {
				case 'album':
					$response = $this->apiClient->methods()->photos_saveMarketAlbumPhoto( array(
						'group_id' => (int) static::$options_oauth['id_group'],
						'photo'    => $request['photo'],
						'server'   => $request['server'],
						'hash'     => $request['hash']
					) );

					break;
				case 'product_photo_ids' || 'product_main_photo_id':
					$response = $this->apiClient->methods()->photos_saveMarketPhoto( array(
						'group_id'  => (int) static::$options_oauth['id_group'],
						'photo'     => $request['photo'],
						'server'    => $request['server'],
						'hash'      => $request['hash'],
						'crop_data' => $request['crop_data'],
						'crop_hash' => $request['crop_hash']
					) );

					break;
			}

			return $response;
		}

		/**
		 * Get image id from result
		 *
		 * @param $photo
		 *
		 * @return int|null
		 */
		private function getImageIdFromResult( $photo ) {
			if ( is_array( $photo ) && count( $photo ) == 1 ) {
				$id = $photo[0]['id'];
			} elseif ( is_array( $photo ) && count( $photo ) > 1 ) {
				$id = '';

				foreach ( $photo as $item ) {
					$id .= $item['id'] . ',';
				}

				$id = rtrim( $id, ',' );
			}

			return isset( $id ) ? $id : null;
		}
	}

endif;
