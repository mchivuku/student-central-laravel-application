<?php
/**
 * Created by
 * User: IU Communications
 * Date: 6/3/16
 */

namespace StudentCentralApp\Models;


class TermDepartment extends BaseModel
{

    protected $table='term_department';


    protected $crsSubjDesc;
    public function scopeAcadTerm($query,$term)
    {
        return $query->where('acad_term_cd', '=', $term);
    }

    public function getCrsSubjDesc(){
        return $this->crs_subj_desc. "(". $this->crs_subj_dept_cd. ")";
    }
}