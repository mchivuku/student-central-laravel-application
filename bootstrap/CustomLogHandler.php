<?php

/**
 * Created by PhpStorm.
 * User: IU Communications
 * Date: 5/16/16
 * Time: 2:01 PM
 */
namespace StudentCentralCourseBrowser\Bootstrap;

use Monolog\Logger as Monolog;
use Illuminate\Log\Writer;
use Illuminate\Contracts\Foundation\Application;
use Monolog\Handler\StreamHandler;


/**
 * Class CustomLogHandler
 * Custom handler for logging
 * @package StudentCentralCourseBrowser\Bootstrap
 */
class CustomLogHandler extends \Illuminate\Foundation\Bootstrap\ConfigureLogging
{

    private $dbconfig_file = '.dbconfig';
    private $dbKey =  'student_central_db';

    /**
     * @param Application $app
     * @param Writer $log
     */
    protected function configureHandlers(Application $app, Writer $log)
    {


        $bubble = false;


        // stream handler
        // 1. Job log - call JobLogWriter
        // 2. Exception Log - call ExceptionLogWriter

        $infoStreamHandler = new StreamHandler( storage_path("/logs/laravel_info.log"),
            Monolog::INFO, $bubble);

        $warningStreamHandler = new StreamHandler( storage_path("/logs/laravel_warning.log"),
            Monolog::WARNING, $bubble);


        $jobExceptionHandler = new AbstractBaseWriter(Monolog::ERROR, $bubble,$this->getConnectionObject());

        // Get monolog instance and push handlers
        $monolog = $log->getMonolog();
        $monolog->pushHandler($infoStreamHandler);
        $monolog->pushHandler($warningStreamHandler);

        $monolog->pushHandler($jobExceptionHandler);

        // Daily Files - keep doing the same
        $log->useDailyFiles($app->storagePath().'/logs/daily.log');
    }

    /**
     * @return \PDO - if the connection was successful
     */
    private function getConnectionObject(){

        try{

            $config = $this->dbconfig_file;

            if(file_exists('./'.$config)) $ini = parse_ini_file('./'.$config, true);
            else if(file_exists(getenv('HOME').'/'.$config))
                $ini = parse_ini_file(getenv('HOME').'/'.$config, true);


            // Get DB credentials to build PDO object
            $db =   $ini[$this->dbKey]['db'];
            $dbhost = $ini[$this->dbKey]['host'];
            $dbport = $ini[$this->dbKey]['port'];

            $user = $ini[$this->dbKey]['user'];
            $password = $ini[$this->dbKey]['password'];

            $dsn = 'mysql:dbname='.$db.';host='.$dbhost.';port='.$dbport.';';

            $pdo= new \PDO($dsn, $user, $password);

            return $pdo;

        }catch(\PDOException $ex){
            die($ex->getMessage());
        }


    }
}