<?php
/**
 * Created by
 * User: IU Communications
 * Date: 6/25/16
 */

namespace StudentCentralApp\Http\Controllers;

use Illuminate\Http\Request;
use StudentCentralApp\Models as Models;

class CourseController extends BaseCourseController
{

    protected $hosted_urls = [4168 => "/register/schedule-classes/index.html",
        4162 => "/register/schedule-classes/spring-2016.html",
        4165 => "/register/schedule-classes/summer-2016.html"
    ];


    public function __construct()
    {
        parent::__construct();
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
            ->attribute('action',
                $_ENV['HOME_PATH'] . $this->hosted_urls[$term])
            ->attribute('method', 'GET')
            ->addClass('filter')
            ->attribute('data-api', $_ENV['HOME_PATH'] .
                '/_php/laravel-app/public/courses/' . $term);

        $html .= $this->builder->formWrapperOpen()->addClass('thirds');

        // build genEd
        $requirements = Models\ClassAttribute::
        select('crs_attrib_val_cd', 'crs_attrib_val_desc')
            ->whereIn('crs_attrib_val_cd', $this->genEd)
            ->distinct()->get()->lists("crs_attrib_val_desc", "crs_attrib_val_cd")
            ->toArray();

        $requirements = array_merge(["" => "GenEd requirement"], $requirements);

        /** @var Departments $departments */
        $departments = $this->getDepartments($term);
        $sessions = $this->getSessions($term);


        /** @var  $instructionModes */
        $instructionModes = Models\ClassTable
            ::select("cls_instrc_mode_cd", "cls_instrc_mode_desc")
            ->whereIn('cls_instrc_mode_cd',
                $this->instructionModes)
            ->distinct()->get()->lists("cls_instrc_mode_desc",
                "cls_instrc_mode_cd")
            ->toArray();

        $instructionModes = array_merge(["" => "Instruction modes"], $instructionModes);

        $html .= $this->builder->select("GenEd requirement", "genEdReq",
            $requirements,
            array('class' => "genEdReq"), isset($genEndAttr) ? $genEndAttr : "");

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
            $this->course_numbers,
            array('class' => "courseNbr"), isset($courseNbr) ? $courseNbr : "");

        $html .= $this->builder->select("Credit hour", "creditHr",
            $this->creditHrs,
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

        /** @var Get Results - $search */
        $search = $this->search($term, $request);

        /** Selection Wrapper */
        $selectionWrapper = $this->builder->selectionWrapper($search['total']);

        if (isset($dept) && $dept != "")
            $selectionWrapper = $selectionWrapper->filterItem('Department', $departments[$dept], $dept);

        if (isset($genEndAttr) && $genEndAttr != "")
            $selectionWrapper = $selectionWrapper->filterItem('GenEd requirement', $requirements[$genEndAttr], $genEndAttr);


        if (isset($instructionMode) && $instructionMode != "")
            $selectionWrapper = $selectionWrapper->filterItem('Instruction mode',
                $instructionModes[$instructionMode], $instructionMode);

        if (isset($courseNbr) && $courseNbr != "")
            $selectionWrapper = $selectionWrapper->filterItem('Course number',
                $this->course_numbers[$courseNbr], $courseNbr);
        if (isset($creditHr) && $creditHr != "")
            $selectionWrapper = $selectionWrapper->filterItem('Credit hour',
                $this->creditHrs[$creditHr], $creditHr);

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

        $genEd = $request->input('genEdReq');
        $dept = $request->input('dept');
        $instructionMode = $request->input('instrucMode');
        $courseNbr = $request->input("courseNbr");
        $creditHr = $request->input("creditHr");

        $page = $request->input("page");

        $json_file = isset($dept) ? storage_path("courses/current/$term/$dept.json") :
            storage_path("courses/current/$term/$term.json");

        // If file not found
        if (!file_exists($json_file)) return;

        // dept, genEd
        if (isset($dept))
            return $this->searchByDepartment($term, $json_file, $request);

        return $this->searchByTerm($term, $json_file, $request);


    }

