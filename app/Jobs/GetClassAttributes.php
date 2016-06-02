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
     *
     * Attributes that are being read
     * BLIW - Intensive Writing
     * BLCAPP	COLL Critical Approach Reqrmnt
     * COLL030AH	COLL (CASE) A&H Breadth of Inq
     * COLL040SH	COLL (CASE) S&H Breadth of Inq
     * COLL050NM	COLL (CASE) N&M Breadth of Inq
     * COLL070DS	COLL (CASE) Diversity in U.S.
     * COLL080GC	COLL (CASE) Global Civ & Cultr
     * 0GENEDAH	IUB GenEd A&H credit
     * 0GENEDEC	IUB GenEd English Composition
     * 0GENEDMM	IUB GenEd Mathematical Model
     * 0GENEDNM	IUB GenEd N&M credit
     * 0GENEDNS	IUB GenEd N&M credit - NS
     * 0GENEDSH	IUB GenEd S&H credit
     * 0GENEDWC - Carries IUB GenEd WC Credit (World Cultures)
     * 0GENEDWL - IUB GenEd-Approved WL Class (World Languages)
     * BLAI - AMERICAN INDIAN STUDIES
     * BLEN , BLLT - has a lot of different descriptions
     */
    const ClassAttributesQuery = "SELECT distinct

            CLS_KEY,
            CRS_ID,
            CRSOFR_NBR,
            ACAD_TERM_CD,
            CLS_SESN_CD,
            CLS_SECT_CD,
            CRS_ATTRIB_CD,
            CRS_ATTRIB_SHRT_DESC,
            CRS_ATTRIB_DESC,
            CRS_ATTRIB_VAL_CD,
            CRS_ATTRIB_VAL_DESC
            FROM DSS_RDS.SR_CLS_ATTRIB_GT
            where ACAD_TERM_CD = @acad_term_str
            AND (
            CRS_ATTRIB_VAL_CD like 'BLIW%' or CRS_ATTRIB_VAL_CD like 'BLCAPP%' OR CRS_ATTRIB_VAL_CD like 'COLL030AH%' or CRS_ATTRIB_VAL_CD like 'COLL040SH%'
            or CRS_ATTRIB_VAL_CD like 'COLL050NM%' or CRS_ATTRIB_VAL_CD like 'COLL070DS%' or   CRS_ATTRIB_VAL_CD like 'COLL080GC%' or
            CRS_ATTRIB_VAL_CD like '0GENEDAH%' or CRS_ATTRIB_VAL_CD like '0GENEDEC%' or
            CRS_ATTRIB_VAL_CD like '0GENEDMM%' or CRS_ATTRIB_VAL_CD like '0GENEDNM%' or
            CRS_ATTRIB_VAL_CD like '0GENEDNS%' or CRS_ATTRIB_VAL_CD like '0GENEDSH%' or
            CRS_ATTRIB_VAL_CD like '0GENEDWC%' or CRS_ATTRIB_VAL_CD like '0GENEDWL%' or
            CRS_ATTRIB_VAL_CD like 'BLAI%' or  CRS_ATTRIB_VAL_CD like 'BLEN%' or
            CRS_ATTRIB_VAL_CD like 'BLLT%')";


    protected $destinationTable = 'class_attributes';

    public function __construct()
    {
        parent::__construct('GetClassAttributes');
    }

    protected function run()
    {

        // truncate
        $this->dbextensionsObj->truncate($this->destinationTable);

        collect($this->getAcadTerms())->each(function($term){

            $data = collect(\DB::connection("oracle")
                ->select(str_replace($this->acad_term_str,$term,self::ClassAttributesQuery)));

        //CLS_KEY
        $this->dbextensionsObj->insert($this->destinationTable, $data,
            function ($item) {
                return [
                    'cls_key'=>$item->cls_key,
                    'crs_id' => $item->crs_id,
                    'crsofr_nbr' => $item->crsofr_nbr,
                    'cls_sesn_cd' => $item->cls_sesn_cd,
                    'cls_sect_cd' => $item->cls_sect_cd,
                    'crs_attrib_cd'=>$item->crs_attrib_cd,
                    'crs_attrib_shrt_desc'=>$item->crs_attrib_shrt_desc,
                    'crs_attrib_desc'=>$item->crs_attrib_desc,
                    'crs_attrib_val_cd'=>$item->crs_attrib_val_cd,
                    'crs_attrib_val_desc'=>$item->crs_attrib_val_desc
                ];
            });

        });

    }
}