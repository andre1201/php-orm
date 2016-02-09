<?php
/**
 * Created by PhpStorm.
 * User: Дрон
 * Date: 05.02.16
 * Time: 19:05
 */

require_once("fields.php");
require_once("database.php");
require_once("migrations.php");

/**
 * @param $obj
 */
function debug($obj){
    echo "<pre>";
    print_r ($obj);
    echo "</pre>";
}

/**
 * Class Model
 * @property $this[] query_set
 * @property string table_name
 * @property AutoField id
 * @property array fields
 */
abstract class Model extends DataBase{


    final public  function __construct(){
        $this->id =  new AutoField();
        $this->fields = $this->get_fields();
        $this->init();
        $this->table_name = $this->get_table_name();
        $this->query_set = null; # содержит результат отработки запроса к базе данных
    }

    abstract public function init();

    /**
     * @return array
     */
    protected  function get_fields()
    {
        $fields = get_object_vars($this);
        $result = array();
        foreach($fields as $key=>$val)
            if ($val instanceof Field)
                $result[$key] =  $val;
        return $result;
    }

    /**
     * @return bool
     */
    public function create_model(){
        $primary_key_name="id";
        #Migrations::create_migrations($table_name,$fields);
        $SQL = "CREATE TABLE IF NOT EXISTS $this->table_name (
        $primary_key_name INT( 5 ) unsigned NOT NULL AUTO_INCREMENT,
        PRIMARY KEY ($primary_key_name)
        );
        ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1 ";
        $this->SQL($SQL);
        $this->update_model();

