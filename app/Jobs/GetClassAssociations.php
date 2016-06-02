<?php
/**
 * Created by
 * User: IU Communications
 * Date: 5/17/16
 */

namespace StudentCentralCourseBrowser\Jobs;


use Symfony\Component\VarDumper\Cloner\Data;

class GetClassAssociations extends Job
{
    /**
     * Get all class associations from the term
     */
    const GetClassAssociationsQuery =
        "

        select distinct
        rownum as rn,
        Q.CRS_ID,
        Q.CRSOFR_NBR,
        Q.ACAD_TERM_CD,
        Q.CLS_SESN_CD,
        Q.CLS_ASSCT_NBR,
        R.CLS_NBR

        FROM  DSS_RDS.SR_CLS_ASSOC_GT Q, DSS_RDS.SR_CLS_GT R
        WHERE 1=1
        AND Q.ACAD_TERM_CD = @acad_term_str
        AND Q.CRS_ID = R.CRS_ID
        AND Q.CRSOFR_NBR = R.CRSOFR_NBR
        AND Q.ACAD_TERM_CD = R.ACAD_TERM_CD
        AND Q.CLS_SESN_CD = R.CLS_SESN_CD
        AND Q.CLS_ASSCT_NBR = R.CLS_ASSCT_NBR AND R.INST_CD = '@inst_cd'";

    protected $destinationTable = 'class_associations';

    public function __construct()
    {
        parent::__construct('GetClassAssociations');
    }

    protected function run()
    {

        // truncate
        $this->dbextensionsObj->truncate($this->destinationTable);

        $acad_terms = $this->getAcadTerms();
        $chunksize = $this->chunk_size;

        collect($acad_terms)->each(function ($acadTerm) use ($chunksize) {

            $query = str_replace($this->inst_str,$this->getInstitutionCD(),
                str_replace($this->acad_term_str, $acadTerm, self::GetClassAssociationsQuery));

            /** Read data from table */
            $this->dbextensionsObj->readDataInChunksDSSPRODAndImport($query,
                function ($data) use ($chunksize) {
                    $insert_rows="";
                    foreach($data as $item){
                        $insert_rows[]= [
                            'crs_id'=>$item['crs_id'],
                            'crsofr_nbr'=>$item['crsofr_nbr'],
                            'acad_term_cd'=>$item['acad_term_cd'],
                            'cls_sesn_cd'=>$item['cls_sesn_cd'],
                            'cls_assct_nbr'=>$item['cls_assct_nbr'],
                            'cls_nbr'=>$item['cls_nbr']

                        ];

                    }

                    /** Insert into table */
                    $this->dbextensionsObj->insert($this->destinationTable,
                        collect($insert_rows),
                        function ($item) {
                            // Map - key => value pairs and flat as the map returns an array(array)
                            return (collect($item)->map(function ($value, $key) {
                                if ($key != 'rn')
                                    return [$key => $value];
                                return [];
                            })->flatMap(function ($item) {
                                if ($item != "")
                                    return $item;
                            })->toArray());

                        }, $chunksize);

                },$chunksize);
            });

     }

}