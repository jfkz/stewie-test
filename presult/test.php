<?php
require_once "lib/DbSimple/Generic.php";

define('DB_CONNECT_STRING', 'mysql://test:test@localhost/test');

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
    /**
     * @var mixed
     */
    protected $db;
    /**
     * @var string таблица
     */
    protected $table;
    /**
     * @var array поля таблицы
     */
    protected $fields;
    /**
     * @var null|int идентификатор 
     */
    protected $id;
    
    function __construct($id = null){
        $this->id = $id;
        $this->table = get_class($this);
        $this->connect();
        if ($this->id != NULL){
            $this->select();
        }
    }
    
    protected function connect(){
        $this->db = DbSimple_Generic::connect(DB_CONNECT_STRING);
        /**
         * TODO: обработка ошибок подключения
         *      наличие таблицы в БД
         */
    }

    protected function select(){
        $this->fields = $this->db->selectRow('SELECT * FROM ?# WHERE id = ?', $this->table, $this->id);
        /**
         * TODO: обработка пустого результата, если записи с таким id нет в базе
         */
    }

    protected function insert(){
        $this->id = $this->db->query('INSERT INTO ?# SET ?a', $this->table, $this->fields);
    }

    protected function update(){
        return $this->db->query('UPDATE ?# SET ?a WHERE id = ?', $this->table, $this->fields, $this->id);
    }

    public function set_field($field, $value){
        /**
         * TODO: обработка пустых значений
         */
        $this->fields[$field] = $value;
    }
    
    public function get_field($field){
        return isset($this->fields[$field])? $this->fields[$field] : NULL;
    }
    
    public function save(){
        if ($this->id == NULL){
            $this->insert();
        }
        else {
            $this->update();
        }
    }
    
    public function delete(){
        return $this->db->query('DELETE FROM ?# WHERE id = ?', $this->table, $this->id);
    }
    
    public function id(){
        return $this->id;
        /**
         * TODO: можно выводить не переданный id, а идентификатор из базы, тогда
         *      return isset($this->fields['id'])? $this->fields['id'] : NULL;
         */
    }
}

class blog_post extends model
{
    public function __construct($id = null){
        parent::__construct($id);
    }
    
    protected function insert() {
        if (!isset($this->fields['date'])){
            $this->fields['date'] = Date('Y-m-d H:i:s') ;
            /**
             * надо еще учитывать формат даты в БД и временную зону
             */
        }
        parent::insert();
    }
}


$post_1 = new blog_post(1);
$post_2 = new blog_post(2);

echo $post_1->get_field('date');
echo $post_1->get_field('text');

$post_1->delete();
$post_2->set_field('name', 'Some new name');
$post_2->save();


$post_3 = new blog_post();
$post_3->set_field('name', 'Test');
$post_3->set_field('text', 'Test text');
$post_3->save();
echo $post_3->id();

// blog_topic
/*
$topic = new blog_topic(1);
echo $post_3->set_field('name');
*/