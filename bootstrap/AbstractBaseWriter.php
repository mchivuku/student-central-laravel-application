<?php

/**
 * Created by
 * User: IU Communications
 * Date: 5/16/16
 */

namespace StudentCentralCourseBrowser\Bootstrap;

use Monolog\Handler\AbstractProcessingHandler;
use Monolog\Logger as Monolog;

/**
 * Class AbstractBaseWriter - custom log handler to log job status into the job_log table
 * Log exceptions into exception_log table.
 * @package StudentCentralCourseBrowser\Bootstrap
 */
class AbstractBaseWriter extends AbstractProcessingHandler
{

    private $pdo;
    public function __construct($level,$bubble,$pdo)
    {

        $this->pdo=$pdo;

        parent::__construct($level,$bubble);

    }

    /**
     * @param mixed $pdo
     * @return AbstractBaseWriter
     */
    public function setPdo($pdo)
    {
        $this->pdo = $pdo;
        return $this;
    }

    /**
     * @param array $record
     */
    protected function write(array $record)
    {

        // check the context - if the context contains BatchJob - save it to log table
        // error log errors in exception table
        $context = $record['context'];

        if(isset($context['log_type']))
        {

            $writer =  new JobLogWriter($this->pdo,$this->level);
        }else{
            $writer =  new ExceptionLogWriter($this->pdo,$this->level);
        }

        // log - data
        $writer->write($record);
        return;

    }

}