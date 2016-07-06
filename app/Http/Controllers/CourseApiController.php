<?php
/**
 * Created by
 * User: IU Communications
 * Date: 6/26/16
 */

namespace StudentCentralApp\Http\Controllers;

use StudentCentralApp\Models as Models;

use Illuminate\Http\Request;
use League\Fractal\Manager;
use League\Fractal\Resource\Collection;
/**
 * Class CourseApiController
 * @package StudentCentralApp\Http\Controllers
 */
class CourseApiController extends BaseApiController
{
//('0GENEDEC','0GENEDMM','0GENEDAH','0GENEDSH','0GENEDNM','0GENEDWL','0GENEDWC')
    protected $genEd = ['0GENEDEC',
        '0GENEDMM','0GENEDAH','0GENEDSH','0GENEDNM','0GENEDWL','0GENEDWC'];

    //('P','OA','OI','HY')
    protected $instructionModes = ['P','OA','OI','HY'];

    protected $builder;
    public function __construct(Manager $fractal)
    {
        parent::__construct($fractal);
        $builder = new \IUCommFormBuilder();

    }

    /**
     * Function to return generalEd requirements for the dropdown.
     * @return string
     */
    public function genEdRequirements(){

        $requirements = Models\ClassAttribute::
            select('crs_attrib_val_cd','crs_attrib_val_desc')
            ->whereIn('crs_attrib_val_cd',$this->genEd)
            ->distinct()->get();

        return $this->fractal
            ->createData(new Collection($requirements,function($req){
                    return ['attribute'=>$req->crs_attrib_val_cd,'description'=>$req->crs_attrib_val_desc];
            }))
            ->toJson();

    }

    /**
     * Function to return departments
     * @return string
     */
    public function departments($term){

        $departments = Models\TermDepartment::acadTerm($term)
            ->orderBy('crs_subj_dept_cd')->get();

        return $this->fractal
            ->createData(new Collection($departments,function($dept){
                return ['dept'=>$dept->crs_subj_dept_cd,
                    'description'=>$dept->crs_subj_desc];
            }))
            ->toJson();

    }

    /**
     * Function return instruction mode
     * class instruction mode - cant be combined
     */
    public function instructionMode(){

        $instructionModes = Models\ClassTable
            ::select("cls_instrc_mode_cd", "cls_instrc_mode_desc")
            ->whereIn('cls_instrc_mode_cd',$this->instructionModes)->distinct()->get();
        return $this->fractal
            ->createData(new Collection($instructionModes,function($mode){
                return ['mode'=>$mode->cls_instrc_mode_cd,
                    'description'=>$mode->cls_instrc_mode_desc];
            }))
            ->toJson();

    }
}
