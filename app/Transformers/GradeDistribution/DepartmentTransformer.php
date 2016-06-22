<?php
/**
 * Created by
 * User: IU Communications
 * Date: 6/21/16
 */


namespace StudentCentralApp\Transformers\GradeDistribution;
use League\Fractal\TransformerAbstract;

class DepartmentTransformer extends TransformerAbstract
{

    public function transform($term)
    {
        return [
            'dept' =>  $term->dept,
            'description' => $term->acad_org_desc

        ];
    }


}