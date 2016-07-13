<?php
/**
 * Created by
 * User: IU Communications
 * Date: 6/25/16
 */

namespace StudentCentralApp\Http\Controllers;

use StudentCentralApp\Models as Models;


class BaseCourseController extends Controller
{

    protected $builder;
    protected $perPage = 10;
    protected $genEd = ['0GENEDEC',
        '0GENEDMM', '0GENEDAH', '0GENEDSH', '0GENEDNM', '0GENEDWL', '0GENEDWC'];

    protected $caseRequirements = [
        'BLIW','COLL030AH','COLL080GC','COLL070DS','COLL050NM','COLL040SH','BLCAPP'
    ];
    protected $days = ["M"=>"Mon",
        "T"=>"Tue","W"=>"Wed","R"=>"Thurs",
        "F"=>"Fri"];

    protected $friday = ["F","R"];


    //('P','OA','OI','HY')
    protected $instructionModes = ['P'=>"In Person","OA"=>"100% Online",
        "OI"=>"76-99% Online Interactive","HY"=>"Hybrid-On Campus & Online"
        ];

    protected $course_numbers = ["" => "Course number",
        "100-199" => "100-199",
        "200-299" => "200-299",
        "300-399" => "300-399",
        "400-499" => "400-499",
        "500-599" => "500-599",
        "600-699" => "600-699",
        "700-above" => "700+"];

    protected $creditHrs = ["" => "Credit hrs",
        1 => 1, 2 => 2, 3 => 3,
        4 => 4, 5 => 5, 6 => 6, "7+" => "7+"];

    public function __construct()
    {

        $this->builder = new \IUCommFormBuilder($_SERVER['QUERY_STRING']);

    }

    /** Build Departments */
    protected function getDepartments($term = '')
    {

        /** @var Departments $departments */
        if ($term != "")
            $departments = Models\TermDepartment::acadTerm($term)->select(\DB::connection("coursebrowser")
                ->raw("
                CONCAT(crs_subj_desc,\" (\",crs_subj_dept_cd,\")\") as crs_subj_desc,crs_subj_dept_cd as crs_subj_dept_cd "))
                ->orderBy('crs_subj_dept_cd')
                ->get()->pluck("crs_subj_desc","crs_subj_dept_cd")
                ->toArray();
        else
            $departments = Models\TermDepartment::
            select(\DB::connection("coursebrowser")
                ->raw("
                CONCAT(crs_subj_desc,\" (\",crs_subj_dept_cd,\")\") as crs_subj_desc,crs_subj_dept_cd as crs_subj_dept_cd "))->orderBy('crs_subj_dept_cd')
                ->distinct()->pluck("crs_subj_desc","crs_subj_dept_cd")
                ->toArray();

        $departments = array_merge(["" => "Departments"], $departments);
        return $departments;

    }

    /**
     * Start Index.
     * @param $page
     * @return int
     */
    protected function get_start_index($page)
    {
        if (!isset($page))
            $page = 1;
        $start_index = ($page - 1) * $this->perPage;
        return $start_index;
    }

    protected function getSessions($term)
    {

        $sessions = Models\ClassTable::where('acad_term_cd', '=', $term)
            ->get()->lists("cls_sesn_desc",
                "cls_sesn_cd")
            ->toArray();

        $sessions =  ["" => "Class session"] + $sessions;
        return $sessions;
    }

    protected function getInstructionModes(){

        $instructionModes = ["" => "Instruction modes"] + $this->instructionModes;

        return $instructionModes;
    }


    /***
     * College case requirements
     * @return array
     */
    protected function getCASERequirements(){
        $case_requirements = Models\ClassAttribute
            ::select("crs_attrib_val_cd", "crs_attrib_val_desc")
            ->whereIn('crs_attrib_val_cd',
                $this->caseRequirements)
            ->distinct()->get()->lists("crs_attrib_val_desc",
                "crs_attrib_val_cd")
            ->toArray();
        $case_requirements = ["" => "CASE Requirement"] + $case_requirements;
        return $case_requirements;

    }


    protected function getTerms(){
        $terms = Models\TermDescription::whereIn('term', config('app.acadTerms'))
            ->get()->lists(
                "description","term")->toArray();

        $x[""]="Term";
        foreach($terms as $k=>$v){
            $x[$k]=$v;
        }
        return $terms;
    }
}