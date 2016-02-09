<?php
/**
 * Created by PhpStorm.
 * User: Дрон
 * Date: 05.02.16
 * Time: 19:51
 */


/**
 * @property array properties
 */
abstract class Field
{
    var $properties = array(
        "NULL" => true,
        "MAX_LENGTH" => 255,
        "DEFAULT" => null,
        "RELATION" => false,
        "UNIQUE" => false
    );

    protected  $value = '';

    /*
     * Получение типа поля
     */
    abstract  function get_type();
    public function get_value(){
        return $this->value;
    }

    public function __construct($properties = array())
    {
        $valid_prop = $this->get_valid_properties();
        foreach ($properties as $key => $value) {
            foreach ($valid_prop as $key_valid_prop) {
                if (strtoupper($key) == strtoupper($key_valid_prop))
                    $this->properties[strtoupper($key_valid_prop)] = $value;
            }
        }
    }

    /**
     * @param $value
     */
    public function set($value)
    {
        $this->value = $value;
    }


    /*
     * Возвращает массив свойст
     * @return array
     */
    public function get_propeties()
    {
        return $this->properties;
    }

    protected function set_properties($properties = array())
    {
        foreach ($properties as $key => $value) {
            $this->properties[strtoupper($key)] = $value;
        }
    }


    public function get_valid_properties()
    {
        $result = array();
        foreach ($this->get_propeties() as $key => $value)
            array_push($result, $key);
        return $result;
    }



    /**
     * Создает строку SQL
     * @param $field_name
     * @param string $table_name
     * @return string
     */
    public function generic_field($field_name, $table_name="news")
    {
        /** @var $field string */
        $field = $this->get_type();
        $properties = $this->get_propeties();
        if (!$properties["RELATION"]){
            if($properties[MAX_LENGTH])
                $field .= "($properties[MAX_LENGTH]) ";
            foreach ($properties as $key => $value) {
                if ($key == "NULL")
                    if ($value == true)
                        $field .= " NULL ";
                    else
                        $field .= " NOT NULL ";
                if ($key == "DEFAULT")
                    if ($value)
                        $field .= " DEFAULT '$value' ";
                if ($key == "AUTO_INCREMENT")
                    if ($value)
                        $field .= " AUTO_INCREMENT $value ";
                if ($key == "AUTO_INCREMENT")
                    if ($value)
                        $field .= " ADD PRIMARY KEY($field_name) $value ";
                if ($key == "UNIQUE")
                    if ($value)
                        $field .= ", ADD UNIQUE($field_name); ";
//                    else
//                        $field .= ", DROP UNIQUE($field_name); ";
            }
        }else{

            if($properties[MAX_LENGTH])
                $field .= "   (5) unsigned Not null ";
            foreach ($properties as $key => $value) {
                if ($key == "TO")
                    if ($value)
                        $field .= " ;ALTER TABLE  $table_name ADD INDEX (  $field_name ) ;
                         ALTER TABLE  $table_name ADD FOREIGN KEY (  $field_name )
                         REFERENCES ".$value."(id)  ON DELETE CASCADE ON UPDATE CASCADE";
            }
        }

        return $field;
    }

    /**
     * Возвращает значения поля при обращению к объекту
     * @return string
     */
    function __toString()
    {
        return (string)$this->value;
    }

}


/**
 * Предстваляет собой объект целого поля
 * Class IntegerField
 */
class IntegerField extends Field
{

    public function get_type()
    {
        return "INT";
    }
}


/*
 * Предстваляет собой объект уникально поля
 */
class AutoField extends IntegerField
{

    function __construct($properties = array())
    {
        parent::__construct();
        $this->set_properties($properties);
        $this->properties['AUTO_INCREMENT'] = true;
        $this->properties['PRIMARY KEY'] = true;
    }
}


/*
 * Предстваляет собой объект строкого поля
 */
class CharField extends Field
{

    public function get_type()
    {
        return "VARCHAR";
    }

}


class PasswordField extends CharField
{
    public function set_password($value){
        $this->value= md5($value);
    }

    public function hash_password($password){
        /*
         * Проверяет пароли по хэш сумме, если они равны тогда вернется true
         */
        if($this->value){
            if($this->value == md5($password))
                return true;
            return false;
        }
        throw new Exception('Значения поля пустое!');
    }
}

/*
 * Предстваляет собой объект текстового поля
 */
class TextField extends Field
{

    function __construct($properties = array())
    {
        parent::__construct();
        $this->set_properties($properties);
        unset($this->properties['MAX_LENGTH']);
    }

    public function get_type()
    {
        return "TEXT";
    }

}


class FloatField extends Field
{

    function __construct($properties = array())
    {
        parent::__construct();
        $this->set_properties($properties);
        unset($this->properties['MAX_LENGTH']);
    }

    public function get_type()
    {
        return "FLOAT";
    }

}


/*
 * Предстваляет собой объект дата поля
 */
class DateField extends TextField
{

    function __construct($properties = array())
    {
        parent::__construct();
        $this->set_properties($properties);
        unset($this->properties['MAX_LENGTH']);
        $this->value = date("y.m.d");
    }

    public function get_type()
    {
        return "DATE";
    }


}


class DateTimeField extends TextField
{

    function __construct($properties = array())
    {
        parent::__construct();
        $this->set_properties($properties);
        unset($this->properties['MAX_LENGTH']);
        $this->value = date('y-m-d H:i:s');
    }

    public function get_type()
    {
        return "DATETIME";
    }

}

class TimeField extends TextField
{

    function __construct($properties = array())
    {
        parent::__construct();
        $this->set_properties($properties);
        unset($this->properties['MAX_LENGTH']);
    }

    public function get_type()
    {
        return "TIME";
    }

}


class ForeignKey extends IntegerField{

    function __construct($properties = array())
    {
        parent::__construct();
        $this->properties["TO"] = null; # Таблица на которую ссылается поле
        $this->properties["RELATION"] = true;
        $this->set_properties($properties);
    }

}