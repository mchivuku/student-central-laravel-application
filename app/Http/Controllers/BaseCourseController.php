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

    protected $days = ["M"=>"Mon",
        "T"=>"Tue","W"=>"Wed","R"=>"Thurs",
        "F"=>"Fri"];

    protected $friday = ["F","R"];


    //('P','OA','OI','HY')
    protected $instructionModes = ['P', 'OA', 'OI', 'HY'];

    protected $course_numbers = ["" => "Course number",
        "100-199" => "100-199",
        "200-299" => "200-299",
        "300-399" => "300-399",
        "400-499" => "400-499",
        "500-599" => "500-599",
        "600-699" => "600-699",
        "700-above" => "700-above"];

    protected $creditHrs = ["" => "Credit hrs",
        1 => 1, 2 => 2, 3 => 3,
        4 => 4, 5 => 5, 6 => 6, 7 => 7];

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
        $instructionModes = Models\ClassTable
            ::select("cls_instrc_mode_cd", "cls_instrc_mode_desc")
            ->whereIn('cls_instrc_mode_cd',
                $this->instructionModes)
            ->distinct()->get()->lists("cls_instrc_mode_desc",
                "cls_instrc_mode_cd")
            ->toArray();
        $instructionModes = array_merge(["" => "Instruction modes"], $instructionModes);

        return $instructionModes;
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