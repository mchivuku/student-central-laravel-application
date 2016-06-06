<?php
/**
 * Created by
 * User: IU Communications
 * Date: 6/3/16
 */

namespace StudentCentralCourseBrowser\Models;


class TermDescription extends BaseModel
{

    protected $table='term_descr';

    public function scopeAcadTerm($query,$term)
    {
        return $query->where('term', '=', $term);
    }
}