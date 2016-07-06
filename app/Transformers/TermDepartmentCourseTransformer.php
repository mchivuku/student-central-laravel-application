<?php
/**
 * Created by
 * User: IU Communications
 * Date: 6/25/16
 */


namespace StudentCentralApp\Transformers;

use League\Fractal\TransformerAbstract;

class TermDepartmentCourseTransformer extends TransformerAbstract
{

    protected $courseTransformer ;
    public function __construct()
    {

        $this->base_transformer = new BaseTransformer();
        $this->courseTransformer = new CourseTransformer();

    }

    /**
     * Course
     * @param $course
     */
    public function transform($dept)
    {

        return [
            "department"=>$dept['department'],
            "courses"=>collect($dept['courses'])->map(function($course) {
                return $this->courseTransformer->transform($course);
                    })
        ];


    }

}