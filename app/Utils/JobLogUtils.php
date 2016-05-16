<?php

namespace StudentCentralCourseBrowser\Utils;
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
                'message' => $ex->getMessage()];
            return $info;
        }


        $info = ['name' => $job_name, 'event' => $event, 'message' => $message];

        return $info;

    }

}