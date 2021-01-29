<?php


class VK_Model_Albums extends WC_VKontakte_Model
{
    /**
     * Get data albums
     *
     * @param int|string $search
     * @param string $code
     * @param string $return
     *
     * @return array
     */
    public function get($search, $code, $return = 'all') {
	    $sql = "SELECT * FROM " . $this->prefix . self::VK_DB_ALBUMS . " WHERE " . $code . " = '" . $search . "'";
	    $query = $this->wpdb->get_results($sql, 'ARRAY_A');

	    return $this->refactorResult($query, $return);
    }

	/**
	 * Get all albums
	 *
	 * @param string $key - column value as a key of an array element
	 *
	 * @return array
	 */
    public function getAll($key = null) {
		$sql = "SELECT * FROM " . $this->prefix . self::VK_DB_ALBUMS;
		$query = $this->wpdb->get_results($sql, 'ARRAY_A');

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
	 * Set data albums
	 *
	 * @param array $data
	 *
	 * @return false|int
	 */
    public function insert($data) {
	    $current_date = date("Y-m-d H:i:s");

    	$data['date_added']     = $current_date;
    	$data['date_modified']  = $current_date;

        return $this->wpdb->insert(
	        $this->prefix . self::VK_DB_ALBUMS,
	        $data
        );
    }

	/**
	 * Delete albums
	 *
	 * @param int|string $value
	 * @param string $code
	 * @param null|int $wp_parent_id
	 *
	 * @return false|int
	 */
    public function delete($value, $code, $wp_parent_id = null) {
        $where  = [$code => $value];

	    if (isset($wp_parent_id)) {
		    $where[$this->prefix . 'parent_id'] = $wp_parent_id;
	    }

        return $this->wpdb->delete(
	        $this->prefix . self::VK_DB_ALBUMS,
	        $where
        );
    }
}