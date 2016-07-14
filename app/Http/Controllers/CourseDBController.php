<?php
/**
 * Created by
 * User: IU Communications
 * Date: 6/25/16
 */

namespace StudentCentralApp\Http\Controllers;

use Illuminate\Http\Request;
use League\Fractal\Manager;
use StudentCentralApp\Models as Models;


class CourseDBController extends BaseCourseController
{

    protected $hosted_urls = [4168 => "/register/schedule-classes/index.html",
        4162 => "/register/schedule-classes/spring-2016.html",
        4165 => "/register/schedule-classes/summer-2016.html"
    ];

    public function __construct(Manager $fractal)
    {
        parent::__construct();
        $this->fractal = $fractal;
        $this->perPage = 20;
    }

    /**
     * Call function to build the dropdown for filtering data.
     */
    public function index($term, Request $request)
    {
        // filter
        $dept = $request->input("dept");
        $genEndAttr = $request->input('genEdReq');
        $instructionMode = $request->input('instrucMode');
        $courseNbr = $request->input("courseNbr");
        $creditHr = $request->input("creditHr");
        $session = $request->input("session");
        $days = $request->input("days");

        /** @var Build Markup $html */
        $html = $this->builder->open()
            ->attribute('action', $_ENV['HOME_PATH'] . $this->hosted_urls[$term])
            ->attribute('method', 'GET')
            ->addClass('filter')
            ->attribute('data-api', $_ENV['HOME_PATH'] . '/_php/laravel-app/public/courses/' . $term);

        $html .= $this->builder->formWrapperOpen()->addClass('thirds');

        // build genEd
        $requirements = $this->getGenEdRequirements();
        $departments = $this->getDepartments($term);
        $sessions = $this->getSessions($term);
        $case_requirements = $this->getCASERequirements();
        $creditHrs = $this->getCreditHrs();
        $courseNumbers = $this->getCourseNumbers();

        /** @var  $instructionModes */
        $instructionModes = $this->getInstructionModes();


        /** @var  $instructionModes */
        $instructionModes = $this->getInstructionModes();

        $html .= $this->builder->select("GenEd requirement", "genEdReq",
            $requirements,
            array('class' => "genEdReq"), isset($genEndAttr) ? $genEndAttr : "");

        $html .= $this->builder->select("CASE requirement", "CASEReq",
            $case_requirements,
            array('class' => "CASEReq"), isset($caseReq) ? $caseReq : "");

        $html .= $this->builder->select("Departments", "dept",
            $departments,
            array('class' => "dept"), isset($dept) ? $dept : "");


        $html .= $this->builder->select("Class session", "session",
            $sessions,
            array('class' => "session"), isset($session) ? $session : "");

        $html .= $this->builder->select("Instruction mode", "instrucMode",
            $instructionModes,
            array('class' => "instrucMode"), isset($instructionMode) ? $instructionMode : "");

        $html .= $this->builder->select("Course number", "courseNbr",
            $courseNumbers,
            array('class' => "courseNbr"),
            isset($courseNbr) ? $courseNbr : "");

        $html .= $this->builder->select("Credit hour", "creditHr",
            $creditHrs,
            array('class' => "creditHr"), isset($creditHr) ? $creditHr : "");


        $html .= $this->builder->formWrapperClose();

        $html .= "<div class='grid'>";
        $html .= $this->builder->checkboxes(array_map(function ($d) use (&$days) {
            $array = ['label' => $d, 'name' => 'days[]', 'value' => $d];
            if (isset($days) && in_array($d, $days))
                $array['attributes'] = ['checked' => 'checked'];
            return $array;
        }, $this->days))->header("Day of the week");
        $html .= "</div>";


        $html .= $this->builder->button('Go')->attribute('type', 'submit');
        $html .= $this->builder->close();

        if (isset($input) && count($input) == 0) {
            $html .= view("emptyresults")
                ->render();
        }

        /** @var Get Results - $search */
        $search = $this->search($term, $request);

        /** Selection Wrapper */
        $selectionWrapper = $this->builder->selectionWrapper($search['total']);

        if (isset($dept) && $dept != "")
            $selectionWrapper = $selectionWrapper->filterItem('Department',
                $departments[$dept], $dept);

        if (isset($genEndAttr) && $genEndAttr != "")
            $selectionWrapper = $selectionWrapper->filterItem('GenEd requirement',
                $requirements[$genEndAttr], $genEndAttr);


        if (isset($instructionMode) && $instructionMode != "")
            $selectionWrapper = $selectionWrapper->filterItem('Instruction mode',
                $instructionModes[$instructionMode], $instructionMode);

        if (isset($courseNbr) && $courseNbr != "")
            $selectionWrapper = $selectionWrapper->filterItem('Course number',
                "Course number: " . $this->course_numbers[$courseNbr], $courseNbr);

        if (isset($creditHr) && $creditHr != "")
            $selectionWrapper = $selectionWrapper->filterItem('Credit hour',
                "Credit hour: " . $creditHr, $creditHr);

        $html .= $selectionWrapper;

        /** Search - return all courses */

        $html .= $this->builder->resultsWrapper($search['view']);
        $html .= $this->builder->pagination(array('total' => $search['total'],
            'perPage' => $this->perPage));
        return $html;

    }

