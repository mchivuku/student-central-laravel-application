<?php
/**
 * Created by
 * User: IU Communications
 * Date: 6/3/16
 */

namespace StudentCentralCourseBrowser\Models;


class ClassNotes extends BaseModel
{

    protected $table='class_notes';

    public function scopeClassNotesA($query,$cls_key){
        return $query->where('cls_key','=',$cls_key)->where('cls_nts_prnt_at_cd','A')->orderBy('cls_nts_seq_nbr');

    }

    public function scopeClassNotesB($query,$cls_key){
        return $query
            ->where('cls_key','=',$cls_key)
            ->where('cls_nts_prnt_at_cd','B')
            ->orderBy('cls_nts_seq_nbr');

    }
    

}