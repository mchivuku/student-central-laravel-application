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

    /** @var array Static element should get appended */
    protected static $default_element =[""=> "&mdash;"];

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

    protected $course_numbers = ["All Course Numbers" => "All Course Numbers",
        "100-199" => "100-199",
        "200-299" => "200-299",
        "300-399" => "300-399",
        "400-499" => "400-499",
        "500-599" => "500-599",
        "600-699" => "600-699",
        "700-above" => "700+"];

    protected $creditHrs = ["All Credit hrs" => "All Credit hrs",
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

        $departments = self::$default_element + ["All Departments" => "All Departments"] + $departments;

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
    /** @var 4168 - Fall, 4162 - Spring, 4165 - Summer $sessions */
        $sessions = [
            4168 =>[
                "1"=>"Regular Academic Session",
                "B71"=>"Kelley First Seven Week",
                "8W1"=>"Eight Week - First",
                "8W2"=>"Eight Week - Second",
                "ACP"=>"Advance College Project",
                "NON"=>"Non Standard Session",
                "B72"=>"Kelley Second Seven Week",
                "INT"=>"Intersession",
                "6W1"=>"Six Week - First",
                "BA1"=>"Kelley Academy Week 1",
                "BA3"=>"Kelley Academy Week 3",
                "6W2"=>"Six Week - Second",
                "7W2"=>"Seven Week - Second",
                "7W1"=>"Seven Week - First"
            ],

            4165=>[
                "6W1"=>"Six Week - First",
                "4W2"=>"Four Week - Second",
                "8W2"=>"Eight Week - Second",
                "1"=>"Regular Academic Session",
                "6W2"=>"Six Week - Second",
                "8W1"=>"Eight Week - First",
                "NS2"=>"Non Standard Session 2",
                "NS1"=>"Non Standard Session 1",
                "4W1"=>"Four Week - First",
                "4W3"=>"Four Week - Third",
                "NON"=>"Non Standard Session"

            ],
            4162 =>[
                "ACP"=>"Advance College Project",
                "8W1"=>"Eight Week - First",
                "8W2"=>"Eight Week - Second",
                "INT"=>"Intersession",
                "BA2"=>"Kelley Academy Week 2",
                "B71"=>"Kelley First Seven Week",
                "B72"=>"Kelley Second Seven Week",
                "NON"=>"Non Standard Session",
                "1"=>"Regular Academic Session",
                "7W1"=>"Seven Week - First",
                "7W2"=>"Seven Week - Second"
            ]
        ];
        $sessions =  self::$default_element + ["All sessions"=>"All sessions"] + $sessions[$term];
        return $sessions;
    }

    protected function getInstructionModes(){

        $instructionModes = self::$default_element + ["All Instruction modes" => "All Instruction modes"] + $this->instructionModes;

        return $instructionModes;
    }


    /***
     * College case requirements
     * @return array
     */
    protected function getCASERequirements(){

        return self::$default_element + ["All CASE requirements"=>"All CASE requirements"] + [
            "BLIW"=>"COLL INTENSIVE WRITING SECTION",
            "COLL030AH"=>"COLL (CASE) A&H Breadth of Inq",
            "COLL080GC"=>"COLL (CASE) Global Civ & Cultr",
            "COLL070DS"=>"COLL (CASE) Diversity in U.S.",
            "COLL050NM"=>"COLL (CASE) N&M Breadth of Inq",
            "COLL040SH"=>"COLL (CASE) S&H Breadth of Inq",
            "BLCAPP"=>"COLL Critical Approach Reqrmnt"
        ];
    }

    protected function getGenEdRequirements(){

        return self::$default_element + ["All GenEd requirements"=>"All GenEd requirements"] + [
            "0GENEDAH"=>"IUB GenEd A&H credit",
            "0GENEDSH"=>"IUB GenEd S&H credit",
            "0GENEDWC"=>"IUB GenEd World Culture credit",
            "0GENEDWL"=>"IUB GenEd World Language class",
            "0GENEDMM"=>"IUB GenEd Mathematical Model",
            "0GENEDNM"=>"IUB GenEd N&M credit",
            "0GENEDEC"=>"IUB GenEd English Composition"
        ];
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

    protected function getCourseNumbers(){
        return self::$default_element + $this->course_numbers;
    }

    protected function getCreditHrs(){
        return self::$default_element + $this->creditHrs;
    }
}