    /** If dept is selected - search for parameters inside the department
     * */
    protected function searchByDepartment($term, $json_file, Request $request)
    {

        $genEndAttr = $request->input('genEdReq');
        $instructionMode = $request->input('instrucMode');
        $courseNbr = $request->input("courseNbr");
        $creditHr = $request->input("creditHr");
        $session = $request->input("session");
        $days = $request->input("days");


        $page = $request->input("page");

        $start_index = $this->get_start_index($page);

        // Build collection
        $courses = collect(json_decode(file_get_contents($json_file), true));

        collect($courses['data'])->filter(function ($course) use ($instructionMode) {
            return $this->filterByInstructionMode($course, $instructionMode);
        });

        /** @var Filter by instruction mode, course number, credit hours, course attributes
         * class session and day of the week.
         * $result */
        $result = collect($courses["data"])
            ->filter(function ($course) use ($creditHr, $courseNbr, $genEndAttr) {
                return $this->filterByCreditHrs($course, $creditHr);
            })->filter(function ($course) use ($courseNbr) {
                return $this->filterByCatalogNbr($course, $courseNbr);
            })->filter(function ($course) use ($genEndAttr) {
                return $this->filterByCourseAttributes($course, $genEndAttr);
            })->filter(function ($course) use ($instructionMode) {
                return $this->filterByInstructionMode($course, $instructionMode);
            })->filter(function ($course) use ($session) {
                return $this->filterBySession($course, $session);
            })->filter(function ($course) use ($days) {
                return $this->filterByDaysOfWeek($course, $days);
            });

        $total = $result->count();
        if (!isset($total)) $total = 0;
        $result = $result->slice($start_index, $this->perPage);

        $courses = "";

        // Append class attributes to the link - instructionMode, class session, days of the week.
        // catlog_nbr, dept, course_letter

        $courses = $this->buildResult($term, $instructionMode, $session, $days, $result);

        return ['total' => $total, 'view' =>
            isset($courses) && $courses != "" ? view('coursebrowser.listing')
                ->with("courses",
                    $courses)->render() : ""];


    }

    /**
     * Filter by instruction mode
     * @param $course
     * @param $genEdcode
     * @return bool
     */
    function filterByInstructionMode($assocSet, $instructionMode)
    {

        if (!isset($instructionMode) || $instructionMode == "")
            return true;

        /** @var Inner array $classes */
        $classes = collect($assocSet['associated_classes'])
            ->filter(function ($set) use ($instructionMode) {
                foreach ($set as $k => $v) {
                    if ($v['instruction_mode']['code'] == $instructionMode)
                        return true;
                }
                return false;
            });

        if (count($classes) > 0)
            return true;

        return false;
    }

    /**
     * Filter by credit hrs
     * @param $course
     * @param $creditHrs
     * @return bool
     */
    function filterByCreditHrs($course, $creditHrs)
    {

        if (!isset($creditHrs) || $creditHrs == "")
            return true;

        if ($course['credit_hrs'] == $creditHrs)
            return true;

        return false;
    }

    /**
     * Filter by catalogNbr range
     * @param $course
     * @param $catalogNbr
     * @return $this
     */
    function filterByCatalogNbr($course, $catalogNbr)
    {

        if (!isset($catalogNbr) || $catalogNbr == "")
            return true;

        // course number input is like min'-'max
        $values = explode("-", $catalogNbr);
        if ($values[1] == "above") {

            if ($course["course_catalog_nbr"] >= $values[0])
                return true;
        } else {

            if ($course["course_catalog_nbr"] >= $values[0] &&
                $course["course_catalog_nbr"] < $values[1]
            )
                return true;
        }

        return false;
    }

    /**
     * Filter course by attribute
     * @param $course
     * @param $genEdcode
     * @return bool
     */
    function filterByCourseAttributes($course, $genEdcode)
    {

        if (!isset($genEdcode) || $genEdcode == "")
            return true;


        $attributes = isset($course["course_attributes"]) ?
            $course["course_attributes"] : null;


        if (isset($attributes) && count($attributes) > 0) {

            if (collect($attributes)->pluck("attribute_code")->contains($genEdcode))
                return true;
        }


        return false;
    }

