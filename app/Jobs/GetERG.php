<?php
/**
 * Created by
 * User: IU Communications
 * Date: 5/31/16
 */


namespace StudentCentralCourseBrowser\Jobs;


/**
 * Class GetERG
 *
 *
! Take the passed restriction code and look it up in the Requirement
! Group table where Line Type = COND. If a record is found, report it
! back to the calling procedure.
 *
 * @package StudentCentralCourseBrowser\Jobs
 */
class GetERG extends Job
{

    protected $destinationTable = 'class_ERG';

    const GetERGQuery = "select distinct O.ACAD_RQGRP_CD,
O.ACAD_RQMT_LN_TYP_CD FROM
FROM DSS_RDS.SR_RQGRP_GT O
WHERE O.ACAD_RQMT_LN_TYP_CD = 'COND'";
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


        collect($this->getAcadTerms())->each(function ($term) use ($inst_cd) {






        });


    }

}