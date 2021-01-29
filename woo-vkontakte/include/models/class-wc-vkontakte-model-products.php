<?php


class VK_Model_Products extends WC_VKontakte_Model {
	/**
	 * Get data product
	 *
	 * @param mixed $search
	 * @param string $code
	 * @param string $return
	 *
	 * @return array
	 */
	public function get( $search, $code, $return = 'all' ) {
		$sql   = "SELECT * FROM " . $this->prefix . self::VK_DB_PRODUCTS . " WHERE " . $code . " = '" . $search . "'";
		$query = $this->wpdb->get_results( $sql, 'ARRAY_A' );

		return $this->refactorResult( $query, $return );
	}

	/**
	 * Get all products
	 *
	 * @return array
	 */
	public function getAll() {
		$sql   = "SELECT * FROM " . $this->prefix . self::VK_DB_PRODUCTS;
		$query = $this->wpdb->get_results( $sql, 'ARRAY_A' );

		return $this->glueProducts( $query );
	}

	/**
	 * Set data product
	 *
	 * @param $data
	 *
	 * @return false|int
	 */
	public function insert( $data ) {
		$current_date = date( "Y-m-d H:i:s" );

		$data['date_added']    = $current_date;
		$data['date_modified'] = $current_date;

		return $this->wpdb->insert(
			$this->prefix . self::VK_DB_PRODUCTS,
			$data
		);
	}

	/**
	 * Edit data product
	 *
	 * @param $data
	 *
	 * @return false|int
	 */
	public function edit( $data ) {
		if ( ! key_exists( 'vk_id', $data ) ) {
			return false;
		}

		$where = [
			'vk_id' => $data['vk_id']
		];

		$data['date_modified'] = date( "Y-m-d H:i:s" );

		return $this->wpdb->update(
			$this->prefix . self::VK_DB_PRODUCTS,
			$data,
			$where
		);
	}

	/**
	 * Delete product
	 *
	 * @param $value
	 * @param $code
	 *
	 * @return false|int
	 */
	public function delete( $value, $code ) {
		$where = [ $code => $value ];

		return $this->wpdb->delete(
			$this->prefix . self::VK_DB_PRODUCTS,
			$where
		);

	}

	/**
	 * Glue product variants into one product
	 *
	 * @param $query
	 *
	 * @return array
	 */
	private function glueProducts( $query ) {
		$data = array();

		foreach ( $query as $row ) {
			if ( ! key_exists( $row[ $this->prefix . 'id' ], $data ) ) {
				$data[ $row[ $this->prefix . 'id' ] ] = array(
					$this->prefix . 'id' => $row[ $this->prefix . 'id' ],
					'categories_albums'  => json_decode( $row['categories_albums'], true ),
					'offers'             => array(
						$row['offer'] => array(
							'vk_id' => $row['vk_id'],
							'offer' => $row['offer']
						)
					)
				);
			} else {
				$data[ $row[ $this->prefix . 'id' ] ]['offers'][ $row['offer'] ] = array(
					'vk_id' => $row['vk_id'],
					'offer' => $row['offer']
				);
			}
		}

		return $data;
	}
}