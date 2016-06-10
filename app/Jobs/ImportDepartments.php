<?php
/**
 * Created by
 * User: IU Communications
 * Date: 6/3/16
 */

namespace StudentCentralCourseBrowser\Jobs;


use Symfony\Component\VarDumper\Cloner\Data;

class ImportDepartments extends Job
{

    const DepartmentsQuery = "
            select
        distinct
        BB.ACAD_TERM_CD,
        BB.ACAD_GRP_CD,
        BB.CRS_SUBJ_DEPT_CD,
        BB.CRS_SUBJ_DESC
        FROM DSS_RDS.SR_CLS_GT BB
        WHERE 1=1
        and BB.INST_CD= '@inst_cd'
        and BB.ACAD_TERM_CD in (@acad_term_str)";

    protected $destinationTable = 'term_department';

    public function __construct()
    {
        parent::__construct('GetDepartments');
    }

    protected function run()
    {

        // truncate
        $this->dbextensionsObj->truncate($this->destinationTable);

        $data = collect(\DB::connection("oracle")
            ->select(str_replace($this->inst_str, $this->getInstitutionCD(),
                (str_replace($this->acad_term_str, implode(",", $this->getAcadTerms()),
                    self::DepartmentsQuery)))));

        $this->dbextensionsObj->insert($this->destinationTable,
            $data,
            function ($item) {
                return [
                    'acad_term_cd' => $item->acad_term_cd,
                    'acad_grp_cd' => $item->acad_grp_cd,
                    'crs_subj_dept_cd' => $item->crs_subj_dept_cd,
                    'crs_subj_desc' => $item->crs_subj_desc
                ];
            });

    }

}
