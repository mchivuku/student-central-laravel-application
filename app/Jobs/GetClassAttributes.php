<?php
/**
 * Created by
 * User: IU Communications
 * Date: 6/1/16
 */

namespace StudentCentralCourseBrowser\Jobs;


use Symfony\Component\VarDumper\Cloner\Data;

class GetClassAttributes extends Job
{
    /**
     * Change if all institutions data is required.
     */
    const GetInstDescrQuery = "SELECT INSTITUTION, DESCR, DESCRSHORT,DESCRFORMAL, trim(ADDRESS1)||trim(ADDRESS2)||trim(ADDRESS3)|| ', '|| CITY \"Address\" FROM DSS.PSE_INST_V  WHERE INSTITUTION= 'IUBLA'";
    protected $destinationTable = 'inst_descr';

    public function __construct()
    {
        parent::__construct('GetInstDescr');
    }

    protected function run()
    {

        // truncate
        $this->dbextensionsObj->truncate($this->destinationTable);

        $data = collect(\DB::connection("oracle")
            ->select(self::GetInstDescrQuery));

        $this->dbextensionsObj->insert($this->destinationTable, $data,
            function ($item) {
                return [
                    'code' => $item->institution,
                    'description' => $item->descr,
                    'short_description' => $item->descrshort,
                    'long_description' => $item->descrformal,
                    'address' => $item->address
                ];
            });


    }
}