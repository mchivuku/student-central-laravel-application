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

    protected $dbextensionsObj;

    /** Init variables from the InitVars procedure */
    protected $yesterday,$just_printed, $special_sess_notes,$acad_grp_line,
              $cls_sesn_curr_sess, $cls_sesn_curr_sess_keep, $cls_sesn_print_cntl,
              $crs_subj_line, $crs_attrib_clst_line,$crs_desc_line, $crs_cmpnt_line;

    // Transfer Indiana Initiative Logic
    protected $ti_inst = ['IUBLA'=>'0409','IUCOA'=>'0405','IUEAA'=>'0409',
                          'IUINA'=>'0403','IUKOA'=> '0408','IUNWA'=>'0400',
                          'IUSBA'=>'0399','IUSEA'=>'0406'];

    /**
     * @var int - choosing a small chunk size as the data set read has lot of columns
     */
    protected $chunk_size = 100;


    function getName(){
        return $this->_jobName;
    }

    /***
     * Job constructor.
     * @param $jobName
     * @param DatabaseExtensions $extensions
     */
    public function __construct($jobName)
    {
        $this->_jobName = $jobName;
        $this->dbextensionsObj=app('DatabaseExtensions');
    }

    /**
     * Execute method calls run method to perform logging for the job
     */
    public function execute(){

        try{

            /** Log message - job started */
             \Log::info('Log message',
              Utils\JobLogUtils::createLogInfo($this->_jobName,JobEvents::JOB_START));

            $this->run();

            /** Log message - job finished */
            \Log::info('Log message',
                Utils\JobLogUtils::createLogInfo($this->_jobName,JobEvents::JOB_FINISH));

        }catch(\Exception $ex){

            /** Log message - job failed */
             \Log::error('Log message',
                Utils\JobLogUtils::createLogInfo($this->_jobName,JobEvents::JOB_FAIL,
                   $ex->getMessage(),$ex));
        }
    }

    protected  abstract function run();

    /**
     * Method to return acad terms for the institution.
     * @return mixed
     */
    protected function getAcadTerms(){
        return config('app.acadTerms');
    }

    /**
     * Method to return institution_cd value
     * @return string
     */
    protected function getInstitutionCD(){
        return 'IUBLA';
    }

}
