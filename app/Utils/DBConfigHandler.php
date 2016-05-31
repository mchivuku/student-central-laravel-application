<?php
/**
 * Created by
 * User: IU Communications
 * Date: 5/17/16
 */

namespace StudentCentralCourseBrowser\Utils;

/**
 * Class DBConfigHandler
 * File handles connections
 * @package StudentCentralCourseBrowser\Utils
 */
class DBConfigHandler
{

    public static $connections = [];

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
                return $key!='DSSProd';
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

        $oracle_connection = self::getOracleConnection();
        if(is_array($connections) && is_array($oracle_connection))
            return array_merge($connections,['oracle'=>self::getOracleConnection()]);

        return "";
    }


    public static function getOracleConnection(){

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

}