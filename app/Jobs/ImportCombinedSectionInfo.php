<?php
/**
 * Created by
 * User: IU Communications
 * Date: 6/1/16
 */

namespace StudentCentralApp\Jobs;


/**
 * Class GetCombinedSectionInfo
 * Get information of the combined section
 * @package StudentCentralApp\Jobs
 */
class ImportCombinedSectionInfo extends Job
{

    protected $destinationTable = 'class_combined_section';

    const CombinedSectionQuery = "SELECT distinct Y.ACAD_TERM_CD,
                   Y.CLS_CMB_SECT_ID,
                   Y.CLS_NBR,
                   Y.CLS_ENRL_CPCTY_NBR,  Y.CLS_SESN_CD,
                   Z.CLS_DRVD_ENRL_CNT
                    FROM DSS_RDS.SR_CMB_SECT_GT Y,
                    DSS_RDS.SR_CLS_ENRL_CNT_GT Z
                    WHERE Y.ACAD_TERM_CD = @acad_term_str
                    AND Y.INST_CD = '@inst_cd'
                    AND Y.ACAD_TERM_CD = Z.ACAD_TERM_CD
                    AND Y.CLS_NBR = Z.CLS_NBR";




    public function __construct()
    {
        parent::__construct('GetCombinedSectionInfo');
    }

    protected function run()
    {
        // truncate  - table
        $this->dbextensionsObj->truncate($this->destinationTable);
        collect($this->getAcadTerms())->each(function($term){
            $data = collect(\DB::connection("oracle")
                ->select(str_replace($this->inst_str,$this->getInstitutionCD(),
                        str_replace($this->acad_term_str,$term,
                        self::CombinedSectionQuery))))
            ;

            $this->dbextensionsObj->insert($this->destinationTable, $data,
                function ($item) {

                    return [
                        'acad_term_cd' => $item->acad_term_cd,
                        'cls_cmb_sect_id' => $item->cls_cmb_sect_id,
                        'cls_nbr' => $item->cls_nbr,
                        'cls_enrl_capcty_nbr' => $item->cls_enrl_cpcty_nbr,
                        'cls_sesn_cd' => $item->cls_sesn_cd,
                        'cls_drvd_enrl_cnt'=>$item->cls_drvd_enrl_cnt
                    ];
                });

        });



    }
}