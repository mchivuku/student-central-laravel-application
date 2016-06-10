<?php
/**
 * Created by
 * User: IU Communications
 * Date: 6/9/16
 */


namespace StudentCentralCourseBrowser\Transformers;
use League\Fractal\TransformerAbstract;

class ClassDetailsTransformer extends TransformerAbstract
{


    public function __construct($params = [])
    {
        $this->params = $params;

        if(isset($params['includes']))
        {
            $this->defaultIncludes=$params['includes'];
        }

        $this->base_transformer = new BaseTransformer();

    }


    public function transform($details)
    {


        return [
            'start_time'=>ltrim($details['cls_mtg_strt_tm'],'0'),
            'end_time'=>ltrim($details['cls_mtg_end_tm'],'0'),
            'meeting_pattern'=>$details['cls_drvd_mtg_ptrn_cd'],
            'facility_bldg_code'=>$details['facil_bldg_cd'],
            'facility_bldg_rm_number'=>$details['facil_bldg_rm_nbr'],
            'instructor'=>$details['instructor']
        ];
    }

}