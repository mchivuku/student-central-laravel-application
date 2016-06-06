<?php
/**
 * Created by
 * User: IU Communications
 * Date: 6/3/16
 */

namespace StudentCentralCourseBrowser\Models;


class TermDepartment extends BaseModel
{

    protected $table='term_department';


    public function scopeAcadTerm($query,$term)
    {
        return $query->where('acad_term_cd', '=', $term);
    }

}