<?php


class VK_Model_Orders extends WC_VKontakte_Model
{
    /**
     * Get data order
     *
     * @param mixed $search
     * @param string $code
     * @param string $return
     *
     * @return array
     */
    public function get($search, $code, $return = 'all')
    {
	    $sql = "SELECT * FROM " . $this->prefix . self::VK_DB_ORDERS . " WHERE " . $code . " = '" . $search . "'";
	    $query = $this->wpdb->get_results($sql, 'ARRAY_A');

	    return $this->refactorResult($query, $return);
    }

    /**
     * Get all orders
     *
     * @param string $key
     *
     * @return array
     */
    public function getAll($key = null)
    {
	    $sql = "SELECT * FROM " . $this->prefix . self::VK_DB_ORDERS;
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
	 * Set data order
	 *
	 * @param $data
	 *
	 * @return bool|false|int
	 */
    public function insert($data)
    {
	    $current_date = date("Y-m-d H:i:s");
	    
	    $data['date_added']     = $current_date;
	    $data['date_modified']  = $current_date;

	    return $this->wpdb->insert(
		    $this->prefix . self::VK_DB_ORDERS,
		    $data
	    );
    }

	/**
	 * Edit data order
	 *
	 * @param $data
	 *
	 * @return false|int
	 */
	public function edit($data)
	{
		if (!key_exists('vk_id', $data)) {
			return false;
		}

		$where = [
			'vk_id' => $data['vk_id']
		];

		$data['date_modified']  = date("Y-m-d H:i:s");

		return $this->wpdb->update(
			$this->prefix . self::VK_DB_ORDERS,
			$data,
			$where
		);
	}

	/**
	 * @param $value
	 * @param $code
	 *
	 * @return bool|false|int
	 */
    public function delete($value, $code)
    {
	    $where  = [$code => $value];

	    return $this->wpdb->delete(
		    $this->prefix . self::VK_DB_ORDERS,
		    $where
	    );
    }
}