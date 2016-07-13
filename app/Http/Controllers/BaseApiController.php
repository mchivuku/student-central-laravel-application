<?php
/**
 * Created by
 * User: IU Communications
 * Date: 6/21/16
 */

namespace StudentCentralApp\Http\Controllers;


namespace StudentCentralApp\Http\Controllers;

use Illuminate\Routing\Controller as BaseController;
use Illuminate\Support\Facades\Input as Input;
use League\Fractal\Manager;
use League\Fractal\Pagination\IlluminatePaginatorAdapter;
use League\Fractal\Resource\Collection;
use League\Fractal\Resource\Item as Item;

class BaseApiController extends BaseController
{

    protected $fractal;
    protected $perPage = 10;

    static $success = 200;
    static $error = 500;
    static $notfound = 404;
    static $unauthorized = 403;

    public function __construct(Manager $fractal)
    {
        $this->fractal = $fractal;
    }

}