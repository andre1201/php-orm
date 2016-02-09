<?php
/**
 * Created by PhpStorm.
 * User: Дрон
 * Date: 05.02.16
 * Time: 23:45
 */

class DataBase
{

    const HOST = "localhost";
    const USER = "root"; # пользователь
    const PASSWORD = ""; # пароль
    const DB_NAME = "test"; # имя базы данных
    const DB = "mysql"; # тип базы


    public static function  connect()

    {
        $connect = self::DB . ":dbname=" . self::DB_NAME . ";host=" . self::HOST;
        try {
            $db = new PDO($connect, self::USER, self::PASSWORD);
        } catch (PDOException $e) {
            print($e->getMessage());
        }

        return $db;
    }


    /**
     * @param $query
     * @return bool|PDOStatement
     */
    public function SQL($query)
    {
        $db = DataBase::connect();
        $result = $db->query($query);
        if($result)
            return $result;
        return false;
    }

    /**
     * @param $query
     * @return int
     */
    public function SQL_EXEC($query)
    {

        $db = DataBase::connect();
        $result = $db->exec($query);
        if ($result === true)
            return $result;
        return $result;
    }
} 