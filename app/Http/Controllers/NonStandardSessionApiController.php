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
use League\Fractal\Pagination\IlluminatePaginatorAdapter;
use StudentCentralApp\Models as Models;


class NonStandardSessionApiController extends BaseApiController
{


    public function __construct(Manager $fractal)
    {
        parent::__construct($fractal);
        $this->perPage=20;
    }


    public function search(){
        $acad_term = Input::get('acad_term');
        $school = Input::get('school');
        $dept = Input::get('dept');

        $results = Models\NonStandardSessionDates::acadTerm($acad_term)
            ->school($school)->dept($dept)
            ->orderByRaw("crs_subj_cd, crs_catlg_nbr, cls_nbr")
            ->paginate($this->perPage);

        $resource = new Collection($results, function($item){
            return [
                'acad_term'=>$item->acad_term_cd,
                'acad_term_desc'=>$item->acad_term_desc,
                'school'=>$item->acad_grp_desc,
                'dept'=>$item->crs_subj_dept_cd,
                'dept_desc'=>$item->crs_subj_desc,
                'course_subj'=>$item->csr_subj_cd,
                'cls_nbr'=>$item->cls_nbr,
                'school_code'=>$item->acad_grp_cd,
                'crs_catlg_nbr'=>$item->crs_catlg_nbr,
                'title'=>$item->crs_desc,
                'start_date'=>isset($item->cls_strt_dt)?$item->cls_strt_dt:"N/A",
                'end_date'=>isset($item->cls_end_dt)?$item->cls_end_dt:"N/A",
                'refund_100'=>isset($item->refund_100)?$item->refund_100:"N/A",
                'auto_w'=>isset($item->auto_w)?$item->auto_w:"N/A",
                'refund_75'=>isset($item->refund_75)?$item->refund_75:"N/A",
                'refund_50'=>isset($item->refund_50)?$item->refund_50:"N/A",
                'refund_25'=>isset($item->refund_25)?$item->refund_25:"N/A",
                'pass_fail'=>isset($item->pass_fail)?$item->pass_fail:"N/A"

            ];
        });
        $resource->setPaginator(new IlluminatePaginatorAdapter($results));
        return $this->fractal->createData($resource)->toJson();
    }



    /**
     * Function to return acadterms
     */
    public function acadTerms(){

         $acad_terms = Models\NonStandardSessionDates::select('acad_term_cd','acad_term_desc')->distinct()->get();

        return $this->fractal
            ->createData(new Collection($acad_terms,
                new Transformer\AcadTermTransformer))
            ->toJson();
    }

    /**
     * Return school
     * @return string
     */
    public function schools(){

        $schools = Models\NonStandardSessionDates::select('acad_grp_cd','acad_grp_desc')
            ->distinct()->get();

        return $this->fractal
            ->createData(new Collection($schools,
               function($item){
                   return [
                       'acad_grp'=>$item->acad_grp_cd,
                       'desc'=>$item->acad_grp_desc
                   ];
               }))
            ->toJson();
    }


    /**
     * Return departments
     * @return string
     */
    public function departments(){

        $departments = Models\NonStandardSessionDates::select('crs_subj_dept_cd','crs_subj_desc')
            ->distinct()->get();

        return $this->fractal
            ->createData(new Collection($departments,
                function($item){
                    return [
                        'dept'=>$item->crs_subj_dept_cd,
                        'desc'=>$item->crs_subj_desc
                    ];
                }))
            ->toJson();
    }


}