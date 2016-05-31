<?php

/**
 * Created by
 * User: IU Communications
 * Date: 5/16/16
 */

namespace StudentCentralCourseBrowser\Bootstrap;

use Monolog\Handler\AbstractProcessingHandler;
use Monolog\Logger as Monolog;

class ExceptionLogWriter extends AbstractProcessingHandler
{
    /**
     * @var string to store pdo object
     */
    protected $pdo;


    /**
     * @var string to store pdo statement
     */
    protected $statement;

    /**
     * @var string the table to store the logs in
     */
    private $table = 'exception_log';

    public function __construct(
        \PDO $pdo = null,
        $level = Monolog::ERROR
    )
    {
        if (!is_null($pdo)) {
            $this->pdo = $pdo;
        }

        parent::__construct($level);

        $this->level = $level;
    }

    /**
     * Writes the record down to the log of the implementing handler
     *
     * @param  array $record
     * @return void
     */
    protected function write(array $record)
    {

        // if database is not initialized return
        if (is_null($this->pdo)) return;


        $context = $record['context'];

        // construct log
        if(isset($context) && is_array($context)){
            $log =
                array_merge(array(
                    'level' => $this->level,
                    'message' => $record['message'],
                ), $context);
        }else{
            $log =
                array(
                    'level' => $this->level,
                    'message' => $record['message'],
                );
        }



        $this->statement = $this->pdo->prepare(
            'INSERT INTO `' . $this->table . '` (query_string, level, message)
               VALUES (:query_string,  :level, :message)'
        );

        $this->statement->execute(['query_string' =>  \Request::path(),
            'level' => $log['level'],
            'message' => $log['message']]);

    }


}