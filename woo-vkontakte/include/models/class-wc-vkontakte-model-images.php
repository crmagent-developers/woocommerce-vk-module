<?php


class VK_Model_Images extends WC_VKontakte_Model {
	/**
	 * Get image
	 *
	 * @param string $wp_path
	 * @param int $wp_source_id
	 * @param string $type
	 *
	 * @return array
	 */
	public function get( $wp_path, $wp_source_id, $type ) {
		$sql = "SELECT * FROM " . $this->prefix . self::VK_DB_IMAGES . " WHERE " . $this->prefix . "path = '" . $wp_path . "' AND " . $this->prefix . "source_id = '" . $wp_source_id . "' AND `type` = '" . $type . "'";

		return $this->wpdb->get_results( $sql, 'ARRAY_A' );
	}

	/**
	 * Set image
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
			$this->prefix . self::VK_DB_IMAGES,
			$data
		);
	}

	/**
	 * Delete image
	 *
	 * @param $wp_source_id
	 * @param $type
	 *
	 * @return false|int
	 */
	public function delete( $wp_source_id, $type ) {

		$where = [
			$this->prefix . 'source_id' => $wp_source_id,
			'type'                      => $type
		];

		return $this->wpdb->delete(
			$this->prefix . self::VK_DB_IMAGES,
			$where
		);
	}
}