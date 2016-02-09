<?php
/**
 * Created by PhpStorm.
 * User: Дрон
 * Date: 06.02.16
 * Time: 3:08
 */

class Migrations {

    public static function create_migrations($table_name, $fields){
        $migrations = fopen("migrations/migrations".$table_name.".json", "a");
        $json = array();

        $json['table_name'] = $table_name;
        $i = 0;
        foreach ($fields as $key => $value){
            $json["fields"][$i]['name'] = $key;
            $json["fields"][$i]['type'] = $value->get_type();
            $json["fields"][$i]['properties'] = $value->get_propeties();
            $i++;
        }
        fwrite($migrations, json_encode($json));
        fclose($migrations);
    }

} 