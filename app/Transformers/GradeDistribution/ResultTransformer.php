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
            "grade_b_plus"=>$dataRow->gradebp,//B+
            "grade_b"=>$dataRow->gradeb,//B
            "grade_b_minus"=>$dataRow->gradebm,//B-
            "grade_c_plus"=>$dataRow->gradecp,//C+
            "grade_c"=>$dataRow->gradec,//C
            "grade_c_minus"=>$dataRow->gradecm,//C-
            "grade_d_plus"=>$dataRow->gradedp,//D+
            "grade_d"=>$dataRow->graded, //grade - d
            "grade_f"=>$dataRow->gradef,//gradef
            "grade_p"=>$dataRow->gradep,
            "grade_s"=>$dataRow->grades,
            "grade_i"=>$dataRow->gradei,
            "grade_w"=>$dataRow->gradew,
            "grade_ny"=>$dataRow->gradeny,
            "grade_wx"=>$dataRow->gradewx,
            "grade_nc"=>$dataRow->gradenc,
            "grade_nr"=>$dataRow->gradenr,
            "grade_other"=>$dataRow->gradeother,
            "gpa_grades"=>$dataRow->gpa_grades,
            "total_grades"=>$dataRow->total_grades,
            "percent_majors"=>$dataRow->majors_pct,
            "avg_class_grade"=>$dataRow->avg_cls_grd,
            "avg_class_gpa"=>$dataRow->avg_std_gpa,
            "instructor_name"=>$dataRow->cls_instr_nm,
            "non_gpa_grades"=>($dataRow->total_grades - $dataRow->gpa_grades)

        ];

    }


}