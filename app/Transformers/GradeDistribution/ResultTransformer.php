<?php
/**
 * Created by
 * User: IU Communications
 * Date: 6/22/16
 */

namespace StudentCentralApp\Transformers\GradeDistribution;

use League\Fractal\TransformerAbstract;

/** Fractal transformer to transform data into json result */
class ResultTransformer extends TransformerAbstract
{

    public function transform($dataRow)
    {

        $calculate_percent = function($array,$gradetotal){
            if($gradetotal==0) return 0;

            $value = (array_sum($array)/$gradetotal)*100;

            $x = $value==0?0:(is_int($value)?
               $value."%": sprintf("%.2f%%",$value));

            return $x;
        };

        return [
            "acad_term"=>$dataRow->acad_term_cd,
            "acad_term_desc"=>$dataRow->acad_term_desc,
            "class_session_code"=>$dataRow->cls_sesn_cd,
            "class_session_desc"=>$dataRow->cls_sesn_desc,
            "acad_grp"=>$dataRow->acad_grp_cd,
            "acad_grp_desc"=>$dataRow->acad_grp_desc,
            "dept"=>$dataRow->dept,
            "dept_desc"=>$dataRow->acad_org_desc,
            "course_subj"=>$dataRow->crs_subj_cd,
            "course_catlg_nbr"=>$dataRow->crs_catlg_nbr,
            "cls_nbr"=>$dataRow->cls_nbr,
            "course_desc"=>$dataRow->crs_desc,
            "course_topic"=>$dataRow->crs_topic,
            "grade_a_plus"=>$dataRow->gradeap,//A+
            "grade_a"=>$dataRow->gradea,//A
            "grade_a_minus"=>$dataRow->gradeam,//A-
            "total_a"=>$dataRow->gradeap+$dataRow->gradea+$dataRow->gradeam,
            "grade_b_plus"=>$dataRow->gradebp,//B+
            "grade_b"=>$dataRow->gradeb,//B
            "grade_b_minus"=>$dataRow->gradebm,//B-
            "total_b"=>$dataRow->gradebp+$dataRow->gradeb
                +$dataRow->gradebm,

            "grade_c_plus"=>$dataRow->gradecp,//C+
            "grade_c"=>$dataRow->gradec,//C
            "grade_c_minus"=>$dataRow->gradecm,//C-
            "total_c"=>$dataRow->gradecp+$dataRow->gradec
                +$dataRow->gradecm,

            "grade_d_plus"=>$dataRow->gradedp,//D+
            "grade_d"=>$dataRow->graded, //grade - d
            "grade_d_minus"=>$dataRow->gradedm, //grade - d
            "total_d"=>$dataRow->gradedp+$dataRow->graded
                +$dataRow->gradedm,
            "grade_f"=>$dataRow->gradef,//gradef
            "grade_p"=>$dataRow->gradep,
            "grade_s"=>$dataRow->grades,
            "grade_i"=>$dataRow->gradei,
            "grade_w"=>$dataRow->gradew,
            "grade_r"=>$dataRow->grader,
            "grade_ny"=>$dataRow->gradeny,
            "grade_wx"=>$dataRow->gradewx,
            "grade_nc"=>$dataRow->gradenc,
            "grade_nr"=>$dataRow->gradenr,
            "grade_other"=>$dataRow->gradeother,
            "gpa_grades"=>$dataRow->gpa_grades,
            "total_grades"=>$dataRow->total_grades,
            "percent_majors"=>is_numeric($dataRow->majors_pct)
                ?$dataRow->majors_pct."%"
                :sprintf("%.2f%%", $dataRow->majors_pct),
            "avg_class_grade"=>$dataRow->avg_cls_grd,
            "avg_class_gpa"=>$dataRow->avg_std_gpa,
            "instructor_name"=>$dataRow->cls_instr_nm,
            "non_gpa_grades"=>
                ($dataRow->total_grades - $dataRow->gpa_grades),


            "a_percent"=>$calculate_percent([$dataRow->gradeap,$dataRow->gradea,
                $dataRow->gradeam],$dataRow->gpa_grades),

            "b_percent"=>$calculate_percent([$dataRow->gradebp,$dataRow->gradeb,
                $dataRow->gradebm],$dataRow->gpa_grades),

            "c_percent"=>$calculate_percent([$dataRow->gradecp,$dataRow->gradec,
                $dataRow->gradecm],$dataRow->gpa_grades),
            "d_percent"=>$calculate_percent([$dataRow->gradedp,$dataRow->graded],$dataRow->gpa_grades)


        ];

    }


}