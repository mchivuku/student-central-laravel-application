<?php
/**
 * Created by
 * User: IU Communications
 * Date: 6/9/16
 */


namespace StudentCentralApp\Transformers;
use League\Fractal\TransformerAbstract;

class ClassDetailsTransformer extends TransformerAbstract
{


    public function __construct($params = [])
    {
        $this->params = $params;
        $this->base_transformer = new BaseTransformer();

    }


    public function transform($details)
    {

        try{
            return [
                'start_time'=>ltrim($details['cls_mtg_strt_tm'],'0'),
                'end_time'=>ltrim($details['cls_mtg_end_tm'],'0'),
                'meeting_pattern'=>isset($details['cls_drvd_mtg_ptrn_cd'])?$details['cls_drvd_mtg_ptrn_cd']:"",
                'facility_bldg_code'=>$details['facil_bldg_cd'],
                'facility_bldg_rm_number'=>$details['facil_bldg_rm_nbr'],
                'instructor'=>isset($details['instructor'])?$details['instructor']:""
            ];
        }catch(\Exception $ex){

            var_dump($ex->getTraceAsString());
        }


    }

}