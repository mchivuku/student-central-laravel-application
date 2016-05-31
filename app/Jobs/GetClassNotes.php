<?php
/**
 * Created by
 * User: IU Communications
 * Date: 5/29/16
 */

namespace StudentCentralCourseBrowser\Jobs;


class GetClassNotes extends Job
{

    protected $destinationTable = 'class_notes';

    /**
     * Get Class Notes constructor.
     */
    public function __construct()
    {
        parent::__construct('Get Class Notes');
    }

    /**
     * Term Description
     */
    protected function run()
    {

        $inst_cd = $this->getInstitutionCD();
        $acad_term = implode(", ",$this->getAcadTerms());

        $query = "select rownum as r, H.CLS_KEY, H.CLS_NTS_SEQ_NBR,H.CLS_NTS_PRNT_AT_CD,
                 H.CLS_NTS_NBR, REPLACE(H.CLS_NTS_NBR_LONG_DESC,CHR(13)||CHR(10),' ')
                 AS NTS_NBR_LONG_DESC  ";
        // truncate
        $this->dbextensionsObj->truncate($this->destinationTable);

        $data = collect(\DB::connection("oracle")->select($query));

        $this->dbextensionsObj->insert($this->destinationTable, $data,
            function ($item) {
                return [
                    'acad_grp_cd' => $item->acad_grp_cd,
                    'acad_term_cd' => $item->acad_term_cd,
                    'crs_subj_cd'=>$item->crs_subj_cd,
                    'stu_glbl_nts_prnt_at_cd'=>$item->stu_glbl_nts_prnt_at_cd,
                    'glbl_nts_long_desc_nsa'=>$item->glbl_nts_long_desc_nsa
                ];
            });

    }

}