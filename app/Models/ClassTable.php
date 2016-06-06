<?php

namespace StudentCentralCourseBrowser\Models;

/**
 * Created by
 * User: IU Communications
 * Date: 6/3/16
 */

class ClassTable extends BaseModel
{

    protected $table="class";

    public function scopeAcadTerm($query,$term)
    {
        return $query->where('acad_term_cd', '=', $term);
    }

    public function scopeDept($query,$subject_dept)
    {
        return $query->where('crs_subj_dept_cd', '=', $subject_dept);
    }

    public function scopeCustomOrderBy($query,$term){
//        $inst_cd = 'IUBLA';

        if(substr($term,5,1)==5){

            return $query->orderBy('acad_grp_cd','crs_subj_dept_cd')
                    ->orderBy('substr(crs_attrib_cd, 1, 2)','desc')
                    ->orderBy('substr(crs_attrib_cd,1,9)')
                    ->orderBy('crs_catlg_nbr')
                    ->orderBy('crs_subj_ltr_cd')
                    ->orderBy('cls_assct_nbr')
                    ->orderBy('cls_drvd_grd_cmpnt','desc')
                    ->orderBy('crs_cmpnt_cd')
                    ->orderBy('cls_mtg_strt_tm')
                    ->orderBy('cls_nbr')
                   ->orderBy('cls_drvd_sort_cd')
                   ->orderBy('cls_mtg_ptrn_nbr')
                   ->orderBy('cls_instr_asgn_seq_nbr');

        }else{


            //ORDER BY A.ACAD_GRP_CD, A.CRS_SUBJ_DEPT_CD,
            // SUBSTR(D.CRS_ATTRIB_VAL_CD, 1, 2) DESC,
            // D.CRS_ATTRIB_VAL_CD, A.CRS_CATLG_NBR,
            // A.CRS_SUBJ_LTR_CD,
            // A.CLS_ASSCT_NBR,
            // F.CLS_DRVD_GRD_CMPNT_IND DESC,
            // A.CRS_CMPNT_CD, G.CLS_DRVD_SORT_CD,
            // G.CLS_MTG_STRT_TM,
            // A.CLS_NBR,
            // B.CLS_DRVD_SORT_CD,
            // B.CLS_MTG_STRT_TM,
            // B.CLS_MTG_PTRN_NBR,
            // C.CLS_INSTR_ASGN_SEQ_NBR'

            return $query->orderBy('acad_grp_cd','crs_subj_dept_cd')
                ->orderByRaw('substr(crs_attrib_val_cd, 1, 2) desc')
                ->orderBy('crs_attrib_val_cd')
                ->orderBy('crs_catlg_nbr')
                ->orderBy('crs_subj_ltr_cd')
                ->orderBy('cls_assct_nbr')
                ->orderBy('cls_drvd_grd_cmpnt_ind','desc')
                ->orderBy('crs_cmpnt_cd')
                ->orderBy('cls_drvd_sort_cd')
                ->orderBy('cls_mtg_strt_tm')
                ->orderBy('cls_nbr')
                ->orderBy('cls_drvd_sort_cd')
                ->orderBy('cls_mtg_ptrn_nbr')
                ->orderBy('cls_instr_asgn_seq_nbr');

        }


    }
}

