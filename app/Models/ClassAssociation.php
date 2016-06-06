<?php
/**
 * Created by
 * User: IU Communications
 * Date: 6/3/16
 */

namespace StudentCentralCourseBrowser\Models;


class ClassAssociation extends BaseModel
{

    protected $table="class_association";


    public function scopeLookupClassAssociations
        ($query,$crs_id,$crsofr_nbr,$acad_term,$cls_sesn_cd,$cls_assct_nbr){

        return $query->where('crs_id','=',$crs_id)
                     ->where('crsofr_nbr','=',$crsofr_nbr)
                     ->where('acad_term_cd','=',$acad_term)
                     ->where('cls_sesn_cd','=',$cls_sesn_cd)
                     ->where('cls_assct_nbr','=',$cls_assct_nbr);


    }
}