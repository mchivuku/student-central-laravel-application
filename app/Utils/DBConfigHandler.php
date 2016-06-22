<?php
/**
 * Created by
 * User: IU Communications
 * Date: 5/17/16
 */

namespace StudentCentralApp\Utils;

/**
 * Class DBConfigHandler
 * File handles connections
 * @package StudentCentralCourseBrowser\Utils
 */
class DBConfigHandler
{

    public static $connections = [];
    public static $oracle_connection_keys=["DSSProd","registrar_grade_distribution_db","registrar_contact_form_db"];

    /**
     * Function parses DB config file
     * @return array
     */
    public static function parseConfig(){

        $config = env('dbconfig','.dbconfig');

        if(file_exists('./'.$config)) $ini = parse_ini_file('./'.$config, true);
        else if(file_exists(getenv('HOME').'/'.$config))
            $ini = parse_ini_file(getenv('HOME').'/'.$config, true);

        return $ini;

    }

    /***
     * Get all connections from the dbconfig file.
     * @return array
     */
    public static function connections(){

        // get Oracle seperately - with extra parameters
        $ini = array_filter(
            self::parseConfig(),
            function ($key){
                return !in_array($key,self::$oracle_connection_keys);
            },
            ARRAY_FILTER_USE_KEY
        );;

        $connections = array_map(function($item){

            return   ['host'=>$item['host'],
                'port'=>$item['port'],
                'database'=>$item['db'],
                'username'=>$item['user'],
                'password'=>$item['password'],
                'driver'=>'mysql',
                'collation'=>'utf8_unicode_ci',
                'charset'=>'utf8'];

        },$ini);

        $oracle_conn = ['oracle'=>self::getDSSProdConnection(),
            'gradesdb'=>self::getRegistrarDBConnection("registrar_grade_distribution_db"),
            "contactformdb"=>self::getRegistrarDBConnection("registrar_contact_form_db")];

        return array_merge($connections,$oracle_conn);

    }


    public static function getDSSProdConnection(){

        $ini = self::parseConfig();

        return [
            'driver'    => 'oci8',
            'tns'       => '',
            'host'      => $ini['DSSProd']['host'],
            'port'      => $ini['DSSProd']['port'],
            'username'  => $ini['DSSProd']['user'],
            'database'  => $ini['DSSProd']['db'],
            'password'  => $ini['DSSProd']['password'],
            'charset'   => 'WE8ISO8859P1',
            'prefix'    => '',
            'quoting'   => false,
        ];
    }

    public static function getRegistrarDBConnection($key){
        $ini = self::parseConfig();
        return ["driver"=>"oracle",
            "host"=>"sasregdt01.uits.iupui.edu","port"=>1521,"tns"=>"",
            "username"=>$ini[$key]['user'],
            "database"=>$ini[$key]['db'],
            "service_name"=>"oem1tst",
            "password"=>$ini[$key]['password'],
            "charset"=>"WE8ISO8859P1","prefix"=>"","quoting"=>""];


    }
}