        return true;
    }

    /**
     * @return bool
     */
    public function update_model(){

        $fields = $this->get_fields();
        foreach ($fields as $key => $value){
            $SQL = "ALTER TABLE  $this->table_name CHANGE   $key  $key ".$value->generic_field($key,$this->table_name).";";
            debug("Поле изменено ".$SQL."<BR>");
            $result = $this->SQL($SQL);
            if(!$result){
                $SQL = "ALTER TABLE  $this->table_name ADD   $key ".$value->generic_field($key,$this->table_name).";";
                debug("Поле добавлено ".$SQL."<BR>");
                $result = $this->SQL($SQL);
            }

        }
        return true;
    }
    /*
     * Удаляет таблицу
     */
    /**
     * @return bool
     */
    public function delete_model(){
        $SQL = "DROP TABLE $this->table_name ";
        $result =  $this->SQL_EXEC($SQL);
        if ($result)
            return true;
        return false;
    }

    public function delete(){
        $table_name = $this->get_table_name();
        $SQL = "DELETE FROM $table_name WHERE id =  $this->id; ";
        if($this->SQL_EXEC($SQL))
            return true;
        return false;
        }


    /**
     * Возвращает имя таблицы
     * @return string
     */
    public function get_table_name(){
        return get_class($this);
    }


    public function save(){
        $table_name = $this->get_table_name();
        $fields = $this->get_fields();
        $fields_name = array();
        $values = array();
        $new_record = true;
        $is_default = false;
        foreach ($fields as $key => $value){
            $default = $value->properties['DEFAULT'];
            /** @var Field $value */
            $is_value = $value->get_value();
            if(!$default and $is_value!="") {
                $is_default = true;
                array_push($fields_name, $key);
                $values[$key]= "'".$value."'";
            }elseif($default and $is_value!=""){
                array_push($fields_name, $key);
                $values[$key]= "'".$value."'";
            }
        }
        $id = $values["id"];
        if($id != "''" and $id !=null){
            $new_record = false;
        }
        if($new_record){
            $slise_index = 1;
            if($is_default)
                $slise_index = 0;
            $sql_field = implode(array_slice($fields_name, $slise_index), ',');
            $sql_values = implode(array_slice($values, $slise_index), ',');
            $SQL = "INSERT INTO $table_name (".$sql_field.")
            VALUES (".$sql_values.")";
            $result = $this->SQL_EXEC($SQL);
            debug($SQL);
            if (empty($result))
                return false;
            $SQL = "SELECT  `AUTO_INCREMENT`
                FROM INFORMATION_SCHEMA.TABLES
                WHERE TABLE_SCHEMA =  '".DataBase::DB_NAME."'
                AND TABLE_NAME =  '$table_name'
                LIMIT 0 , 30";
            $result= $this->SQL($SQL);
            $result = $result->fetch(PDO::FETCH_ASSOC);
            $new_id = $result["AUTO_INCREMENT"]-1;
            $this->id->set((string)$new_id);
            $this->query_set = $this;
            return true;
        }

        if(!$new_record){
            $SQL = "UPDATE  $table_name SET ";
            $temp = "";
            foreach($fields_name as $name){
                $temp.= " $name = $values[$name], ";
            }
            $temps = substr($temp, 0,strlen($temp)-2);
            $SQL.=$temps."  WHERE id = $values[id] ";
            $result = $this->SQL($SQL);
            debug($SQL);
            if (!empty($result))
                $this->query_set = $this;
                return true;

        }
        return false;
    }

    public function __call($fieldname, $args = array()){
        /** Вызывает функцию при не существующем обращении функции */
        $this->$fieldname->set($args[0]);
        return true;
    }

    /**
     * @param $id
     * @return $this
     */
    public function get($id){
        $SQL = "SELECT * from $this->table_name where id=$id";
        $result = $this->SQL($SQL);
        if($result)
            return $this->return_objects($result);
        return false;
    }


    /**
     * @return $this[]
     */
    public function get_count($limit = 30){
        $SQL = "SELECT * from $this->table_name LIMIT $limit";
        $result = $this->SQL($SQL);
        if($result)
            return  $this->return_objects($result);
        return false;
    }

    /**
     * @return $this[]
     */
    public function get_all(){

        $table_name = $this->get_table_name();
        $fields = $this->get_fields();
        $SQL = "SELECT * from $table_name";
        $result = $this->SQL($SQL);
        $i = 0;
        if($result) {
            return $this->return_objects($result);
        }
        return false;
    }

    protected function return_objects($result){
        $i = 0;
        $fields = $this->get_fields();
        if($result->rowCount() > 1){
            $result = $result->fetchAll(PDO::FETCH_ASSOC);
            foreach ($result as $key => $value){
                $objects[] = new $this->table_name();
                foreach ($fields as $key2 => $value2)
                        $objects[$i]->$key2->set($value[$key2]);
                $i++;
            }
            $this->query_set = $objects;
            return $objects;
        }
        $result = $result->fetch(PDO::FETCH_ASSOC);
        $objects = new $this->table_name();
        foreach ($objects as $key => $value){
            if($value instanceof Field) {
                $val_res = $result[$key];
                $this->$key->set($val_res);
                $objects->$key->set($val_res);
                $objects->$key->set($val_res);
            }
        }
        $this->query_set = $objects;
        return $objects;
    }


    /**
     * @return $this[]
     */
    public function filter(){
        $arg_list = func_get_args();
        $kwargs = array();
        $fields = $this->get_fields();
        $table_name = $this->get_table_name();
        $obj = array();
        $params_filter = array();
        foreach ($arg_list as $key =>$val){
            $params = explode('=',$val);
            if ($params[0] and $params[1]){
                $kwargs[$params[0]] =  $params[1];
                if(!array_key_exists($params[0], $fields))
                    return false;
                $WHERE = implode('', array($params[0],'=',"'".$params[1]."'"));
                array_push($params_filter, $WHERE);
            }
            else
                return false;
        }
        $WHERE = implode(' AND ', $params_filter);
        $FILTER =  "SELECT * FROM $table_name WHERE $WHERE";
        $result = $this->SQL($FILTER);
        if($result)
            return $this->return_objects($result);
        return false;
    }

    public  function __toString()
    {
        /** @var $val Field */
        /** @var $key string */
        $result = array();
        foreach ($this->get_fields() as $key => $val){
            $result[$key] = $val;
        }
        return (array)$result;
    }

    /**
     * Выводит информацию об объекте
     * return $this
     */
    public function info(){
        debug($this->__toString());
    }

    /**
     * @param null $x
     * @return $this[]
     */
    public function __invoke($count_record = null)
    {
        if($count_record > 1)
            if($count_record != null){
                $this->query_set = $this->get_count($count_record);
                return $this->query_set;
            }
        if($this->query_set == null or $count_record == 1)
            $this->query_set = $this->get_all();
        return $this->query_set;
    }
}
