<?php

namespace StudentCentralApp\Models;

/**
 * Created by
 * User: IU Communications
 * Date: 6/3/16
 */

class ClassTable extends BaseModel
{

    protected $table = "class";

    public function scopeAcadTerm($query, $term)
    {
        return $query->where('acad_term_cd', '=', $term);
    }

    public function scopeDept($query, $subject_dept)
    {
        return $query->where('crs_subj_dept_cd', '=', $subject_dept);
    }

    public function scopeCustomOrderBy($query, $term)
    {


        if (substr($term, 5, 1) == 5)
            return $query->orderByRaw('ACAD_GRP_CD, CRS_SUBJ_CD, CLS_DRVD_SESN_CD,  SUBSTR(CRS_ATTRIB_VAL_CD, 1, 2) DESC, CRS_ATTRIB_VAL_CD, CRS_CATLG_NBR, CRS_SUBJ_LTR_CD, CLS_ASSCT_NBR, CLS_DRVD_GRD_CMPNT_IND DESC,
 CRS_CMPNT_CD, CLS_DRVD_SORT_CD, CLS_MTG_STRT_TM_sec, CLS_NBR, CLS_DRVD_SORT_CD, CLS_MTG_STRT_TM_sec, CLS_MTG_PTRN_NBR, CLS_INSTR_ASGN_SEQ_NBR
                        ');
        return $query->orderByRaw('ACAD_GRP_CD,  CRS_SUBJ_CD,CRS_CATLG_NBR, CRS_SUBJ_LTR_CD, CLS_ASSCT_NBR, CLS_DRVD_GRD_CMPNT_IND DESC,
                          CRS_CMPNT_CD, CLS_DRVD_SORT_CD, cls_mtg_strt_tm_sec, CLS_NBR, CLS_DRVD_SORT_CD, cls_mtg_strt_tm_sec, CLS_MTG_PTRN_NBR, CLS_INSTR_ASGN_SEQ_NBR
	           ');

    }

}

