<?php
/**
 * Created by
 * User: IU Communications
 * Date: 6/25/16
 */

namespace StudentCentralApp\Http\Controllers;

use Illuminate\Http\Request;
use StudentCentralApp\Models as Models;


class CrossListedCoursesController extends BaseCourseController
{

    public function __construct()
    {

        parent::__construct();

    }

    /**
     * Call function to build the dropdown for filtering data.
     */
    public function index(Request $request)
    {
        $dept = $request->input('dept');
        $acadTerm = $request->input('acadTerm');

        /** @var build markup $html */
        $html = $this->builder->open()
            ->attribute('action',
                $_ENV['HOME_PATH'] . '/register/schedule-classes/crosslisted-courses.html')
            ->attribute('method', 'GET')
            ->addClass('hide-labels')
            ->attribute('data-api', $_ENV['HOME_PATH'] .
                '/_php/laravel-app/public/crosslisted');
        $html .= $this->builder->formWrapperOpen()->addClass('halves');

        /** @var terms */
       $terms = self::$default_element + ["All Terms" => "All Terms"] + $this->getTerms();

        $departments = $this->getDepartments();

        $html .= $this->builder->select("Term", "acadTerm",
            $terms,
            array('class' => "acadTerm"), isset($acadTerm)?$acadTerm:"");

        $html .= $this->builder->select("Department", "dept",
            $departments,
            array('class' => "dept"),  isset($dept)?$dept:"");

        $html .= $this->builder->formWrapperClose();

        $input = $request->all();

        if (isset($input) && count($input) == 0) {
            $html .= view("emptyresults")->render();
            return $html;
        }

        $result = $this->search($request,$departments);

        $selectionWrapper = $this->builder->selectionWrapper($result['total']);
        // filter
        if(isset($acadTerm) && $acadTerm!="")
            $selectionWrapper->filterItem('Term',$terms[$acadTerm],$acadTerm);
        if(isset($dept) && $dept!="")
            $selectionWrapper->filterItem('Department',$departments[$dept],$dept);

        $html.=$selectionWrapper;

        $html .= $this->builder->resultsWrapper($result['view']);
        $html .= $this->builder->pagination(array('total' => $result['total'],
            'perPage' => $this->perPage));

        return $html;

    }

    /**
     * Search return courses that match the request..
     * @param $request
     */
    public function search(Request $request,$departments)
    {

        $dept = $request->input('dept');
        $acadTerm = $request->input('acadTerm');
        $page = $request->input("page");

        $json_file = storage_path("courses/current/crosslisted_courses.json");

        // If file not found
        if (!file_exists($json_file)) return;

        $json = json_decode(file_get_contents($json_file), true);

        /** @var get all cross listed courses -  format data */
        $all = "";
        collect($json['data'])->each(function ($term) use (&$all) {
            collect($term['cross_listings'])->each(function ($course) use (&$all) {
                $all[$course['term']['term'] . "_" . $course['department']] = $course;
            });
        });

        $all = collect($all)->filter(function ($course) use ($acadTerm) {
            if (!isset($acadTerm) || $acadTerm=="" || stripos($acadTerm,"all")!==false) return true;

            if ($course['term']['term'] == $acadTerm)
                return true;

            return false;
        })->filter(function ($course) use ($dept) {
            if (!isset($dept) || $dept=="" || stripos($dept,"all")!==false) return true;

            if ($course['department'] == $dept)
                return true;

            return false;
        })->sortBy(function($x){
            return $x['department'];
        });


        return ['total' => $all->count(),
            'view' => view("crosslistedcourses.index")->with('departments',$departments)
                ->with('result',
                $all->slice($this->get_start_index($page),
                    $this->perPage))->render()];
    }


}