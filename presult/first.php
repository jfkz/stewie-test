<?php
require_once "lib/config.php";
require_once "lib/DbSimple/Generic.php";

$GLOBALS["DB"] = DbSimple_Generic::connect('mysql://root:1111111@localhost/test');

$GLOBALS["DB"]->setErrorHandler('databaseErrorHandler');

function databaseErrorHandler($message, $info)
{
	// Если использовалась @, ничего не делать.
	if (!error_reporting()) return;
	// Выводим подробную информацию об ошибке.
	echo "SQL Error: $message<br><pre>"; 
	print_r($info);
	echo "</pre>";
	exit();
}

interface i_model
{
	public function __construct($id = null);
	public function set_field($field, $value);
	public function get_field($field);
	public function save();
	public function delete();
	public function id();
}

abstract class model implements i_model
{
	private $tableName = null;
	private $id = null;
	private $db = null;
	protected $fields = array();

	public function __construct($id = null){
		$this->tableName = get_class($this);

		if(empty($GLOBALS["DB"])){
			die("DB connection error");
		}

		$this->db = $GLOBALS["DB"];//По грамотному нужно это дело передавать на вход, но это не тот случай...

		$result = $this->db->select('SHOW TABLES');//Смотрим список таблиц в текущей БД

		$tableExist = false;
		
		foreach ($result as $key => $value) {
			if(in_array($this->tableName, $value)){//Ищем таблицу по названия класса
				$tableExist = true;
				break;
			}
		}

		if(!$tableExist){
			die("Error: table '" . $this->tableName . "' not exist");
		}

		if(!empty($id)){
			$record = $this->db->select('SELECT * FROM `' . $this->tableName . '` WHERE `id`=' . (int)$id);
			if(count($record) > 0){
				$this->id = $id;
				foreach ($record[0] as $key => $value) {
					if(array_key_exists($key, $this->fields)){
						$this->fields[$key]["value"] = $value;
					}
				}
			}
		}
	}

	public function get_field($field){
		if(empty($field)){
			return null;
		}

		if(array_key_exists($field, $this->fields)){
			return $this->fields[$field]["value"];
		}else{
			return null;
		}
	}


	public function set_field($field, $value){
		if(!array_key_exists($field, $this->fields)){
			return;
		}

		switch($this->fields[$field]["type"]){
			case 'int':
				$value = (int)$value;
				break;

			case 'string':
				$value = $this->fields[$field]["safe"] ? htmlspecialchars($value) : $value;
				break;
		}

		$this->fields[$field]["value"] = $value;
	}

	public function save(){
		$sql = array();

		foreach ($this->fields as $key => $value) {
			$sql[] = "`" . $key . "`" . "=" . "'" . $value["value"] . "'";
		}

		$res = $this->db->query("UPDATE `" . $this->tableName . "` SET " . implode(",", $sql));

		if($res){
			return true;
		}else{
			return false;
		}
	}

	public function delete(){
		$res = $this->db->query("DROP TABLE `" . $thos->tableName . "`");

		if($res){
			return true;
		}else{
			return false;
		}
	}

	public function id(){
		return $this->id;
	}

}

class blog_post extends model
{
	protected $fields = array(
		"id" => array("type" => "int", "value" => null),
		"name" => array("type" => "string", "safe" => true, "value" => null),
		"text" => array("type" => "string", "safe" => true, "value" => null),
		"date" => array("type" => "date", "value" => null),
	);
}