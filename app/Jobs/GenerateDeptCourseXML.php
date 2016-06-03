<?php
/**
 * Created by
 * User: IU Communications
 * Date: 6/2/16
 */
namespace StudentCentralCourseBrowser\Jobs;

class GenerateDeptCourseXML extends Job{

    public function __construct()
    {
        parent::__construct('GenerateDeptCourseXML');
    }

    protected function run()
    {
        $acad_terms = $this->getAcadTerms();

        //1.Iterate through terms
        //2.Iterate through departments and build courses and classes;
        collect($acad_terms)->each(function($term){
            //get all departments for the acad term







        });




    }
}