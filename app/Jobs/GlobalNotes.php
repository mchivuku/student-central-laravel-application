<?php
/**
 * Created by
 * User: IU Communications
 * Date: 5/29/16
 */

namespace StudentCentralCourseBrowser\Jobs;


class GlobalNotes extends Job
{

    protected $destinationTable = 'global_notes';

    /**
     * GetTermDescr constructor.
     */
    public function __construct()
    {
        parent::__construct('GlobalNotes');
    }

    /**
     * Term Description
     */
    protected function run()
    {

        $inst_cd = $this->getInstitutionCD();
        $acad_term = collect($this->getAcadTerms())->join(",");

        $query = "Select distinct
                    M.ACAD_GRP_CD,
                    M.ACAD_TERM_CD,
                    M.CRS_SUBJ_CD,
                    M.STU_GLBL_NTS_PRNT_AT_CD,
                    REPLACE(M.STU_GLBL_NTS_LONG_DESC,CHR(13)||CHR(10),' ') AS
                    GLBL_NTS_LONG_DESC_NSA
                    FROM DSS_RDS.SR_GLBL_NTS_GT M
                    WHERE
                    M.INST_CD = $inst_cd
                    AND M.ACAD_TERM_CD IN ($acad_term)";
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