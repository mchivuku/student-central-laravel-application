<?php
/**
 * Created by
 * User: IU Communications
 * Date: 6/22/16
 */

namespace StudentCentralApp\Jobs;

class ImportNonStandardSessionDates extends Job
{
    /**
     * Change all terms need to be retrieved. Query retrieves terms after Summer 2015
     */
    const NonStandardSessionDatesQuery = "   SELECT A.ACAD_TERM_CD,
         A.CRS_SUBJ_CD,
         A.CRS_CATLG_NBR,
         A.CLS_NBR,
         A.CRS_DESC,
         to_char(A.CLS_STRT_DT, 'YYYY-MM-DD')CLS_STRT_DT ,
         to_char(A.CLS_END_DT, 'YYYY-MM-DD') CLS_END_DT,
         to_char((CASE WHEN A.CLS_END_DT - A.CLS_STRT_DT < 13 THEN A.CLS_STRT_DT WHEN
         A.CLS_END_DT - A.CLS_STRT_DT < 34 THEN A.CLS_STRT_DT + 1 ELSE A.CLS_STRT_DT + 6 END),'YYYY-MM-DD')as REFUND_100,
         to_char((CASE WHEN substr(A.ACAD_TERM_CD,4,1) = '5' THEN
         (TRUNC(.5625 * (A.CLS_END_DT - A.CLS_STRT_DT))) + A.CLS_STRT_DT
         ELSE
              CASE WHEN A.CLS_STRT_DT <= (A.ACAD_TERM_BEG_DT + 6) AND A.CLS_STRT_DT >= A.ACAD_TERM_BEG_DT AND A.CLS_END_DT - A.CLS_STRT_DT >= 63
                   THEN A.ACAD_TERM_BEG_DT + 62
                   ELSE (TRUNC(.5625 * (A.CLS_END_DT - A.CLS_STRT_DT)) + A.CLS_STRT_DT) END
         END),'YYYY-MM-DD') as AUTO_W,
         A.CRS_SUBJ_DEPT_CD,
          A.CRS_SUBJ_DESC,
         A.ACAD_GRP_CD,
         A.ACAD_GRP_DESC,
         A.ACAD_TERM_DESC,
         to_char((CASE WHEN A.CLS_END_DT - A.CLS_STRT_DT < 62 THEN null ELSE A.CLS_STRT_DT + 13 END),'YYYY-MM-DD') as REFUND_75,
         to_char((CASE WHEN A.CLS_END_DT - A.CLS_STRT_DT < 13 THEN A.CLS_STRT_DT + 1 WHEN A.CLS_END_DT - A.CLS_STRT_DT < 34 THEN A.CLS_STRT_DT + 3
         WHEN A.CLS_END_DT - A.CLS_STRT_DT < 62 THEN A.CLS_STRT_DT + 13 ELSE A.CLS_STRT_DT + 20 END),'YYYY-MM-DD')REFUND_50,
         to_char((CASE WHEN A.CLS_END_DT - A.CLS_STRT_DT < 62 THEN null ELSE A.CLS_STRT_DT + 27 END),'YYYY-MM-DD') as REFUND_25
         ,
         /* Added P/F calculation (next business day if the P/F date falls on Sat or Sun) per Lisa Scully on 08/11/2015 */
         to_char((CASE WHEN to_char((TRUNC(.253 * (A.CLS_END_DT - A.CLS_STRT_DT)) + A.CLS_STRT_DT), 'DY', 'NLS_DATE_LANGUAGE=English') in ('SAT')
              THEN (TRUNC(.253 * (A.CLS_END_DT - A.CLS_STRT_DT)) + A.CLS_STRT_DT)+2
              WHEN to_char((TRUNC(.253 * (A.CLS_END_DT - A.CLS_STRT_DT)) + A.CLS_STRT_DT), 'DY', 'NLS_DATE_LANGUAGE=English') in ('SUN')
              THEN (TRUNC(.253 * (A.CLS_END_DT - A.CLS_STRT_DT)) + A.CLS_STRT_DT)+1
         ELSE (TRUNC(.253 * (A.CLS_END_DT - A.CLS_STRT_DT)) + A.CLS_STRT_DT)
         END),'YYYY-MM-DD')PASS_FAIL
  FROM DSS_RDS.SR_CLS_GT A
  WHERE A.INST_CD = 'IUBLA'
  AND A.ACAD_TERM_CD in (@acad_term_str)
  AND A.CLS_SESN_CD IN ('NON','NS1','NS2','INT')
  -- removed OPT/LAW/BUKD restrictions 05/15/2014 per Kathy Shields
  --AND A.CRS_SUBJ_CD NOT LIKE ''OPT%''
  --AND A.CRS_SUBJ_CD NOT LIKE ''LAW%''
  --AND A.CRS_SUBJ_CD NOT LIKE ''BUKD%''
  ORDER BY A.CRS_SUBJ_CD, A.CRS_CATLG_NBR, A.CLS_NBR ";

    protected $destinationTable = 'non_standard_course_dates';

    /**
     *  ImportNonSessionDatesData - constructor
     */
    public function __construct()
    {
        parent::__construct('ImportNonSessionDatesData');
    }

    /**
     * Term Description
     */
    protected function run()
    {

        // truncate
        $this->dbextensionsObj->truncate($this->destinationTable);

        $acad_terms = implode(",",$this->getAcadTerms());

        $data = collect(\DB::connection("oracle")
                        ->select(str_replace($this->acad_term_str,$acad_terms,self::NonStandardSessionDatesQuery)));

        $this->dbextensionsObj->insert($this->destinationTable, $data,
            function ($item) {
                return [
                    'acad_term_cd' => $item->acad_term_cd,
                    'crs_subj_cd' => $item->crs_subj_cd,
                    'crs_subj_desc'=>$item->crs_subj_desc,
                    'crs_desc'=>$item->crs_desc,
                    'crs_catlg_nbr'=>$item->crs_catlg_nbr,
                    'cls_nbr'=>$item->cls_nbr,
                    'cls_strt_dt'=>$item->cls_strt_dt,
                    'cls_end_dt'=>$item->cls_end_dt,
                    'refund_100'=>$item->refund_100,
                    'auto_w'=>$item->auto_w,
                    'crs_subj_dept_cd'=>$item->crs_subj_dept_cd,
                    'acad_grp_cd'=>$item->acad_grp_cd,
                    'acad_grp_desc'=>$item->acad_grp_desc,
                    'acad_term_desc'=>$item->acad_term_desc,
                    'refund_75'=>$item->refund_75,
                    'refund_50'=>$item->refund_50,
                    'refund_25'=>$item->refund_25,
                    'pass_fail'=>$item->pass_fail
                ];
            });


    }
}