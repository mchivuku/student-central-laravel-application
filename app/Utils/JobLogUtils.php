<?php

namespace StudentCentralApp\Utils;
/**
 * Created by
 * User: IU Communications
 * Date: 5/16/16
 */
class JobLogUtils
{


    /**
     * @param      $job_name
     * @param      $event
     * @param      $message
     * @param null $ex
     * @return array
     */
    static function createLogInfo($job_name, $event, $message = "", $ex = null)
    {

        if (isset($ex)) {
            $info = ['name' => $job_name,
                'event' => $event,
                'message' => $ex->getMessage(),'log_type'=>'job'];
            return $info;
        }

        $info = ['name' => $job_name, 'event' => $event, 'message' => $message,'log_type'=>'job'];

        return $info;

    }

}