    /** Filter by class session */
    function filterBySession($assocSet, $session)
    {

        if (!isset($session) || $session == "")
            return true;

        /** @var Inner array $classes */
        $classes = collect($assocSet['associated_classes'])
            ->filter(function ($set) use ($session) {
                foreach ($set as $k => $v) {
                    if ($v['class_session']['session_code'] == $session)
                        return true;
                }
                return false;
            });

        if (count($classes) > 0)
            return true;

        return false;
    }

    /**
     * Filter by days of the week
     * @param $assocSet
     * @param $instructionMode
     * @return bool
     */
    function filterByDaysOfWeek($assocSet, $days)
    {

        if (!isset($days) || $days == "")
            return true;

        /** @var Inner array $classes */
        $classes = collect($assocSet['associated_classes'])
            ->filter(function ($set) use ($days) {
                foreach ($set as $k => $v) {
                    if (strpos($days, $k) !== false)
                        return true;
                }
                return false;
            });

        if (count($classes) > 0)
            return true;

        return false;
    }

    /**
     * Search by term
     * @param $json_file
     * @param Request $request
     */
    protected function searchByTerm($term, $json_file, Request $request)
    {

        $genEndAttr = $request->input('genEdReq');
        $instructionMode = $request->input('instrucMode');
        $courseNbr = $request->input("courseNbr");
        $creditHr = $request->input("creditHr");
        $session = $request->input("session");
        $days = $request->input("days");


        $page = $request->input("page");

        $start_index = $this->get_start_index($page);


        // Build collection
        $departments = collect(json_decode(file_get_contents($json_file), true));
        $result = "";

        collect($departments["data"])->each(function ($department)
        use (
            $creditHr, $courseNbr, $genEndAttr, &$result, $instructionMode,
            $session, $days
        ) {
            $collection = collect($department["courses"])
                ->filter(function ($course) use ($creditHr) {
                    return $this->filterByCreditHrs($course, $creditHr);
                })->filter(function ($course) use ($courseNbr) {
                    return $this->filterByCatalogNbr($course, $courseNbr);
                })->filter(function ($course) use ($genEndAttr) {
                    return $this->filterByCourseAttributes($course, $genEndAttr);
                })->filter(function ($course) use ($instructionMode) {
                    return $this->filterByInstructionMode($course, $instructionMode);
                })->filter(function ($course) use ($session) {
                    return $this->filterBySession($course, $session);
                })->filter(function ($course) use ($days) {
                    return $this->filterByDaysOfWeek($course, $days);
                });

            if ($collection->count() > 0) {
                if (is_array($result))
                    $result = array_merge($result, $collection->toArray());
                else
                    $result = $collection->toArray();
            }
        });


        if ($result != "")
            $total = collect($result)->count();
        else
            $total = 0;


        $result = collect($result)->slice($start_index, $this->perPage);
        $courses = $this->buildResults($term, $instructionMode, $session, $days, $result);


        return ['total' => $total, 'view' =>
            $courses == "" ? "" : view('coursebrowser.listing')
                ->with("courses", $courses)->render()];
    }

    /**
     * Build results for the listing page
     * @param $instructionMode
     * @param $session
     * @param $days
     * @param $result
     */
    protected function buildResults($term, $instructionMode, $session, $days, $result)
    {
        $courses = "";
        foreach ($result as $c) {
            $x = $c['description_line'];
            $query_string = "";
            foreach (['term' => $term, 'instrucMode' => $instructionMode, 'session' => $session, 'days' => $days, 'nbr' =>
                $c['course_subj_letter'] . "-" . $c['course_catalog_nbr'], 'dept' => $c['subject_department_short_desc']] as $k => $v) {

                if (isset($v) & $v != "") {
                    if ($query_string != "")
                        $query_string .= "&";

                    $query_string .= "$k=$v";
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

    }


}