    /**
     * Search return courses that match the request..
     * @param $request
     */
    public function search($term, Request $request)
    {
        $input = $request->all();


        $genEndAttr = $request->input('genEdReq');
        $instructionMode = $request->input('instrucMode');
        $courseNbr = $request->input("courseNbr");
        $creditHr = $request->input("creditHr");
        $session = $request->input("session");
        $days = $request->input("days");

        $page = $request->input("page");


        $query = Models\ClassTable::select(\DB::connection("coursebrowser")
            ->raw('crs_subj_cd,crs_catlg_nbr,crs_subj_dept_cd, crs_desc'));

        //set acad term
        $query = $query->where('acad_term_cd', '=', $term);

        if (isset($dept) && $dept != "") $query = $query
            ->where('crs_subj_dept_cd', 'like', $dept);

        /* Instruction mode */
        if (isset($instructionMode) && $instructionMode != "" && stripos($instructionMode, "all") === false)
            $query = $query->where('cls_instrc_mode_cd', 'like', $instructionMode);

        /** catalog Nbr */
        if (isset($courseNbr) && $courseNbr != "" && stripos($courseNbr, "all") === false) {

            $values = explode("-", $courseNbr);// 379
            if ($values[1] == "above")
                $query = $query->where('crs_catlg_nbr', '>=', $values[0]);
            else
                $query = $query->whereBetween("crs_catlg_nbr", array($values[0], $values[1]));

        }

        /** Session */
        if (isset($session) && $session != "" && stripos($session, "all") === false)
            $query = $query->where('cls_sesn_cd', '=', $session);

        /** Credit Hrs */
        if (isset($creditHr) && $creditHr != "" && stripos($creditHr, "all") === false) {

            if ($creditHr == 1) {
                $query = $query->where('cls_assct_min_unt_nbr', '=', 1);
            } else {
                $min_credit_hr = $creditHr > 1 ? $creditHr - 1 : 1;
                $max_credit_hr = $creditHr;


                $query = $query->where('cls_assct_min_unt_nbr', '>=', $min_credit_hr)
                    ->where('cls_assct_max_unt_nbr', "<=", $max_credit_hr);
            }


        }

        /** days check */
        if (isset($days)) {
            $CI = $this;
            $convert_days = array_map(function ($day) use ($CI) {
                return array_search($day, $CI->days);
            }, $days);

            $day_query = "";
            foreach ($convert_days as $day) {

                if ($day_query != "")
                    $day_query .= " and ";
                $day_query .= "(cls_drvd_mtg_ptrn_cd like '$day%'
                or cls_drvd_mtg_ptrn_cd like '%$day%'
                or  cls_drvd_mtg_ptrn_cd like '$day%' or  cls_drvd_mtg_ptrn_cd like '$day')";

            }


            $query = $query->whereRaw($day_query);
        }


        /** attributes */
        if ((isset($genEndAttr) && $genEndAttr != "") || (isset($caseReq) && $caseReq != "")) {

            $query = $query->join('class_attribute',
                'class.cls_key', '=', 'class_attribute.cls_key');
        }

        if (isset($genEndAttr) && $genEndAttr != "") {
            if (stripos($genEndAttr, "all") !== false) {
                $query = $query->whereIn('class_attribute.crs_attrib_val_cd',
                    $this->genEd);
            } else {
                echo $genEndAttr;
                $query = $query->where('class_attribute.crs_attrib_val_cd', 'like',
                    $genEndAttr);
            }

        }

        if (isset($caseReq) && $caseReq != "") {
            if (stripos($caseReq, "all") !== false) {
                $query = $query->whereIn('class_attribute.crs_attrib_val_cd',
                    $this->caseRequirements);
            } else {

                $query = $query->where('class_attribute.crs_attrib_val_cd', '=', $caseReq);
            }

        }

