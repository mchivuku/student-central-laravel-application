<?php
/**
 * Created by
 * User: IU Communications
 * Date: 5/18/16
 */

namespace StudentCentralCourseBrowser\Jobs;

class GetTermDescr extends Job
{
    /**
     * Change all terms need to be retrieved. Query retrieves terms after Summer 2015
     */
    const GetTermDescrQuery = "select STRM, DESCR from DSS_RDS.SR_TERM_CD_V where STRM>4155 ";

    protected $destinationTable = 'term_descr';

    /**
     * GetTermDescr constructor.
     */
    public function __construct()
    {
        parent::__construct('GetTermDescr');
    }

    /**
     * Term Description
     */
    protected function run()
    {

        // truncate
        $this->dbextensionsObj->truncate($this->destinationTable);

        $data = collect(\DB::connection("oracle")->select(self::GetTermDescrQuery));

        $this->dbextensionsObj->insert($this->destinationTable, $data,
            function ($item) {
                return [
                    'term' => $item->strm,
                    'description' => $item->descr,
                ];
            });


    }
}