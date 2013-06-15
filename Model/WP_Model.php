<?php
/**
 * Class WP_Model
 * @extends Model
 */
/** You must have Wordpress installed to use this class */
abstract class WP_Model extends Model {
	public function __construct()
	{
		global $wpdb;
		$this->prefix = $wpdb->prefix;
		$this->table_name = $this->prefix . 'table_' . self::$count;
		self::$count++;
	}

	public function insert($data, $data_format)
	{
		if ($this->validate($data)) {
			global $wpdb;
			if (count(date) === count($data_format))
				$wpdb->insert($this->get_table_name(), $data, $data_format);
			else
				throw new Exception("Mismatch between data given to Model::insert() and data_format");
		}
	}

	public function delete($where)
	{
		$sql = "DELETE FROM " . $this->get_table_name() . " WHERE $where";
		global $wpdb;
		$wpdb->query($sql);
	}

	public function get($what, $where = "")
	{
		$sql = $this->build_get_sql_str($what, $where);

		global $wpdb;
		$select_data = $wpdb->get_results($sql, 'ARRAY_A');

		return $select_data;
	}

	public function __destruct() {}
}