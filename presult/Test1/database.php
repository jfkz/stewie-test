<?php


/**
 * Class database
 */
class database
{

	private $_host     = 'localhost';
	private $_username = 'root';
	private $_password = '';
	private $_database = 'stewie_test_1';

	/**
	 * @var DbSimple_Mysql|null
	 */
	private static $_connection = null;

	/**
	 * @var database
	 */
	private static $_instance = null;

	/**
	 * database constructor.
	 */
	private function __construct()
	{
		self::$_connection = DbSimple_Generic::connect("mysql://{$this->_username}:{$this->_password}@{$this->_host}/{$this->_database}");
	}

	/**
	 * restrict clone this object
	 */
	protected function __clone()
	{

	}

	/**
	 * @return database|null
	 */
	static public function getInstance()
	{

		if (is_null(self::$_instance)) {
			self::$_instance = new self();
		}

		return self::$_instance;
	}

	/**
	 * @return DbSimple_Mysql|mixed|null
	 */
	public function getConnection()
	{
		return self::$_connection;
	}
}