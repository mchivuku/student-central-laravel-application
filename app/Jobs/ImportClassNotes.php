<?php
/**
 * Created by
 * User: IU Communications
 * Date: 5/29/16
 */

namespace StudentCentralApp\Jobs;


class ImportClassNotes extends Job
{

    protected $destinationTable = 'class_notes';

    /**
     * Get Class Notes constructor.
     */
    public function __construct()
    {
        parent::__construct('Import Class Notes');
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

            $chunksize = 400;

            $query = " select rownum as rn, H.CLS_KEY, H.CLS_NTS_SEQ_NBR,
                  H.CLS_NTS_PRNT_AT_CD,H.CLS_NTS_NBR,
                  REPLACE(H.CLS_NTS_NBR_LONG_DESC,CHR(13)||CHR(10),' ')
                  AS NTS_NBR_LONG_DESC,
                  REPLACE(H.CLS_NTS_LONG_DESC,CHR(13)||CHR(10),' ') AS NTS_LONG_DESC
                  FROM
                  DSS_RDS.SR_CLS_NTS_GT H,
                  DSS_RDS.SR_CLS_GT N
                  WHERE 1=1
                  and H.CLS_KEY= N.CLS_KEY and N.ACAD_TERM_CD = $term and
                   N.INST_CD='" . $inst_cd . "' ";


            $this->dbextensionsObj->readDataInChunksDSSPRODAndImport($query, function ($data) use ($chunksize) {
                $this->dbextensionsObj->insert($this->destinationTable, collect($data),

                    function ($item) {

                        return [
                            'cls_key' => $item['cls_key'],
                            'cls_nts_seq_nbr' => $item['cls_nts_seq_nbr'],
                            'cls_nts_prnt_at_cd' => $item['cls_nts_prnt_at_cd'],
                            'cls_nts_nbr' => $item['cls_nts_nbr'],
                            'nts_nbr_long_desc' => $item['nts_nbr_long_desc'],
                            'nts_long_desc' =>$item['nts_long_desc']
                        ];

                    }, $chunksize);

            }, $chunksize);


        });


    }

}