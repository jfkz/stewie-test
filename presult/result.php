<?php

interface ModelInterface
{
    public function __construct($id = null);

    public function setField($field, $value);

    public function getField($field);

    public function save();

    public function delete();

    public function id();
}

abstract class Model implements ModelInterface
{
    /**
     * @var mixed variable to store db connection
     */
    protected $db;

    /**
     * @var array databases fields
     */
    protected $fields;

    /**
     * @var string variable to store databases table name
     */
    protected $tableName;

    /**
     * @var null|int variable to store current collection id
     */
    protected $id;

    public function __construct($id = null)
    {
        require_once "../vendor/DbSimple/Generic.php";
        $this->db = DbSimple_Generic::connect("mysql://root:@localhost/test");
        $this->id = $id;
        $this->setTableName();
        $this->setModelFields();
        $this->setFieldsData();
    }

    /**
     * Gets a collection Id
     *
     * @return null| int id current model id
     */
    public function id()
    {
        return $this->id;
    }

    /**
     * Sets field's value
     *
     * @param $field - field
     * @param $value - value to be set for associated field
     */
    public function setField($field, $value)
    {
        $this->fields[$field] = $value;
    }

    /**
     * Gets field
     *
     * @param $field - field from the table
     * @return null
     */
    public function getField($field)
    {
        return $this->fields[$field];
    }

    /**
     * Saves data to the database
     */
    public function save()
    {
        if (!is_null($this->id)) {
            $this->updateRow($this->id());
        } else {
            $this->insertRow();
        }
    }

    /**
     * Deletes row from the database
     */
    public function delete()
    {
        if (!is_null($this->id)) {
            $sql = "DELETE FROM " . $this->tableName . " WHERE id=?";
            $this->db->query($sql, $this->id);
        } else {
            foreach ($this->fields as $key => $value) {
                $this->fields[$key] = $value;
            }
        }
    }

    /**
     * Sets table name for the model
     * either can be defined in the child class as protected variable $tableName
     * or we can use current class name otherwise
     *
     */
    protected function setTableName()
    {
        if (is_null($this->tableName)) {
            $this->tableName = get_class($this);
        }
    }

    /**
     * Sets fields from the database
     */
    protected function setModelFields()
    {
        $result = $this->db->query('DESCRIBE ' . $this->tableName);
        if (!empty($result)) {
            foreach ($result as $value) {
                $this->fields[$value['Field']] = $value['Default'];
            }
        }
    }

    /**
     * Sets initial value to fields
     */
    protected function setFieldsData()
    {
        if (!is_null($this->id)) {
            $sql = "SELECT * FROM " . $this->tableName . " WHERE id=? LIMIT 1";
            $result = $this->db->selectRow($sql, $this->id);
            if (!empty($result)) {
                foreach ($result as $key => $value) {
                    if (array_key_exists($key, $this->fields)) {
                        $this->fields[$key] = $value;
                    }
                }
            } else {
                $this->id = null;
            }
        }
    }

    /**
     * Inserts new row to the database
     */
    protected function insertRow()
    {
        $fieldsSql = implode(',', array_keys($this->fields));
        $valuesSql = implode("','", array_values($this->fields));

        $sql = "INSERT INTO " . $this->tableName . " (" . $fieldsSql . ") VALUES('" . $valuesSql . "')";

        $this->id = $this->db->query($sql);
    }

    /**
     * Updates database rows by id
     *
     * @param $id - row id
     */
    protected function updateRow($id)
    {
        $updateSql = '';
        foreach ($this->fields as $key => $value) {
            $updateSql .= " " . $key . "='" . $value . "',";
        }
        $updateSql = rtrim($updateSql, ',');

        $sql = "UPDATE " . $this->tableName . " SET " . $updateSql . " WHERE id = ?";

        $this->db->query($sql, $id);
    }
}

class BlogPostModel extends Model
{
    /**
     * @var string table name if defined here, will be used for table name
     * otherwise class name will be assumed as table name
     */
    protected $tableName = 'blog_post';

    public function __construct($id = null)
    {
        parent::__construct($id);
    }
}

class BlogTopicModel extends Model
{
    public function __construct($id = null)
    {
        parent::__construct($id);
    }
}


$post_1 = new BlogPostModel(1);
$post_2 = new BlogPostModel(2);

echo $post_1->getField('date');
echo $post_1->getField('text');

$post_1->delete();
$post_2->setField('name', 'Some new name');
$post_2->save();


$post_3 = new BlogPostModel();
$post_3->setField('name', 'Test');
$post_3->setField('text', 'Test text');
$post_3->save();
echo $post_3->id();


// blog_topic

//$topic = new blog_topic(1);
//echo $post_3->set_field('name');
