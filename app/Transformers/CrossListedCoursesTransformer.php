<?php
/**
 * Created by
 * User: IU Communications
 * Date: 6/9/16
 */

namespace StudentCentralApp\Transformers;

use League\Fractal\TransformerAbstract;

class CrossListedCoursesTransformer extends TransformerAbstract
{

    protected $classTransformer ;
    public function __construct($params = [])
    {
        $this->params = $params;

        $this->base_transformer = new BaseTransformer();

    }

    /**
     * Course
     * @param $course
     */
    public function transform($term)
    {

         //** Set Course attributes, loop through
         //individual classes to construct classes array */
        return [
            'term' => isset($term['term']) ?
                $term['term'] : "",
            'cross_listings' => collect($term['courses'])
                ->map(function ($dept) {

                    return

                    ['term'=>$dept['term'],
                        'department'=>$dept['department'],

                    'courses'=>
                          collect($dept['courses'])->map(function ($courses) {
                                return [
                                    'offered_by'=>$courses['department'],
                                    'crs_subj_line'=>
                                        $courses['crlt_crs_subj_line'],
                                    'courses'=>
                                        $courses['courses']

                                ];
                            })
                    ];

                })
        ];


    }

}