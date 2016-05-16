<?php

namespace StudentCentralCourseBrowser\Jobs;

use Illuminate\Bus\Queueable;
use StudentCentralCourseBrowser\Utils as Utils;

abstract class Job
{
    /*
    |--------------------------------------------------------------------------
    | Queueable Jobs
    |--------------------------------------------------------------------------
    |
    | This job base class provides a central location to place any logic that
    | is shared across all of your jobs. The trait included with the class
    | provides access to the "onQueue" and "delay" queue helper methods.
    |
    */

    use Queueable;

    private $_jobName;

    function getName(){
        return $this->_jobName;
    }

    public function __construct($jobName)
    {
        $this->_jobName = $jobName;
    }

    /**
     * Execute method calls run method to perform logging for the job
     */
    public function execute(){

        try{

            // Log job started.
            \Log::info('Log message',
                Utils\JobLogUtils::createLogInfo($this->_jobName,JobEvents::JOB_START));

            $this->run();

            // Log job finished
            \Log::info('Log message',
                Utils\LogUtils::createLogEventInfo($this->_jobName,JobEvents::JOB_FINISH));

        }catch(\Exception $ex){

            // Log job failed
            \Log::error('Log message',
                Utils\LogUtils::createLogEventInfo($this->_jobName,JobEvents::JOB_FAIL,
                    $ex->getMessage(),$ex));
        }
    }

    protected  abstract function run();

}
