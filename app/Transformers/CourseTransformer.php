<?php
/**
 * Created by
 * User: IU Communications
 * Date: 6/9/16
 */

namespace StudentCentralCourseBrowser\Transformers;

use League\Fractal\TransformerAbstract;

class CourseTransformer extends TransformerAbstract
{

    protected $classTransformer ;
    public function __construct($params = [])
    {
        $this->params = $params;


        $this->base_transformer = new BaseTransformer();
        $this->classTransformer = new ClassTransformer();

    }

    /**
     * Course
     * @param $course
     */
    public function transform($course){

        //** Set Course attributes, loop through
        // individual classes to construct classes array */

         $classes = collect($course['class_assoc'])
            ->map(function($associated_section){
            return collect($associated_section)->map(function($class){
                 return $this->classTransformer->transform(($class));
            });
        });


        return [
                'description_line'=>isset($course['crs_desc_line'])?$course['crs_desc_line']:"",
                'subject_department_short_desc'=> $course['crs_subj_dept_cd'],
                'subject_department_long_desc'=>$course['crs_subj_line'],
                'component_short_desc'=>$course['crs_cmpnt_cd'],
                'component_long_desc'=>$course['crs_cmpnt_line'],
                'associated_classes'=>
                    $classes
        ];

    }


}