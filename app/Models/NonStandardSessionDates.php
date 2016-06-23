<?php
/**
 * Created by
 * User: IU Communications
 * Date: 6/22/16
 */

namespace StudentCentralApp\Models;


class NonStandardSessionDates extends BaseModel
{
    protected $table="non_standard_course_dates";

    /** Acad term search */
    public function scopeAcadTerm($query,$acadTerm)
    {
        if(isset($acadTerm)){
            return $query->where('acad_term_cd', '=', $acadTerm);
        }else{
            return $query;
        }

    }

    /**
     * School search
     * @param $query
     * @param $acadTerm
     * @return mixed
     */
    public function scopeSchool($query,$school)
    {
        if(isset($school)){
            return $query->where('acad_grp_cd', '=', $school);
        }else{
            return $query;
        }

    }

    public function scopeDept($query,$dept)
    {
        if(isset($school)){
            return $query->where('crs_subj_dept_cd', 'like', $dept);
        }else{
            return $query;
        }

    }
}