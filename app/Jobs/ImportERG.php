<?php
/**
 * Created by
 * User: IU Communications
 * Date: 5/31/16
 */


namespace StudentCentralApp\Jobs;


/**
 * Class GetERG
 *
 *
! Take the passed restriction code and look it up in the Requirement
! Group table where Line Type = COND. If a record is found, report it
! back to the calling procedure.
 *
 * @package StudentCentralApp\Jobs
 */
class ImportERG extends Job
{

    protected $destinationTable = 'requirement_group';

    const GetERGQuery = "select distinct O.ACAD_RQGRP_CD,
O.ACAD_RQMT_LN_TYP_CD FROM
  DSS_RDS.SR_RQGRP_GT O
WHERE O.ACAD_RQMT_LN_TYP_CD = 'COND' AND INST_CD = '@inst_cd'";


    /**
     * Get Class Notes constructor.
     */
    public function __construct()
    {
        parent::__construct('GetERG');
    }

    /**
     * Term Description
     */
    protected function run()
    {

        $inst_cd = $this->getInstitutionCD();

        // truncate
        $this->dbextensionsObj->truncate($this->destinationTable);

        /** small dataset - get everything and insert in chunks */
        $data = collect(\DB::connection("oracle")->select(str_replace($this->inst_str,$inst_cd,
            self::GetERGQuery)));


        $this->dbextensionsObj->insert($this->destinationTable, $data,
            function ($item) {
                return [
                    'acad_rqgrp_cd' => $item->acad_rqgrp_cd,
                    'acad_rqmt_ln_typ_cd' => $item->acad_rqmt_ln_typ_cd
                ];
            });


    }

}