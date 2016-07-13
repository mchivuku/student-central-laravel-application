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
use StudentCentralApp\Models\TermDepartment;
use StudentCentralApp\Transformers\GradeDistribution as Transformer;


class GradeDistributionApiController extends BaseApiController
{

    protected $aggregateRepts = [


        1 =>['text'=>"Distribution by Course Academic Group, Academic Org, and Class Number",
            "where"=>"REC_TYPE='CLS'",'orderBy'=>'ACAD_TERM_CD desc,
             ACAD_GRP_CD, ACAD_ORG_CD, CRS_SUBJ_CD,
             CRS_CATLG_NBR, CLS_NBR, CLS_INSTR_NM'],

        2=> ['text' => 'Distribution by Course Group, Academic Org,
                and Course', 'where' => "REC_TYPE='CRSE'",
            'orderBy' => "ACAD_TERM_CD desc, ACAD_GRP_CD,
            ACAD_ORG_CD, CRS_SUBJ_CD, CRS_CATLG_NBR"],

        3 => ['text' => 'Distribution by Course Group and Academic Org',
            'where' => "REC_TYPE='ORG'",
            'orderBy' => "ACAD_TERM_CD desc, ACAD_GRP_CD, ACAD_ORG_CD"],

        4 => ['text' => 'Distribution by Course Academic Group',
            'where' => "REC_TYPE='GRP'",
            'orderBy' => "ACAD_TERM_CD desc, ACAD_GRP_CD, ACAD_ORG_CD"],

        5 => ['text' => 'Distribution by Institution and Session',
            'where' => "REC_TYPE='SESN'",
            'orderBy' => "ACAD_TERM_CD desc, CLS_SESN_CD"

           ],

        6 => ["text" => "Distribution by Institution",
            'where' => "REC_TYPE='INST'",
            'orderBy' => "ACAD_TERM_CD desc"

        ]
    ];

    public function __construct(Manager $fractal)
    {
        parent::__construct($fractal);
        $this->perPage = 20;
    }


    /**
     * Main method that performs search for items based on the criteria,
     * search on - acad term, department, course subject, catalog number
     * class number, instructor
     *
     * Query used - SELECT ACAD_TERM_DESC, ACAD_TERM_CD, INST_CD, INST_DESC, CLS_SESN_CD, CLS_SESN_DESC, ACAD_GRP_CD, ACAD_GRP_DESC, ACAD_ORG_CD, ACAD_ORG_DESC, DEPT,
     * CRS_SUBJ_CD, CRS_CATLG_NBR, CLS_NBR, CRS_DESC, CRS_TOPIC, GRADEAP,GRADEA,GRADEAM,GRADEBP,GRADEB,GRADEBM,GRADECP,GRADEC,
     * GRADECM, GRADEDP,GRADED, GRADEDM, GRADEF, GRADEP, GRADES, GRADEI, GRADER, GRADEW, GRADENY, GRADEWX, GRADENC, GRADENR,
     * GRADEOTHER, GPA_GRADES, TOTAL_GRADES, MAJORS_CNT, MAJORS_PCT, AVG_CLS_GRD, AVG_STD_GPA, CLS_INSTR_NM, ROW_EFF_DT, REC_TYPE
     * FROM SR_GDR_BY_REPORT_T
     */
    public function search()
    {

        // Read inputs
        $reportType = Input::get("reportType");

        $acadTerms = Input::get('acadTerm');
        $dept = Input::get('dept');
        $course_subject = Input::get('courseSubject');
        $catalog_number = Input::get('catalogNbr');
        $class_number = Input::get('classNbr');
        $instructor = Input::get('instructor');
        $school = Input::get('school');
        $exclude_small_class_results = Input::get('excludeSmallClass');

        $query = \DB::connection("gradesdb")->table('SR_GDR_BY_REPORT_T')
            ->select(\DB::connection("gradesdb")->raw('ACAD_TERM_DESC, ACAD_TERM_CD, INST_CD, INST_DESC, CLS_SESN_CD,
                 CLS_SESN_DESC, ACAD_GRP_CD, ACAD_GRP_DESC, ACAD_ORG_CD, ACAD_ORG_DESC, DEPT,
    CRS_SUBJ_CD, CRS_CATLG_NBR, CLS_NBR, CRS_DESC, CRS_TOPIC, GRADEAP,GRADEA,GRADEAM,GRADEBP,GRADEB,GRADEBM,GRADECP,GRADEC,
    GRADECM, GRADEDP,GRADED, GRADEDM, GRADEF, GRADEP, GRADES, GRADEI, GRADER, GRADEW, GRADENY, GRADEWX, GRADENC, GRADENR,
    GRADEOTHER, GPA_GRADES, TOTAL_GRADES, MAJORS_CNT, MAJORS_PCT, AVG_CLS_GRD, AVG_STD_GPA, CLS_INSTR_NM, ROW_EFF_DT, REC_TYPE'));

        // aggregate reporting,(cls reporting)
        // Aggregate reports -> query are going - report data in the table
        // flat data -> selection
        // rec_type = cls, sesn, - institution - rec_type - ins;
        //dept - org, school - grp, course - crse,

        // default is cls reporting
        if (!isset($reportType) || $reportType=="0")
            $reportType=1;

        $query = $query->whereRaw($this->aggregateRepts[$reportType]['where'])
            ->whereRaw("ACAD_TERM_CD IS NOT NULL");

        /** If acad terms is selected */
        if (isset($acadTerms)) {
            if (is_array($acadTerms))
                $query = $query->whereIn("ACAD_TERM_CD", $acadTerms)
                    ->whereRaw("ACAD_TERM_CD IS NOT NULL");
            else if ($acadTerms != "")
                $query = $query->where("ACAD_TERM_CD", "=", $acadTerms)
                    ->whereRaw("ACAD_TERM_CD IS NOT NULL");
        }


        /** if department is selected */
        if (isset($dept) && $dept != "") {
            $query = $query->where("DEPT", $dept)->whereRaw("ACAD_ORG_CD is not null");
        }

        /** if course subject is selected */
        if (isset($course_subject) && $course_subject != "")
            $query = $query->where("CRS_SUBJ_CD", 'like',
                $course_subject);

        /** if catalog number is selected */
        if (isset($catalog_number) && $catalog_number != "")
            $query = $query->where("CRS_CATLG_NBR", 'like', $catalog_number);

        /** $class_number */
        if (isset($class_number))
            $query = $query->where('cls_nbr', 'like', $class_number);

        /** instructor name is set */
        if (isset($instructor) && $instructor != "")
            $query = $query->where("(CLS_INSTR_NM)", "like", "%$instructor%")
                ->whereRaw("CLS_INSTR_NM IS NOT NULL");

        if (isset($exclude_small_class_results) && $exclude_small_class_results == "1")
            $query = $query->where("GRADEOTHER", 'not like', "SMALL");


        if(isset($school) && $school!="")
            $query=$query->where("ACAD_GRP_CD","=",$school);
        /** @var Order by - based on the report type $query */
        if (!isset($reportType)) {
            $query = $query->orderByRaw("ACAD_TERM_CD desc, ACAD_ORG_CD, CRS_SUBJ_CD,
        CRS_CATLG_NBR, CLS_NBR, CLS_INSTR_NM");
        } else {
            $query = $query
                ->orderByRaw($this->aggregateRepts[$reportType]['orderBy']);
        }

        $result = $query->paginate($this->perPage);

        $resource = new Collection($result, new Transformer\ResultTransformer);
        $resource->setPaginator(new \League\Fractal\Pagination\IlluminatePaginatorAdapter($result));

        return $this->fractal->createData($resource)->toJson();

    }

    /**
     * Function to return acadterms
     */
    public function acadTerms()
    {

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
    public function departments()
    {

        /*   $departments = \DB::connection("gradesdb")
                   ->select("SELECT DISTINCT DEPT,acad_org_desc FROM SR_GDR_BY_REPORT_T where ACAD_ORG_CD is not NULL
                             and ACAD_ORG_DESC is not null and dept is not null
                             ORDER BY ACAD_ORG_DESC");
   */
        /** @var Departments $departments */
        $departments = TermDepartment::orderBy('crs_subj_dept_cd')
            ->select("crs_subj_dept_cd", "crs_subj_desc")->groupBy("crs_subj_dept_cd", "crs_subj_desc")->get();


        return $this->fractal
            ->createData(new Collection($departments, function ($dept) {
                return [
                    'dept' => $dept->crs_subj_dept_cd,
                    'description' => $dept->crs_subj_desc
                ];
            }))
            ->toJson();

    }

    /**
     * Report Types -  Aggregate report to construct
     */
    public function reportTypes()
    {

        $data = ['data' => collect($this->aggregateRepts)
                ->map(function($item){
                    return $item['text'];
                })];
        return json_encode($data);
    }


    /**
     * Schools/Groups
     */
    public function schools()
    {

        $schools = \DB::connection("gradesdb")
            ->select("SELECT DISTINCT ACAD_GRP_CD, ACAD_GRP_DESC
                    FROM SR_GDR_BY_REPORT_T where ACAD_ORG_CD is not NULL
                          and ACAD_GRP_DESC is not null and ACAD_GRP_CD is not null
                          ORDER BY ACAD_GRP_CD");

        return $this->fractal
            ->createData(new Collection($schools, function ($school) {
                return [
                    'school' => $school->acad_grp_cd,
                    'description' => $school->acad_grp_desc
                ];
            }))
            ->toJson();
    }


}