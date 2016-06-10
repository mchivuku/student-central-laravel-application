<?php
/**
 * Created by PhpStorm.
 * User: mchivuku
 * Date: 3/12/16
 * Time: 1:02 PM
 */


namespace StudentCentralCourseBrowser\Jobs;

/**
* Class BackUpCourseDB
* @package App\CronJobs
*/
class BackupDB extends Job{

    protected $tables = [['from'=>'class','to'=>'class_bk'],
        ['from'=>'class_association','to'=>'class_association_bk'],
        ['from'=>'class_attribute','to'=>'class_attribute_bk'],
        ['from'=>'class_combined_section','to'=>'class_combined_section_bk'],
        ['from'=>'class_notes','to'=>'class_notes_bk'],
        ['from'=>'crosslisted_course','to'=>'crosslisted_course_bk'],
        ['from'=>'requirement_group','to'=>'requirement_group_bk'],
        ['from'=>'reservation_capacity','to'=>'reservation_capacity_bk']
    ];

    public function __construct()
    {
        parent::__construct("BackUpCourseDB");
    }

    protected function run()
    {

        // delete the old backup
        array_walk($this->tables,array($this,'truncate_tables'));
        //copy
        array_walk($this->tables,array($this,'copy_data'));


    }

    protected function truncate_tables($item){

        \DB::connection("student_central_db")->table($item['to'])->truncate();
    }

    protected function copy_data($item){

        //INSERT INTO TABLE2 SELECT * FROM TABLE1
        $sql = sprintf("Insert into %s select * from %s",$item['to'],$item['from']);

        \DB::connection("student_central_db")->transaction(function()use($sql){
            \DB::connection("student_central_db")->getPdo()->exec( $sql );
        });


      }
}
