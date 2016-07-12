<?php
/**
 * Created by
 * User: IU Communications
 * Date: 6/2/16
 */

namespace StudentCentralApp\Jobs;
use League\Fractal\Manager;
use League\Fractal\Resource\Collection as Collection;

use StudentCentralApp\Models as Models;
use StudentCentralApp\Transformers\CrossListedCoursesTransformer;
use StudentCentralApp\Transformers\TermDepartmentCourseTransformer;
use Symfony\Component\Process\Process;

ini_set('memory_limit', '500M');

class GenerateJSONFiles extends Job
{

    protected $courseTransformer, $fractal;

    public function __construct()
    {
        parent::__construct('GenerateJSONFiles');
        $this->fractal = new Manager();

    }

    /**
     * Function builds json files for each
     * department with all the courses
     * and classes for that department.
     */
    protected function run()
    {

        $acad_terms = $this->getAcadTerms();

        /** @var Root Folder - that has date_timestamp $path */
        $path = $this->makeCoursesFolder();


        //1.Iterate through terms
        //2.Iterate through departments and build courses and classes;
        $all_cross_listed_courses = "";

        collect($acad_terms)->each(
        /**
         * @param $term
         */
            function ($term) use ($path, &$all_cross_listed_courses) {
                $term_info = Models\TermDescription::acadTerm($term)->first();

                /** Break departments into two sets */
                $departments = Models\TermDepartment::acadTerm($term)
                    ->orderBy('crs_subj_dept_cd')->get();

                $all_courses = [];

                $term_info = Models\TermDescription::acadTerm($term)->first();
                $term_folder_path = $this->makeTermFolder($term, $path);

                foreach ($departments as $dept) {

                    $term = $term_info->term;

                    // json for each department
                    $this->buildCourses($term,
                        $term_info, $dept, $all_courses,
                        $term_folder_path);
                }

                // cross listings for department.
                $this->buildCrossListings($term, $term_info, $all_cross_listed_courses);

                $data = new Collection($all_courses,
                    new TermDepartmentCourseTransformer);
                $this->saveJsonToFile($term_folder_path,
                    $term,
                    $this->fractal->createData($data)->toJson());
            });

        // save the cross listings - outside on the course folder.
        $data = new Collection($all_cross_listed_courses,
            new CrossListedCoursesTransformer);

        $this->saveJsonToFile($path, 'crosslisted_courses',
            $this->fractal->createData($data)->toJson());
    }

    /** Make  */
    protected
    function makeCoursesFolder()
    {
        $path = storage_path() . "/courses/current";

        // move the current folder to backup
        if (file_exists($path))
            $this->runProcess('mv  ' . $path . "  " . storage_path()
                . "/courses/backup/courses_" . date('m-d-Y_Hi'));

        // create new current folder.
        $process = $this->runProcess('mkdir ' . $path);
        if ($process) return $path;

        return false;
    }

    /** Helper Functions */
    protected
    function runProcess($commandstring)
    {
        $process = new Process($commandstring);
        $process->run();

        // executes after the command finishes
        if (!$process->isSuccessful()) {
            throw new \Exception($commandstring . " failed");
        }

        return true;

    }

    /** Create main folder with timestamp, terms folder inside it */
    protected
    function makeTermFolder($term, $path)
    {
        $term_folder_path = $path . "/" . $term;
        $process = $this->runProcess('mkdir ' . $term_folder_path);

        if ($process) return $term_folder_path;
        return false;
    }

    /**
     * Function builds courses
     * @param $term
     * @param $term_info
     * @param $dept
     * @param $allcourses
     * @param $term_folder_path
     */
    protected function buildCourses($term, $term_info, $dept, &$allcourses,
                                    $term_folder_path)
    {

        $model = new Models\Course();

        $data = $model->buildCourses($term, $term_info, $dept);


        if (isset($data)){
            $this->saveJsonToFile($term_folder_path,
                $dept->crs_subj_dept_cd,
                ($this->fractal->createData($data['data'])->toJson()));
            $allcourses[$dept->crs_subj_dept_cd] =
                ["department" => $dept->crs_subj_dept_cd,
                "courses" => $data['courses']];

        }


        echo $dept->crs_subj_dept_cd . " created the file" . PHP_EOL;



    }



    /**
     * Save JSON to file
     * @param $path
     * @param $filename
     * @param $contents
     */
    public function saveJsonToFile($path, $filename, $contents)
    {

        $ext = ".json";

        if (!file_exists($filename))
            $fd = fopen($path . "/" . $filename . $ext, "w");
        else
            $fd = fopen($path . "/" . $filename . $ext, "a+");

        fwrite($fd, $contents);
        fclose($fd);
    }

