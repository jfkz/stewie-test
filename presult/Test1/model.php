<?php

/**
 * Class model
 */
abstract class model implements i_model
{
	private $_fields_info = [];
	private $_fields      = [];

	/**
	 * model constructor.
	 * @param null $id
	 * @throws Exception
	 */
	public function __construct($id = null)
	{
		if ($id) {
			$this->_fields = $this->db()->selectRow("SELECT * FROM {$this->table_name()} WHERE id = ?", $id);

			if (!$this->_fields || !count($this->_fields)) {
				throw new Exception('Can\'t get object from database');
			}
		}

		$fields_info = database::getInstance()->getConnection()->select("DESC {$this->table_name()}");

		if (!$fields_info || !count($fields_info)) {
			throw new Exception('Can\'t get fields info from database');
		}

		foreach ($fields_info as $field_info) {
			$field_name = $field_info['Field'];
			$this->_fields_info[$field_name] = $field_info;
			if (!isset($this->_fields[$field_name])) {
				$this->_fields[$field_name] = null;
			}
		}

	}

	/**
	 * Get database connection
	 * @return DbSimple_Mysql|mixed|null
	 */
	private function db()
	{
		return database::getInstance()->getConnection();
	}

	/**
	 * Check field exist
	 * @param $field
	 * @return bool
	 */
	private function is_field_exist($field)
	{
		return (key_exists($field, $this->_fields) && isset($this->_fields_info[$field]));
	}

	/**
	 * Get table name
	 * @return string
	 */
	private function table_name()
	{
		$path = explode('\\', get_class($this));

		return array_pop($path);
	}

	/**
	 * Set field
	 * @param $field
	 * @param $value
	 * @return $this
	 * @throws Exception
	 */
	public function set_field($field, $value)
	{
		if ($field === 'id') {
			throw new Exception('Change object id is now allowed. Use clone instead');
		}

		if (!$this->is_field_exist($field)) {
			throw new Exception('Unknown field : ' . $field);
		}

		$this->_fields[$field] = $value;

		return $this;
	}

	/**
	 * Get field
	 * @param $field
	 * @return bool
	 * @throws Exception
	 */
	public function get_field($field)
	{
		if (!$this->is_field_exist($field)) {
			throw new Exception('Unknown field : ' . $field);
		}

		return $this->_fields[$field];
	}

	/**
	 * Save object to database
	 * @return int|bool
	 * @throws Exception
	 */
	public function save()
	{

		$field_names = array_keys($this->_fields_info);
		unset($field_names['id']);

		$args = [];
		if ($this->id()) {
			$args[] = "UPDATE {$this->table_name()} SET `" . implode("` = ?,`", $field_names) . "` = ? WHERE id = ?";
			foreach ($field_names as $field_name) {
				$args[] = $this->_fields[$field_name];
			}
			$args[] = $this->id();
		} else {
			$args[] = "INSERT INTO {$this->table_name()} (`" . implode("`,`", $field_names) . "`) VALUES (" . implode(',', array_fill(0, count($field_names), '?')) . ")";
			foreach ($field_names as $field_name) {
				$args[] = $this->_fields[$field_name];
			}
		}

		if (!$result = call_user_func_array([$this->db(), 'query'], $args)) {
			throw new Exception('Can\'t save object to database: ' . $this->db()->errmsg);
		}

		if (!$this->id()) {
			$this->_fields['id'] = $result;
		}

		return $this->id();
	}

	/**
	 * Delete object from database
	 * @return bool
	 */
	public function delete()
	{
		if ($this->id()) {
			return ($this->db()->query("DELETE FROM {$this->table_name()} WHERE id = ?", $this->id())) ? true : false;
		}

		return false;
	}

	/**
	 * Get object ID
	 * @return int|null
	 */
	public function id()
	{
		return isset($this->_fields['id']) ? (int)$this->_fields['id'] : null;
	}

	/**
	 * clone
	 */
	public function __clone()
	{
		$this->_fields['id'] = null;
	}
}