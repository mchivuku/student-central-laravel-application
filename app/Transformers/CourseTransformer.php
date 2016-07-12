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
         //individual classes to construct classes array */
        $classes="";


            if(isset($course['class_assoc'])){
                $classes = collect($course['class_assoc'])
                    ->map(function ($associated_section) {
                        return [
                            'min_credit_hrs'=>isset($associated_section['min_credit_hrs'])?$associated_section['min_credit_hrs']:"",
                            'max_credit_hrs'=>isset($associated_section['max_credit_hrs'])?
                                $associated_section['max_credit_hrs']:"",
                            'classes'=>
                            collect($associated_section['classes'])->map(function ($class) {
                            return $this->classTransformer->transform(($class));
                        })->toArray()];
                    })->toArray();
            }

            return [
                'description_line' => isset($course['crs_desc_line']) ?
                    $course['crs_desc_line'] : "",
                'course_subj_letter'=>isset($course['crs_subj_ltr_cd']) ?
                    $course['crs_subj_ltr_cd'] : "",
                'subject_department_short_desc' => isset($course['crs_subj_dept_cd'])?$course['crs_subj_dept_cd']:"",
                'subject_department_long_desc' => isset($course['crs_subj_line'])?$course['crs_subj_line']:"",
                'component_short_desc' => isset($course['crs_cmpnt_cd'])?$course['crs_cmpnt_cd']:"",
                'component_long_desc' => isset($course['crs_cmpnt_line'])?$course['crs_cmpnt_line']:"",

                'course_attributes' => isset($course["course_attributes"])?collect($course['course_attributes'])
                    ->map(function ($attribute) {
                        return ['attribute_code' => $attribute['crs_attrib_val_cd'],
                            'attribute_desc' => $attribute['crs_attrib_val_desc']];
                    })->toArray():"",
                'course_catalog_nbr'=>isset($course['crs_catlg_nbr'])?$course['crs_catlg_nbr']:"",
                'associated_classes' =>$classes,
                'transfer_indiana_initiative'=>
                    collect($course['transfer_indiana_initiative'])
                    ->unique()->map(function ($notes) {
                    return $notes;
                })->toArray(),

                'min_credit_hrs'=>min(collect($course['class_assoc'])->pluck('min_credit_hrs')->toArray()),
                'max_credit_hrs'=>max(collect($course['class_assoc'])->pluck('max_credit_hrs')->toArray())


            ];


    }

}