<?php
/**
 * Created by
 * User: IU Communications
 * Date: 7/7/16
 */

namespace StudentCentralApp\Http\Controllers;

use League\Fractal\Manager;
use League\Fractal\Resource\Collection;
use League\Fractal\Pagination\IlluminatePaginatorAdapter;


class TermsController extends Controller
{

    protected $fractal;

    public function __construct(Manager $fractal)
    {
        $this->fractal = $fractal;
     }

    public function index(){

        $data = \DB::connection("coursebrowser")->table('term_descr')
            ->select('term','description')->get();

        return $this->fractal
            ->createData(new Collection($data,function($term){
                return ['term'=>$term->term,
                    'description'=>$term->description];
            }))
            ->toJson();
    }

    /** pagination */
    public function paginate(){

        $data = \DB::connection("coursebrowser")->table('term_descr')
            ->select('term','description')->paginate(20);

        $resource =new Collection($data,function($term){
            return ['term'=>$term->term,
                'description'=>$term->description];
        });

        $resource->setPaginator(new IlluminatePaginatorAdapter($data));

        return $this->fractal
            ->createData($resource)
            ->toJson();
    }

}