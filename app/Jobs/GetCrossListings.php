<?php
/**
 * Created by
 * User: IU Communications
 * Date: 6/1/16
 */


namespace StudentCentralCourseBrowser\Jobs;


/**
 * Class GetCombinedSectionInfo
 * Get information of the combined section
 * @package StudentCentralCourseBrowser\Jobs
 */
class GetCrossListings extends Job
{

    protected $destinationTable = 'class_crosslisted';

    const CrossListedCoursesQuery = "

select J.CRS_ID,
J.CRSOFR_NBR,
J.ACAD_TERM_CD,
J.CLS_SESN_CD,
J.CLS_SECT_CD,
J.CRS_ATTRIB_CD,
J.CRS_ATTRIB_VAL_CD,
K.CRS_ID,
K.CRSOFR_NBR,
K.ACAD_TERM_CD,
K.CLS_SESN_CD,
K.CLS_SECT_CD,
K.INST_CD,
K.CRS_SUBJ_DEPT_CD,
K.CRS_SUBJ_CD,
K.CRS_SUBJ_DESC,
K.CRS_SUBJ_LTR_CD,
K.CRS_CATLG_NBR,
K.CRS_DESC,
K.CRS_TPC_ID,
K.CRS_TPC_DESC,
L.CRS_ID,
L.CRSOFR_NBR,
L.ACAD_TERM_CD,
L.CLS_SESN_CD,
L.CLS_ASSCT_NBR,
L.CLS_ASSCT_MIN_UNT_NBR,
L.CLS_ASSCT_MAX_UNT_NBR

FROM
DSS_RDS.SR_CLS_ATTRIB_GT J,
DSS_RDS.SR_CLS_GT K, DSS_RDS.SR_CLS_ASSOC_GT L
WHERE CRS_ATTRIB_CD = 'CRLT'
AND K.INST_CD = '@inst_cd'
AND J.CRS_ID = K.CRS_ID
AND J.CRSOFR_NBR = K.CRSOFR_NBR
AND J.ACAD_TERM_CD = K.ACAD_TERM_CD
AND J.CLS_SESN_CD = K.CLS_SESN_CD
AND J.CLS_SECT_CD = K.CLS_SECT_CD
AND J.CRS_ID = L.CRS_ID
AND J.CRSOFR_NBR = L.CRSOFR_NBR
AND J.ACAD_TERM_CD = L.ACAD_TERM_CD
AND J.CLS_SESN_CD = L.CLS_SESN_CD
AND K.CLS_ASSCT_NBR = L.CLS_ASSCT_NBR
AND K.ACAD_TERM_CD = @acad_term_str
ORDER BY K.CRS_SUBJ_DEPT_CD,
K.CRS_CATLG_NBR, K.CRS_SUBJ_LTR_CD
";



    public function __construct()
    {
        parent::__construct('GetCrossListings');
    }

    protected function run()
    {
        // truncate  - table
        $this->dbextensionsObj->truncate($this->destinationTable);
        collect($this->getAcadTerms())->each(function($term){


            $data = collect(\DB::connection("oracle")
                ->select(str_replace($this->inst_str,
                    $this->getInstitutionCD(),
                    str_replace($this->acad_term_str,$term,
                        self::CrossListedCoursesQuery))));

            $this->dbextensionsObj->insert($this->destinationTable, $data,
                function ($item) {

                    return [
                        'crs_id'=>$item->crs_id,
                        'acad_term_cd' => $item->acad_term_cd,
                        'cls_sesn_cd' => $item->cls_sesn_cd,
                        'cls_sect_cd'=>$item->cls_sect_cd,
                        'crs_attrib_cd' => $item->crs_attrib_cd,
                        'crs_attrib_val_cd' => $item->crs_attrib_val_cd,
                        'crsofr_nbr' => $item->crsofr_nbr,
                        'crs_subj_dept_cd'=>$item->crs_subj_dept_cd,
                        'crs_subj_cd'=>$item->crs_subj_cd,
                        'crs_subj_desc'=>$item->crs_subj_desc,
                        'crs_subj_ltr_cd'=>$item->crs_subj_ltr_cd,
                        'crs_catlg_nbr'=>$item->crs_catlg_nbr,
                        'crs_desc'=>$item->crs_desc,
                        'crs_tpc_id'=>$item->crs_tpc_id,
                        'crs_tpc_desc'=>$item->crs_tpc_desc,
                        'cls_assct_nbr'=>$item->cls_assct_nbr,
                        'cls_assct_min_unt_nbr'=>$item->cls_assct_min_unt_nbr,
                        'cls_assct_max_unt_nbr'=>$item->cls_assct_max_unt_nbr
                    ];
                });

        });



    }
}