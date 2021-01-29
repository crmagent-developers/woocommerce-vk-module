<?php


class VK_Model_Customers extends WC_VKontakte_Model {
	/**
	 * Get customer
	 *
	 * @param int $id
	 * @param string $code
	 * @param string $return
	 *
	 * @return array
	 */
	public function get( $id, $code, $return = 'all' ) {
		$sql   = "SELECT * FROM " . $this->prefix . self::VK_DB_CUSTOMERS . " WHERE " . $code . " = '" . (int) $id . "'";
		$query = $this->wpdb->get_results( $sql, 'ARRAY_A' );

		return $this->refactorResult( $query, $return );
	}

	/**
	 * Get all customers
	 *
	 * @param string $key
	 *
	 * @return array
	 */
	public function getAll( $key = null ) {
		$sql   = "SELECT * FROM " . $this->prefix . self::VK_DB_CUSTOMERS;
		$query = $this->wpdb->get_results( $sql, 'ARRAY_A' );

		$data = array();

		if ($key !== null) {
			foreach ($query as $row) {
				$data[$row[$key]] = $row;
			}
		} else {
			$data = $query;
		}

		return $data;
	}

	/**
	 * Set data customer
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
			$this->prefix . self::VK_DB_CUSTOMERS,
			$data
		);
	}

	/**
	 * Delete customer
	 *
	 * @param int $id
	 * @param string $code
	 *
	 * @return false|int
	 */
	public function delete( $id, $code ) {
		$where = [ $code => $id ];

		return $this->wpdb->delete(
			$this->prefix . self::VK_DB_CUSTOMERS,
			$where
		);
	}
}