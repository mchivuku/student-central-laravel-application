<?php
/**
 * Created by
 * User: IU Communications
 * Date: 6/20/16
 */

namespace StudentCentralApp\Http\Controllers;

use Illuminate\Support\Facades\Input as Input;
use League\Fractal\Manager;
use League\Fractal\Resource\Collection;
use StudentCentralApp\Transformers\GradeDistribution as Transformer;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;


class GradeDistributionApiController extends BaseApiController
{


    public function __construct(Manager $fractal)
    {
        parent::__construct($fractal);
        $this->perPage=20;
    }


    /**
     * Main method that performs search for items based on the criteria,
     * search on - acad term, department, course subject, catalog number
     * class number, instructor
     *
     * Query used - SELECT ACAD_TERM_DESC, ACAD_TERM_CD, INST_CD, INST_DESC, CLS_SESN_CD, CLS_SESN_DESC, ACAD_GRP_CD, ACAD_GRP_DESC, ACAD_ORG_CD, ACAD_ORG_DESC, DEPT,
    CRS_SUBJ_CD, CRS_CATLG_NBR, CLS_NBR, CRS_DESC, CRS_TOPIC, GRADEAP,GRADEA,GRADEAM,GRADEBP,GRADEB,GRADEBM,GRADECP,GRADEC,
    GRADECM, GRADEDP,GRADED, GRADEDM, GRADEF, GRADEP, GRADES, GRADEI, GRADER, GRADEW, GRADENY, GRADEWX, GRADENC, GRADENR,
    GRADEOTHER, GPA_GRADES, TOTAL_GRADES, MAJORS_CNT, MAJORS_PCT, AVG_CLS_GRD, AVG_STD_GPA, CLS_INSTR_NM, ROW_EFF_DT, REC_TYPE
    FROM SR_GDR_BY_REPORT_T
     */
    public function search(){

        // Read inputs
        $acadTerms = Input::get('acadTerm');
        $dept = Input::get('dept');
        $course_subject = Input::get('courseSubject');
        $catalog_number = Input::get('catalogNbr');
        $class_number = Input::get('classNbr');
        $instructor  = Input::get('instructor');
        $exclude_small_class_results = Input::get('excludeSmallClass');

        $query = \DB::connection("gradesdb")->table('SR_GDR_BY_REPORT_T')
                ->select(\DB::connection("gradesdb")->raw('ACAD_TERM_DESC, ACAD_TERM_CD, INST_CD, INST_DESC, CLS_SESN_CD, CLS_SESN_DESC, ACAD_GRP_CD, ACAD_GRP_DESC, ACAD_ORG_CD, ACAD_ORG_DESC, DEPT,
    CRS_SUBJ_CD, CRS_CATLG_NBR, CLS_NBR, CRS_DESC, CRS_TOPIC, GRADEAP,GRADEA,GRADEAM,GRADEBP,GRADEB,GRADEBM,GRADECP,GRADEC,
    GRADECM, GRADEDP,GRADED, GRADEDM, GRADEF, GRADEP, GRADES, GRADEI, GRADER, GRADEW, GRADENY, GRADEWX, GRADENC, GRADENR,
    GRADEOTHER, GPA_GRADES, TOTAL_GRADES, MAJORS_CNT, MAJORS_PCT, AVG_CLS_GRD, AVG_STD_GPA, CLS_INSTR_NM, ROW_EFF_DT, REC_TYPE'));


        //TODO: check with Darren
        $query = $query->where("REC_TYPE","=",'CLS')->whereRaw("ACAD_TERM_CD IS NOT NULL");


        /** If acad terms is selected */
        if(isset($acadTerms)){
            $query = $query->whereIn("ACAD_TERM_CD",$acadTerms)->whereRaw("ACAD_TERM_CD IS NOT NULL");
        }


        /** if department is selected */
        if(isset($dept)){
            $query = $query->where("DEPT",$dept)->whereRaw("ACAD_ORG_CD is not null");
        }

        /** if course subject is selected */
        if(isset($course_subject))
            $query = $query->where("CRS_SUBJ_CD",'=',$course_subject);

        /** if catalog number is selected */
        if(isset($catalog_number))
            $query = $query->where("CRS_CATLG_NBR",'=',$catalog_number);

        /** $class_number */
        if(isset($class_number))
            $query = $query->where('cls_nbr','=',$class_number);

        /** instructor name is set */
        if(isset($instructor))
            $query = $query->where("upper(CLS_INSTR_NM)","like",$instructor)->whereRaw("CLS_INSTR_NM IS NOT NULL");

        if(isset($exclude_small_class_results))
            $query=$query->where("GRADEOTHER",'like',"SMALL");

        $query = $query->orderByRaw("ACAD_TERM_CD desc, ACAD_ORG_CD, CRS_SUBJ_CD,
        CRS_CATLG_NBR, CLS_NBR, CLS_INSTR_NM");

        $result = $query->paginate($this->perPage);

        $resource = new Collection($result, new Transformer\ResultTransformer);
        $resource->setPaginator(new \League\Fractal\Pagination\IlluminatePaginatorAdapter($result));

        return $this->fractal->createData($resource)->toJson();


    }

    /**
     * Function to return acadterms
     */
    public function acadTerms(){

         $acad_terms = \DB::connection("gradesdb")
             ->select("SELECT DISTINCT ACAD_TERM_CD, ACAD_TERM_DESC FROM SR_GDR_BY_REPORT_T where ACAD_TERM_DESC
                      is not NULL ORDER BY ACAD_TERM_CD DESC");

        return $this->fractal
            ->createData(new Collection($acad_terms, new Transformer\AcadTermTransformer))
            ->toJson();


    }

    /**
     * Function to return departments
     */
    public function departments(){

        $departments = \DB::connection("gradesdb")
                ->select("SELECT DISTINCT DEPT,ACAD_ORG_DESC FROM SR_GDR_BY_REPORT_T where ACAD_ORG_CD is not NULL
                          and ACAD_ORG_DESC is not null and dept is not null
                          ORDER BY ACAD_ORG_DESC");

        return $this->fractal
            ->createData(new Collection($departments, new Transformer\DepartmentTransformer))
            ->toJson();

    }


}