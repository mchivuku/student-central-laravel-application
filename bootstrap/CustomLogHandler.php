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
class CustomLogHandler
{

    private $dbconfig_file = '.dbconfig';
    private $dbKey =  'student_central_db';


    /**
     * @return \PDO - if the connection was successful
     */
    public  function getConnectionObject(){

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