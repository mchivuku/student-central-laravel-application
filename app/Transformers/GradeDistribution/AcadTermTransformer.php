<?php
/**
 * Created by
 * User: IU Communications
 * Date: 6/21/16
 */


namespace StudentCentralApp\Transformers\GradeDistribution;
use League\Fractal\TransformerAbstract;

class AcadTermTransformer extends TransformerAbstract
{

    public function transform($term)
    {
        return [
            'acad_term' => (int)$term->acad_term_cd,
            'description' => $term->acad_term_desc

        ];
    }


}