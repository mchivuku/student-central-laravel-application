<?php
/**
 * Created by
 * User: IU Communications
 * Date: 6/1/16
 */


namespace StudentCentralApp\Jobs;


/**
 * Class GetERG2
 *
 *
! Check the Reserve Capacity table if no departmental consent or other
! restriction data found yet for this class section.
 *
 * @package StudentCentralApp\Jobs
 */
class ImportERG2 extends Job
{

    protected $destinationTable = 'reservation_capacity';

    const GetERG2Query = "select distinct P.CLS_KEY,
                         P.CLS_RQMT_GRP_CD
                         FROM DSS_RDS.SR_CLS_RSV_CPCTY_GT P";


    /**
     * Get Class Notes constructor.
     */
    public function __construct()
    {
        parent::__construct('GetERG2');
    }

    /**
     * Term Description
     */
    protected function run()
    {

        // truncate
        $this->dbextensionsObj->truncate($this->destinationTable);

        /** Nothing in the where clause - get the whole table */
        $data = collect(\DB::connection("oracle")
            ->select(self::GetERG2Query));


        $this->dbextensionsObj->insert($this->destinationTable,
            $data,
            function ($item) {
                return [
                    'cls_key' => $item->cls_key,
                    'cls_rqmt_grp_cd' => $item->cls_rqmt_grp_cd
                ];
            });


    }

}