<?php
/**
 * Created by
 * User: IU Communications
 * Date: 6/9/16
 */

namespace StudentCentralApp\Transformers;

use League\Fractal\TransformerAbstract;


class ClassTransformer extends TransformerAbstract
{


    protected $classDetailsTransformer;

    public function __construct( $params = [])
    {
        $this->params = $params;

        $this->base_transformer = new BaseTransformer();
        $this->classDetailsTransformer = new ClassDetailsTransformer();

    }


    /**
     * @param $class - class transform
     * @return array
     */
    public function transform($class)
    {
        try {
            return [
                'component_short_description' => $class['cls_cmpnt_cd'],
                'component_long_description' => $class['cls_cmpnt_desc'],
                'consent_type_requirement' => isset($class['cls_cnst_type_req']) ? $class['cls_cnst_type_req'] : "",
                'class_number' => $class['cls_nbr'],
                'class_closed' => $class['clsd'],
                'details' => collect($class['details'])->map(function ($detail) {
                    return $this->classDetailsTransformer->transform($detail);
                })->toArray(),
                'enrollment_capacity' => $class['cls_enrl_cpcty_nbr'],
                'total_available' => $class['cls_tot_avl_nbr'],
                'waitlisted_total_number' => $class['cls_wlst_tot_nbr'],
                'long_description' => $class['cls_long_desc'],
                'class_attributes' => collect($class['class_attributes'])
                    ->map(function ($attribute) {
                        return ['attribute_code' => $attribute['crs_attrib_val_cd'],
                            'attribute_desc' => $attribute['crs_attrib_val_desc']];

                    }),

                "topic"=>isset($class["crs_tpc_desc"])?$class["crs_tpc_desc"]:"",
                'class_notes_before' => collect($class['class_notes_b'])->unique()->map(function ($notes) {
                    return $this->base_transformer->parseTextForHttpLinks($notes);
                }),

                'class_notes_after' => collect($class['class_notes_a'])->unique()->map(function ($notes) {
                    return $this->base_transformer->parseTextForHttpLinks($notes);
                }),

            'class_session'=>['session_code'=>$class['cls_sesn_cd'],'derived_session'=>$class['cls_drvd_sesn_cd'],
                'session_description'=>$class['cls_sesn_desc']],
                'instruction_mode'=>['code'=>$class['cls_instrc_mode_cd'],
                    'short_description'=>$class['cls_instrc_mode_shrt_desc'],
                    'long_description'=>$class['cls_instrc_mode_desc']]
            ];
        }catch(\Exception $ex){
            var_dump($ex->getTraceAsString());
            exit;
        }
    }

}