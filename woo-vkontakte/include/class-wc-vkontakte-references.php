<?php

if ( ! class_exists( 'WC_VK_References' ) ) :

	class WC_VK_References {
		const DELIVERY_TYPES_VK = array(
			0 => 'Самовывоз',
			1 => 'Доставка в пункт выдачи Boxberry',
			2 => 'Доставка в пункт выдачи СДЭК',
			3 => 'В ближайшее почтовое отделение',
			4 => 'Курьерская доставка'
		);

		const STATUSES_VK = array(
			0 => 'Новый',
			1 => 'Согласуется',
			2 => 'Собирается',
			3 => 'Доставляется',
			4 => 'Выполнен',
			5 => 'Отменен',
			6 => 'Возвращен'
		);

		const PAYMENT_TYPES = array();

		const LENGTH_FACTOR = array(
			'mm' => 1,
			'cm' => 10,
			'm'  => 1000
		);

		const WEIGHT_FACTOR = array(
			'g'  => 1,
			'kg' => 1000
		);

		/**
		 * @var object
		 */
		protected $apiClient;

		public function __construct( $apiClient ) {
			$this->apiClient = $apiClient;
		}

		/**
		 * ORDER STATUSES
		 */

		/**
		 * Array order statuses from vk
		 *
		 * @return array
		 */
		public function VK_getOrderStatuses() {
			return self::STATUSES_VK;
		}

		/**
		 * Array order statuses from wc
		 *
		 * @return array
		 */
		public function WC_getOrderStatuses() {
			$statuses = array();

			foreach ( wc_get_order_statuses() as $code => $name ) {
				$statuses[ str_replace( 'wc-', '', $code ) ] = $name;
			}

			return $statuses;
		}

		/**
		 * DELIVERY
		 */

		/**
		 * Get delivery types from vk
		 *
		 * @return array
		 */
		public function VK_getDeliveryTypes() {
			return self::DELIVERY_TYPES_VK;
		}

		/**
		 * Get delivery types from wc
		 *
		 * @return array
		 */
		public function WC_getDeliveryTypes() {
			$wc_shipping_list = array();

			foreach ( VK_get_wc_shipping_methods() as $shipping_code => $shipping ) {
				if ( isset( $shipping['enabled'] ) && $shipping['enabled'] == WC_VKontakte_Abstracts_Settings::YES ) {
					$wc_shipping_list[ $shipping_code ] = __( $shipping['title'], 'woocommerce' );
				}
			}

			return $wc_shipping_list;
		}

		/**
		 * PAYMENTS
		 */

		/**
		 * Get payment types from wc
		 *
		 * @return array
		 */
		public function WC_getPaymantTypes() {
			$wc_payment      = WC_Payment_Gateways::instance();
			$wc_payment_list = array();

			foreach ( $wc_payment->payment_gateways() as $payment ) {
				$wc_payment_list[ $payment->id ] = __( $payment->method_title, 'woocommerce' );
			}

			return $wc_payment_list;
		}

		/**
		 * CATEGORIES
		 */

		/**
		 * Getting a list all categories
		 *
		 * @param array $wc_args
		 * @param array $vk_args
		 *
		 * @return array
		 */
		public function getCategories( $wc_args = array(), $vk_args = array() ) {
			return array(
				'woocommerce' => $this->WC_getCategories( $wc_args ),
				'vkontakte'   => $this->VK_getCategories( $vk_args )
			);
		}

		/**
		 * VK categories
		 */

		/**
		 * Get VK categories
		 *
		 * @param array $args
		 *
		 * @return array
		 */
		public function VK_getCategories( $args = array() ) {
			$categories = $this->apiClient->methods()->market_getCategories( [ 'count' => 1000 ] );

			$custom_sort = isset( $args['custom_sort'] ) ? $args['custom_sort'] : 'default';

			return $this->VK_customSort( $categories, $custom_sort );
		}

		/**
		 * @param $categories
		 * @param $type
		 *
		 * @return array
		 */
		private function VK_customSort( $categories, $type ) {
			switch ( $type ) {
				case 'sections':
					$result = $this->VK_sortSections( $categories );

					break;
				default:
					$result = $categories;
			}

			return $result;
		}

		/**
		 * @param $categories
		 *
		 * @return array
		 */
		private function VK_sortSections( $categories ) {
			$result = array();

			foreach ( $categories['items'] as $item ) {
				$result[ $item['section']['id'] ]['name'] = $item['section']['name'];
				$result[ $item['section']['id'] ]['id']   = $item['section']['id'];

				$result[ $item['section']['id'] ]['categories'][] = [
					'id'   => $item['id'],
					'name' => $item['name']
				];
			}

			return $result;
		}

		/**
		 * WC categories
		 */

		/**
		 * Get WC categories
		 *
		 * @param array $args
		 *
		 * @return array
		 */
		public function WC_getCategories( $args = array() ) {
			$taxonomy     = 'product_cat';  // Название таксономии, которую нужно обрабатывать. Добавлено с версии 3.0.
			$child_of     = '';             // Получить дочерние категории (включая все уровни вложенности), указанной категории. В параметре указывается ID родительской категории (категория, вложенные категории которой нужно показать).
			$parent       = '';             // Получает категории, родительская категория которых указана в этом параметре. Отличие от child_of в том, что будет показан один (только первый) уровень вложенности.
			$orderby      = 'parent';       // Сортировка полученных данных по определенным критериям. Например, по количеству постов в каждой категории или по названию категорий. (id или term_id/name/count/slug/description/term_group/parent/include/slug__in/meta_value/meta_value_num/ключ "meta_query"/none)
			$order        = 'ASC';          // Направление сортировки, указанной в параметре "orderby" (ASC/DESC)
			$hide_empty   = 0;              // Получать (true) или нет (false) пустые категории
			$hierarchical = 1;              // Если параметр установлен в true, то в результат будут включены пустые дочерние категории, дочерние категории которых имеют записи (непустые)
			$exclude      = '';             // Исключить какие-либо категории из списка. Нужно указывать ID категорий через запятую или в массиве. Если этот параметр указан, параметр child_of будет отменен.
			$exclude_tree = '';             // ID родительских терминов, которые нужно исключить. Исключена будет вся ветка.
			$include      = '';             // Вывести списком только указанные категории. Указывать нужно ID категорий через запятую или в массиве.
			$number       = '';             // Лимит. Число категорий, которые будут получены. По умолчанию без ограничений - будут получены все категории.
			$offset       = '';             // Верхний отступ в запросе. Сколько первых элементов пропустить. Указывать нужно число. По умолчанию без отступов.
			$show_count   = 0;              // 1 for yes, 0 for no
			$pad_counts   = 0;              // Если передать true, то число которое показывает количество записей в родительских категориях будет суммой своих записей и записей из дочерних категорий.
			$name_like    = '';             // Показать термины, в названии которых есть указанная строка. Поиск по строке.
			$name         = '';             // Укажите тут строку или массив строк, чтобы получить термины с указанными названиями.
			$slug         = '';             // Укажите тут строку или массив строк, чтобы получить термины с указанными ярлыками (слагами).
			$fields       = 'all';          /* Какие поля возвращать в результирующем массиве.

										    all - Вернуть массив объектов (все данные) - по умолчанию
										    ids - вернуть массив чисел
										    names - вернуть массив строк
										    count - (3.2+) возвращает количество найденных терминов
										    id=>parent - вернуть массив, где ключ = ID термина, а значение = ID родительского термина
										    id=>slug - вернуть массив, где ключ = ID термина, а значение = слаг (название для УРЛ) термина
										    id=>name - вернуть массив, где ключ = ID термина, а значение = название (имя) термина */

			$custom_fields = ! empty( $args['custom_fields'] ) // picture      - ссылка на изображение категории, если есть (string)
				? $args['custom_fields']                    // picture-path - путь к изображению категории, если есть (string)
				: array();                                  // childless    - есть ли дочерние категории (bool)
			// level        - уровень вложенности (int)

			$custom_sort = ! empty( $args['custom_sort'] )     // Кастомная сортировка
				// key-***  - что использовать в качестве ключа (только уникальные значения)
				? $args['custom_sort']                      // said-bar - массив повторяет древо категорий (обязательно возвращать в custom_fields parent и level категории)
				: '';

			$args = array(
				'taxonomy'     => isset( $args['taxonomy'] ) ? $args['taxonomy'] : $taxonomy,
				'child_of'     => isset( $args['child_of'] ) ? $args['child_of'] : $child_of,
				'parent'       => isset( $args['parent'] ) ? $args['parent'] : $parent,
				'orderby'      => isset( $args['orderby'] ) ? $args['orderby'] : $orderby,
				'order'        => isset( $args['order'] ) ? $args['order'] : $order,
				'hide_empty'   => isset( $args['hide_empty'] ) ? $args['hide_empty'] : $hide_empty,
				'hierarchical' => isset( $args['hierarchical'] ) ? $args['hierarchical'] : $hierarchical,
				'exclude'      => isset( $args['exclude'] ) ? $args['exclude'] : $exclude,
				'exclude_tree' => isset( $args['exclude_tree'] ) ? $args['exclude_tree'] : $exclude_tree,
				'include'      => isset( $args['include'] ) ? $args['include'] : $include,
				'number'       => isset( $args['number'] ) ? $args['number'] : $number,
				'offset'       => isset( $args['offset'] ) ? $args['offset'] : $offset,
				'show_count'   => isset( $args['show_count'] ) ? $args['show_count'] : $show_count,
				'pad_counts'   => isset( $args['pad_counts'] ) ? $args['pad_counts'] : $pad_counts,
				'name'         => isset( $args['name'] ) ? $args['name'] : $name,
				'name_like'    => isset( $args['name_like'] ) ? $args['name_like'] : $name_like,
				'slug'         => isset( $args['slug'] ) ? $args['slug'] : $slug,
				'fields'       => isset( $args['fields'] ) ? $args['fields'] : $fields
			);

			$wcatTerms  = get_categories( $args );
			$categories = $this->WC_getCustomFields( $wcatTerms, $custom_fields );

			return ! empty( $custom_sort ) ? $this->WC_customSort( $categories, $custom_sort ) : $categories;
		}

		// Custom fields

		/**
		 * @param $wcatTerms
		 * @param $custom_fields
		 *
		 * @return array
		 */
		private function WC_getCustomFields( $wcatTerms, $custom_fields ) {
			$categories = array();
			$allParents = array();

			foreach ( $wcatTerms as $term ) {
				$term = (array) $term;

				if ( empty( $custom_fields ) ) {
					$categories[] = $term;
					continue;
				}

				$category = array();

				foreach ( $custom_fields as $field ) {
					switch ( $field ) {
						case 'picture':
							$category[ $field ] = $this->WC_getCategoryPicture( $term['term_id'] );

							break;
						case 'picture-path':
							$category[ $field ] = $this->WC_getCategoryPicture( $term['term_id'], 'path' );

							break;
						case 'level':
							$category[ $field ] = $this->WC_getCategoryLevel( $term['parent'], $term['term_id'] );

							break;
						case 'childless':
							if ( empty( $allParents ) ) {
								$allParents = $this->WC_getAllParents( $wcatTerms );
							}

							$category[ $field ] = in_array( $term['term_id'], $allParents );

							break;
						default:
							$category[ $field ] = $term[ $field ];
					}
				}

				$categories[] = $category;
			}

			return $categories;
		}

		/**
		 * @param $term_id
		 * @param string $type
		 *
		 * @return string
		 */
		private function WC_getCategoryPicture( $term_id, $type = 'url' ) {
			$picture = false;

			$thumbnail_id = function_exists( 'get_term_meta' )
				? get_term_meta( $term_id, 'thumbnail_id', true )
				: get_woocommerce_term_meta( $term_id, 'thumbnail_id', true );

			if ( $type == 'url' ) {
				$picture = wp_get_attachment_url( $thumbnail_id );
			} elseif ( $type == 'path' ) {
				$picture = wp_get_original_image_path( $thumbnail_id );
			}

			return $picture ? $picture : '';
		}

		/**
		 * @param $term_parent
		 * @param $term_id
		 *
		 * @return int
		 */
		private function WC_getCategoryLevel( $term_parent, $term_id ) {
			global $taxonomy;

			return $term_parent
				? count( get_ancestors( $term_id, $taxonomy, 'taxonomy' ) )
				: 0;
		}

		/**
		 * @param $wcatTerms
		 *
		 * @return array
		 */
		private function WC_getAllParents( $wcatTerms ) {
			$allParents = array();

			foreach ( $wcatTerms as $wcat_term ) {
				$allParents[] = $wcat_term->parent;
			}

			return array_unique( $allParents );
		}

		// Custom sort

		/**
		 * @param $categories
		 * @param $type
		 *
		 * @return mixed
		 */
		private function WC_customSort( $categories, $type ) {
			if ( strripos( $type, 'key-' ) === 0 ) {
				return $this->WC_re_key( $categories, $type );
			}

			$result = array();

			switch ( $type ) {
				case 'said-bar':
					$result = $this->WC_sortSaidBar( $categories );

					break;
				default:

					break;
			}

			return $result;
		}

		/**
		 * @param $categories
		 * @param $type
		 *
		 * @return array
		 */
		private function WC_re_key( $categories, $type ) {
			$result = array();
			$key    = str_replace( 'key-', '', $type );

			foreach ( $categories as $category ) {
				if ( isset( $category[ $key ] ) && ! key_exists( $category[ $key ], $result ) ) {
					$result[ $category[ $key ] ] = $category;
				} else {
					#todo catch
					return $categories;
				}
			}

			return $result;
		}

		/**
		 * @param $categories
		 *
		 * @return array
		 */
		private function WC_sortSaidBar( $categories ) {
			$level_grouping = $this->WC_levelGrouping( $categories );

			if ( ! $level_grouping ) {
				return $categories;
			}

			$result = $this->WC_putCategoriesRoot( array_shift( $level_grouping ) );

			foreach ( $level_grouping as $categories ) {
				foreach ( $categories as $category ) {
					$category['children'] = array();
					$this->WC_searchParent( $category, $result );
				}
			}

			return $this->WC_singleLevelArray( $result );
		}

		/**
		 * @param $categories
		 *
		 * @return array|bool
		 */
		private function WC_levelGrouping( $categories ) {
			$level_grouping = array();

			foreach ( $categories as $category ) {

				if ( ! isset( $category['level'] ) ) {
					return false;
				}

				if ( ! key_exists( $category['level'], $level_grouping ) ) {
					$level_grouping[ $category['level'] ] = [ $category ];
				} else {
					$level_grouping[ $category['level'] ][] = $category;
				}
			}

			ksort( $level_grouping );

			return $level_grouping;
		}

		/**
		 * @param $categories_root
		 *
		 * @return array
		 */
		private function WC_putCategoriesRoot( $categories_root ) {
			$result = array();

			foreach ( $categories_root as $category ) {
				$category['children']           = array();
				$result[ $category['term_id'] ] = $category;
			}

			return $result;
		}

		/**
		 * @param $category
		 * @param $result
		 */
		private function WC_searchParent( $category, &$result ) {
			if ( isset( $result[ $category['parent'] ] ) ) {
				$result[ $category['parent'] ]['children'][ $category['term_id'] ] = $category;
			} else {
				foreach ( $result as &$parent ) {
					if ( isset( $parent['children'] ) ) {
						$this->WC_searchParent( $category, $parent['children'] );
					}
				}

				unset( $parent );
			}
		}

		/**
		 * @param array $array
		 * @param array $data
		 *
		 * @return array|mixed
		 */
		private function WC_singleLevelArray( array $array, $data = array() ) {
			foreach ( $array as $item ) {
				$category = array();

				foreach ( $item as $field => $value ) {
					if ( $field != 'children' ) {
						$category[ $field ] = $value;
					}
				}

				$data[] = $category;

				if ( ! empty( $item['children'] ) ) {
					$data = $this->WC_singleLevelArray( $item['children'], $data );
				}
			}

			return $data;
		}
	}

endif;