<?php
/**
 * Created by
 * User: IU Communications
 * Date: 6/2/16
 */

namespace StudentCentralApp\Jobs;

// Faculty and department description


class ImportClassDescriptions extends Job
{

    protected $destinationTable = 'class_description';

    const GetClassDescriptionQuery = "select
GetMostRecentUpdate.ACAD_TERM_CD,GetMostRecentUpdate.CLS_NBR,
GetMostRecentUpdate.CRS_ID,
GetCourseDescription.CLS_FAC_DTL_LAST_UPDT_DT,

REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(GetCourseDescription.CRS_DESCR,
Chr(225),'a'),Chr(231),'c'),Chr(233),'e'),Chr(239),'i'),Chr(241),'n'),Chr(243),'o'),Chr(250),'u') CRS_DESCR ,



REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(GetCourseDescription.CLS_FAC_DTL_LONG_DESC,
Chr(225),'a'),Chr(231),'c'),Chr(233),'e'),Chr(239),'i'),Chr(241),'n'),Chr(243),'o'),Chr(250),'u') CLS_FAC_DTL_LONG_DESC ,

REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(GetCourseDescription.CLS_FAC_DTL_IU_DEPT_DESC,
Chr(225),'a'),Chr(231),'c'),Chr(233),'e'),Chr(239),'i'),Chr(241),'n'),Chr(243),'o'),Chr(250),'u') CLS_FAC_DTL_IU_DEPT_DESC


  from
(SELECT DISTINCT
DSS_RDS.SR_IU_CLS_FAC_DTL_GT.CRS_ID, CLS_NBR,
CLS_FAC_DTL_LAST_UPDT_DT, CRS_DESCR,
CLS_FAC_DTL_LONG_DESC, ACAD_TERM_CD,
CLS_FAC_DTL_IU_DEPT_DESC
FROM DSS_RDS.SR_IU_CLS_FAC_DTL_GT
WHERE ACAD_TERM_CD =  @acad_term_str
and INST_CD = '@inst_cd'
ORDER BY CLS_NBR)GetCourseDescription
,

(SELECT ACAD_TERM_CD, CRS_ID, CLS_NBR, Max(CLS_FAC_DTL_LAST_UPDT_DT) AS MaxOfCLS_FAC_DTL_LAST_UPDT_DT
FROM DSS_RDS.SR_IU_CLS_FAC_DTL_GT
WHERE ACAD_TERM_CD =  @acad_term_str
and INST_CD = '@inst_cd'
GROUP BY ACAD_TERM_CD, CRS_ID, CLS_NBR)  GetMostRecentUpdate where
GetMostRecentUpdate.CLS_NBR = GetCourseDescription.CLS_NBR and
GetMostRecentUpdate.ACAD_TERM_CD = GetCourseDescription.ACAD_TERM_CD
and
GetMostRecentUpdate.MaxOfCLS_FAC_DTL_LAST_UPDT_DT =
GetCourseDescription.CLS_FAC_DTL_LAST_UPDT_DT

";

    public function __construct()
    {
        parent::__construct("ClassDescriptions");
    }

    protected function run()
    {
        // truncate
        $this->dbextensionsObj->truncate($this->destinationTable);
        $acad_terms = $this->getAcadTerms();
        $chunksize = $this->chunk_size;
        collect($acad_terms)->each(function ($acadTerm) use ($chunksize) {

            $query = str_replace($this->inst_str,$this->getInstitutionCD(),
                str_replace($this->acad_term_str, $acadTerm,
                    self::GetClassDescriptionQuery));

            $data = collect(\DB::connection("oracle")
                ->select($query));


            $this->dbextensionsObj->insert($this->destinationTable, $data,
                function ($desc) {
                    $description = ['acad_term_cd'=>$desc->acad_term_cd,
                        'cls_nbr'=> $desc->cls_nbr,
                        'crs_id'=>$desc->crs_id,
                        'cls_fac_dtl_last_updt_dt'=>$desc->cls_fac_dtl_last_updt_dt,
                        'crs_desc'=>$desc->crs_descr
                    ];

                    // Description - iu-dept  description if present otherwise -
                    if($desc->cls_fac_dtl_long_desc=="")
                        $description['cls_long_desc']=$desc->cls_fac_dtl_iu_dept_desc;
                    else // description - faculty
                        $description['cls_long_desc']=$desc->cls_fac_dtl_long_desc;

                    return $description;
                });

        });



    }



}