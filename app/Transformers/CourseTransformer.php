<?php
/**
 * Created by
 * User: IU Communications
 * Date: 6/9/16
 */

namespace StudentCentralApp\Transformers;

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
    public function transform($course)
    {

        //** Set Course attributes, loop through
        // individual classes to construct classes array */
        $classes="";

        try {

            if(isset($course['class_assoc'])){
                $classes = collect($course['class_assoc'])
                    ->map(function ($associated_section) {
                        return collect($associated_section)->map(function ($class) {
                            return $this->classTransformer->transform(($class));
                        });
                    });
            }

            return [
                'description_line' => isset($course['crs_desc_line']) ?
                    $course['crs_desc_line'] : "",
                'subject_department_short_desc' => isset($course['crs_subj_dept_cd'])?$course['crs_subj_dept_cd']:"",
                'subject_department_long_desc' => isset($course['crs_subj_line'])?$course['crs_subj_line']:"",
                'component_short_desc' => isset($course['crs_cmpnt_cd'])?$course['crs_cmpnt_cd']:"",
                'component_long_desc' => isset($course['crs_cmpnt_line'])?$course['crs_cmpnt_line']:"",
                'associated_classes' =>$classes

            ];

        } catch (\Exception $ex) {
            var_dump($ex->getTraceAsString());
        }

    }

}