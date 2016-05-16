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
 * Class JobLogWriter - write job log to database.
 * @package StudentCentralCourseBrowser\Bootstrap
 */
class JobLogWriter extends AbstractProcessingHandler
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
    private $table = 'job_log';


    public function __construct(
        \PDO $pdo = null,
        $level = Monolog::DEBUG
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
        $log =
            array_merge(array(
                'name' => $record['channel'],
                'event' => $record['level_name'],
                'message' => $record['message'],
            ), $context);

        $this->statement = $this->pdo->prepare(
            'INSERT INTO `' . $this->table . '` (name, event, message, timestamp)
               VALUES (:name,  :event, :message, :timestamp)'
        );

        $this->statement->execute(['name' => $log['name'],
            'event' => $log['event'],
            'message' => $log['message']]);

    }


}