    /** Courses that are not offered by the department refer to courses in a different department */
    protected function buildCrossListings($term,
                                          $term_info,
                                          &$all_cross_listed_courses
    )
    {

        $crosslisted_courses = Models\CrossListedCourse
            ::where('ACAD_TERM_CD', '=', $term)
            ->orderByRaw('CRS_SUBJ_DEPT_CD,
             CRS_CATLG_NBR, CRS_SUBJ_LTR_CD')
            ->distinct()->get();


        $crlt_crs_subj_dept_cd_keep = "";
        $crlt_crs_subj_line_keep = "";

        $courses = '';
        $crlt_crs_desc_line_keep = "";
        $crs_subj_dept_cd_keep = "";
        foreach ($crosslisted_courses as $course) {

            // main department
            $crs_attrib_val_cd = $course->crs_attrib_val_cd;
            $crs_subj_dept_cd = $crs_attrib_val_cd;

            //Class credits
            $cls_assct_min_unt_nbr = $course->cls_assct_min_unt_nbr;
            $cls_assct_max_unt_nbr = $course->cls_assct_max_unt_nbr;

            $crlt_crs_subj_cd = $course->crs_subj_cd;
            $crlt_crs_sub_desc = $course->crs_subj_desc;
            $crlt_crs_subj_dept_cd = $course->crs_subj_dept_cd;

            $crlt_crs_subj_line = $crlt_crs_sub_desc . " (" . $crlt_crs_subj_dept_cd . ")";
            $crlt_crs_catlg_nbr = $course->crs_catlg_nbr;

            $crlt_crs_desc = $course->crs_desc;
            $crlt_crs_tpc_desc = $course->crs_tpc_desc;

            if ($crlt_crs_tpc_desc != '')
                $crlt_crs_desc_use = $crlt_crs_tpc_desc;
            else
                $crlt_crs_desc_use = $crlt_crs_desc;

            if ($cls_assct_max_unt_nbr > $cls_assct_min_unt_nbr)
                $crlt_crs_desc_line = $crlt_crs_subj_cd . " " . $crlt_crs_catlg_nbr . " " . $crlt_crs_desc_use . " ( " . $cls_assct_min_unt_nbr . "&mdash;" . $cls_assct_max_unt_nbr . " CR)";
            else
                $crlt_crs_desc_line = $crlt_crs_subj_cd . " " . $crlt_crs_catlg_nbr . " " . $crlt_crs_desc_use . " ( " . $cls_assct_min_unt_nbr . " CR)";


            if (!isset($courses[$crs_attrib_val_cd]) || (isset($courses[$crs_attrib_val_cd]) &&
                    !in_array($crlt_crs_subj_dept_cd, $courses[$crs_attrib_val_cd]))
            ) {

                $courses[$crs_attrib_val_cd]
                [$crlt_crs_subj_dept_cd]
                ['crlt_crs_subj_line'] =
                    $crlt_crs_subj_line;
                $courses[$crs_attrib_val_cd]
                [$crlt_crs_subj_dept_cd]
                ['department'] =
                    $crlt_crs_subj_dept_cd;

                $courses[$crs_attrib_val_cd]
                [$crlt_crs_subj_dept_cd]
                ['crosslisted_for'] =
                    $crs_attrib_val_cd;

                $courses[$crs_attrib_val_cd]
                [$crlt_crs_subj_dept_cd]
                ['crlt_crs_subj_dept_cd'] =
                    $crlt_crs_subj_cd;

                $courses[$crs_attrib_val_cd]
                [$crlt_crs_subj_dept_cd]
                ['term'] = ['term' => $term,
                    'desc' => $term_info->description];
            }


            if ($crlt_crs_subj_line_keep != $crlt_crs_subj_line || $crlt_crs_subj_dept_cd_keep !=
                $crs_subj_dept_cd_keep
            ) {
                if (!isset($courses[$crs_attrib_val_cd][$crlt_crs_subj_dept_cd]
                        ['courses']) || !in_array($crlt_crs_desc_line,
                        $courses[$crs_attrib_val_cd][$crlt_crs_subj_dept_cd]
                        ['courses'])
                )
                    $courses[$crs_attrib_val_cd][$crlt_crs_subj_dept_cd]
                    ['courses'][] = $crlt_crs_desc_line;
            }

            if ($crlt_crs_desc_line_keep != $crlt_crs_desc_line ||
                $crlt_crs_subj_dept_cd_keep != $crs_subj_dept_cd_keep
            )
                if (!isset($courses[$crs_attrib_val_cd][$crlt_crs_subj_dept_cd]
                        ['courses']) || !in_array($crlt_crs_desc_line,
                        $courses[$crs_attrib_val_cd][$crlt_crs_subj_dept_cd]
                        ['courses'])
                )
                    $courses[$crs_attrib_val_cd][$crlt_crs_subj_dept_cd]
                    ['courses'][] = $crlt_crs_desc_line;

                else {
                    if ($crlt_crs_subj_line_keep != $crlt_crs_subj_line)
                        if (!isset($courses[$crs_attrib_val_cd][$crlt_crs_subj_dept_cd]
                                ['courses']) || !in_array($crlt_crs_desc_line,
                                $courses[$crs_attrib_val_cd][$crlt_crs_subj_dept_cd]
                                ['courses'])
                        )
                            $courses[$crs_attrib_val_cd][$crlt_crs_subj_dept_cd]
                            ['courses'][] = $crlt_crs_desc_line;
                }

            $crlt_crs_subj_line_keep = $crlt_crs_subj_line;
            $crlt_crs_subj_dept_cd_keep = $crlt_crs_subj_dept_cd;
            $crlt_crs_desc_line_keep = $crlt_crs_desc_line;
            $crs_subj_dept_cd_keep = $crs_subj_dept_cd;
        }

        $all_cross_listed_courses[$term] =
            ['term' => $term,
                'courses' => array_map(function ($course) {
                    $first_course = current($course);
                    $term = $first_course['term'];
                    $dept = $first_course['crosslisted_for'];
                    return ['term' => $term, 'department' => $dept,
                        'courses' => $course];

                }, $courses)];

    }
}