        $query = $query->orderBy('crs_subj_cd', 'asc')->orderBy('crs_catlg_nbr', 'asc')
            ->groupBy('crs_subj_dept_cd', 'crs_subj_cd',
                'crs_catlg_nbr', 'crs_desc')
            ->distinct()->paginate($this->perPage);


        $courses = $this->buildResults($term, $instructionMode, $session,
            $days, $query);


        return ['total' => $query->total(),
            'view' =>
                isset($courses) && $courses != "" ? view('coursebrowser.listing')
                    ->with("courses",
                        $courses)->render() : ""];


    }

    /**
     * Build results for the listing page
     * @param $instructionMode
     * @param $session
     * @param $days
     * @param $result
     */
    protected function buildResults($term, $instructionMode,
                                    $session, $days, $result)
    {

        $courses = "";
        foreach ($result as $c) {
            $x = sprintf("%s %s &mdash; %s",
                $c->crs_subj_cd, $c->crs_catlg_nbr, $c->crs_desc);

            $subject = explode("-", $c->crs_subj_cd); // extract letter information

            $query_string = "";
            foreach (['term' => $term, 'instrucMode' => $instructionMode,
                         'session' => $session,
                         'days' => $days,
                         'nbr' =>
                             $subject[1] . "-" . $c->crs_catlg_nbr,
                         'dept' => $c['crs_subj_dept_cd']] as $k => $v) {

                if (isset($v) & $v != "") {
                    if ($query_string != "")
                        $query_string .= "&";

                    if (!is_array($v))
                        $query_string .= "$k=$v";
                    else {
                        $str = "";
                        foreach ($v as $item) {
                            if ($str != "")
                                $str .= "&";
                            $str .= "$k" . "[]=" . $item;
                        }
                        $query_string .= $str;
                    }
                }

            }
            $link = $_ENV['HOME_PATH'] . "/register/schedule-classes/course.html";
            if ($query_string != "")
                $link .= "?" . $query_string;

            $courses[] = ['description' => $x,
                'link' => $link];
        }

        return $courses;

    }

    /**
     * Course - return a single course information
     * @param Request $request
     */
    public function course(Request $request)
    {
        // term, instruction mode, department, nbr - letter- number, session
        $term = $request->get("term");
        $dept = $request->get("dept");
        $nbr = $request->get("nbr");
        $session = $request->get("session");
        $instructionMode = $request->get("instrucMode");
        $days = $request->get("days");
        $courseltr = "";
        $catalogNbr = "";
        if (isset($nbr)) {
            $string = explode("-", $nbr);
            $courseltr = $string[0];
            $catalogNbr = $string[1];
        }


        /** 1. get json files */
        /** build form - with sessions, instruction modes, days, */
        $sessions = $this->getSessions($term);
        $instructionModes = $this->getInstructionModes();

        /** @var Build Markup $html */
        $html = $this->builder->open()
            ->attribute('action',
                $_ENV['HOME_PATH'] . "register/schedule-classes/course.html")
            ->attribute('method', 'GET')
            ->addClass('filter')
            ->attribute('data-api', $_ENV['HOME_PATH'] .
                '/_php/laravel-app/public/course');

        $html .= $this->builder->formWrapperOpen()->addClass('halves');
        $html .= $this->builder->select("Class session", "session",
            $sessions,
            array('class' => "session"), isset($session) ? $session : "");

        $html .= $this->builder->select("Instruction mode", "instrucMode",
            $instructionModes,
            array('class' => "instrucMode"), isset($instructionMode) ? $instructionMode : "");


        $html .= $this->builder->formWrapperClose();

        $html .= "<div class='grid'>";
        $html .= $this->builder->checkboxes(array_map(function ($d) use (&$days) {
            $array = ['label' => $d, 'name' => 'days[]', 'value' => $d];
            if (isset($days) && in_array($d, $days))
                $array['attributes'] = ['checked' => 'checked'];
            return $array;
        }, $this->days))->header("Day of the week");
        $html .= "</div>";


        $html .= $this->builder->button('Go')->attribute('type', 'submit');
        $html .= $this->builder->close();

        $model = new Models\Course();

        $convert_days = "";
        if (isset($days)) {
            $CI = $this;
            $convert_days = array_map(function ($day) use ($CI) {
                return array_search($day, $CI->days);
            }, $days);
        }


        $data = $model->buildCourses($term, null, $dept,
            $courseltr, $catalogNbr, $instructionMode, $convert_days, $session);

        $result = $this->fractal->createData($data['data'])->toArray();


        $view = (view('coursebrowser.course')
            ->with('course',
                current($result['data']))->render());
        $html .= $this->builder->resultsWrapper($view);

        echo $html;

    }

}