<?php
/**
 * Created by
 * User: IU Communications
 * Date: 5/18/16
 */

namespace StudentCentralCourseBrowser\Jobs;


use StudentCentralCourseBrowser\Utils\ArrayHelpers;

class CreateSC911LE3 extends Job
{


    protected $and, $and2, $and3, $and4, $and5, $and6;
    protected $destinationTable = 'class';


    /***
     * CreateSC911LE3 constructor.
     */
    function __construct()
    {
        parent::__construct("CreateSC911LE3");
    }

    /**
     * Run method runs the job
     */
    protected function run()
    {

        /** TODO - add backup and then truncate */

        $this->dbextensionsObj->truncate('class');


        $acad_terms_cd = array_map(function ($item) {
            return "'" . $item . "'";
        }, $this->getAcadTerms());

        /**
         * iterate through each acad term and retrieve 200 records each loop
         * Choose lowest chunk size so that the data can be read - job crashes on low memory
         */
        $chunksize = 100;

        collect($acad_terms_cd)->each(function ($acadTerm) use ($chunksize) {

            $query = str_replace($this->acad_term_str, $acadTerm, $this->selectQuery() . " " .
                $this->whereClause());

            $this->dbextensionsObj->readDataInChunksDSSPROD($query,

                function ($data) use ($chunksize) {

                    $insert_rows = "";
                    foreach ($data as $row) {


                        $CS_CLS_DRVD_ENRL_CNT = 0;
                        $CS_CLS_ENRL_CPCTY_NBR = 0;

                        //cls_cnst_typ_req_cd == 'D', I
                        if ($row['cls_cnst_typ_req_cd'] == 'D' || $row['cls_cnst_typ_req_cd'] == 'I')
                            $row['cls_cnst_typ_req_cd'] = 'PERM';
                        else
                            $row['cls_cnst_typ_req_cd'] = "";


                        /** Enrollment Numbers - TODO - move it to combined section information */
                        $row['cls_tot_avl_nbr'] = $row['cls_enrl_cpcty_nbr'] - $row['cls_drvd_enrl_cnt'];

                        if ($row['cls_tot_avl_nbr'] < 0)
                            $row['cls_tot_avl_nbr'] = 0;

                        if ($row['cls_tot_avl_nbr'] == 0 || $row['cls_stat_cd'] != 'A')
                            $row['cls_clsd_cd'] = 'CLSD';
                        else
                            $row['cls_clsd_cd'] = '';

                        /* ELMINATES STUTTERING (SOMETIMES BUILDING CODE APPEARS AT START OF ROOM NUMBER)*/
                        if (substr($row['facil_bldg_rm_nbr'], 1, 2) == $row['facil_bldg_cd']) {
                            $row['facil_bldg_rm_nbr'] = SUBSTR($row['facil_bldg_rm_nbr'], 3, 8);
                        }

                        if ($row['facil_bldg_cd'] == "")
                            $row['facil_bldg_cd'] = "ARR";

                        if ($row['cls_drvd_mtg_ptrn_cd'] == "")
                            $row['cls_drvd_mtg_ptrn_cd'] = "ARR";


                        //Format Instructor Name
                        $row['formatted_instructor_name'] = $this->formatInstructorName($row['cls_instr_nm']);

                        /** Attributes */
                        $row['crs_attrib_clst_cd'] = substr($row['crs_attrib_val_cd'], 7, 3);

                        $insert_rows[] = $row;


                    }


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

                }, $chunksize);


        });

    }

    private function selectQuery()
    {


        return "SELECT distinct rownum as rn,
        A.CLS_KEY,
        A.CRSOFR_KEY,
        A.CRS_ID,
        A.CRSOFR_NBR,
        A.CLS_SECT_CD,
        A.ACAD_TERM_CD,
        A.CLS_SESN_CD,
        A.CLS_DRVD_SESN_CD,
        A.CLS_SESN_DESC,
        A.CMP_LOC_CD,
        A.ACAD_GRP_CD,
        A.ACAD_GRP_DESC,
        A.CRS_SUBJ_CD,
        A.CRS_SUBJ_DESC,
        A.CRS_SUBJ_DEPT_CD,
        A.CRS_SUBJ_LTR_CD,
        A.CRS_CATLG_NBR,
        REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(A.CRS_DESC,Chr(200),
        'E'),Chr(201),'E'),'&',' & '),':',': '),',',', '),'  ',' '),'  ',' ')CRS_DESC,
        A.CLS_NBR,
        A.CLS_TYP_IND,
        A.CRS_CMPNT_CD,
        A.CRS_CMPNT_SHRT_DESC,
        A.CLS_ENRL_STAT_CD,
        A.CLS_STAT_CD,
        A.CLS_ASSCT_NBR,
        A.CLS_SC_PRNT_IND,
        A.CLS_CNST_TYP_REQ_CD,
        A.CLS_TOT_ENRL_NBR,
        AAA.CLS_DRVD_ENRL_CNT,
        A.CLS_ENRL_CPCTY_NBR,
        A.CRS_TPC_ID,
        REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(A.CRS_TPC_DESC,'&',' & '),':',': '),',',', '),'  ',' '),'  ',' ') CRS_TPC_DESC,
        A.CLS_WLST_TOT_NBR,
        B.CLS_MTG_PTRN_NBR,
        B.FACIL_ID,
        B.FACIL_BLDG_CD,
        B.FACIL_BLDG_RM_NBR,
        to_char(B.CLS_MTG_STRT_TM, 'hh:mi am' ) CLS_MTG_STRT_TM,
        to_char(B.CLS_MTG_END_TM, 'hh:mi am' ) CLS_MTG_END_TM,
        B.CLS_DRVD_MTG_PTRN_CD,
        B.CLS_DRVD_SORT_CD,
        C.CLS_INSTR_ASGN_SEQ_NBR,
        REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(C.CLS_INSTR_NM,Chr(225),'a'),Chr(231),'c'),Chr(233),'e'),Chr(239),'i'),Chr(241),'n'),Chr(243),'o'),Chr(250),'u') CLS_INSTR_NM,
        C.CLS_SC_INSTR_PRNT_IND,
        D.CRS_ATTRIB_CD,
        D.CRS_ATTRIB_SHRT_DESC,
        D.CRS_ATTRIB_DESC,
        D.CRS_ATTRIB_VAL_CD,
        D.CRS_ATTRIB_VAL_DESC,
        E.CLS_ASSCT_MIN_UNT_NBR,
        E.CLS_ASSCT_MAX_UNT_NBR,
        E.CLS_CMPNT_CD,
        E.CLS_RQMT_GRP_CD,
        E.CLS_USE_CATLG_RQRST_IND,
        E.CRS_RQMT_GRP_CD,
        F.CLS_ASSOC_CMPNT_CD,
        F.CLS_DRVD_GRD_CMPNT_IND,
        G.CLS_MTG_STRT_DT,
        X.CLS_CMB_SECT_SHRT_DESC,
        X.CLS_CMB_SECT_ID
      FROM
        DSS_RDS.SR_CLS_GT A,
        DSS_RDS.SR_CLS_MTG_GT B,
        DSS_RDS.SR_CMB_CLS_INSTR_GT C,
        (" . $this->getTableD() . ") D,
        DSS_RDS.SR_CLS_ASSOC_GT E,
        DSS_RDS.SR_CLS_CMPNT_GT F, (" . $this->getTableG() . ") G, (" . $this->getTableX() . ") X ,
        DSS_RDS.SR_CLS_ENRL_CNT_GT AAA ";


    }


    private function getTableD()
    {
        return "SELECT
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
        WHERE CRS_ATTRIB_CD = 'CLST'";

    }

    private function getTableG()
    {
        return "SELECT CLS_KEY,
        CLS_MTG_STRT_TM,
        CLS_DRVD_SORT_CD,
        CLS_MTG_STRT_DT
        FROM DSS_RDS.SR_CLS_MTG_GT
        WHERE CLS_MTG_PTRN_NBR = 1";
    }

    private function getTableX()
    {
        return " SELECT DISTINCT ACAD_TERM_CD,
        CLS_CMB_SECT_ID,
        CLS_NBR,
        CLS_CMB_SECT_SHRT_DESC
        FROM DSS_RDS.SR_CMB_SECT_GT ";
    }

    private function whereClause()
    {

        $inst_cd = "'IUBLA'";
        $acad_terms_cd = $this->acad_term_str;

        /*
         * join clause
         */
        $where_clause = "WHERE 1=1
                        AND B.CLS_KEY (+) = A.CLS_KEY
                        AND C.CLS_KEY (+) = B.CLS_KEY
                        AND C.CLS_MTG_PTRN_NBR (+) = B.CLS_MTG_PTRN_NBR
                        AND D.CLS_KEY (+) = A.CLS_KEY
                        AND E.CRS_ID = A.CRS_ID
                        AND E.CRSOFR_NBR = A.CRSOFR_NBR
                        AND E.ACAD_TERM_CD = A.ACAD_TERM_CD
                        AND E.CLS_SESN_CD = A.CLS_SESN_CD
                        AND E.CLS_ASSCT_NBR = A.CLS_ASSCT_NBR
                        AND F.CRS_ID = A.CRS_ID
                        AND F.CRSOFR_NBR = A.CRSOFR_NBR
                        AND F.ACAD_TERM_CD = A.ACAD_TERM_CD
                        AND F.CLS_SESN_CD = A.CLS_SESN_CD
                        AND F.CLS_ASSCT_NBR = A.CLS_ASSCT_NBR
                        AND F.CLS_ASSOC_CMPNT_CD = A.CRS_CMPNT_CD
                        AND G.CLS_KEY (+) = A.CLS_KEY
                        AND X.ACAD_TERM_CD (+) = A.ACAD_TERM_CD
                        AND X.CLS_NBR (+) = A.CLS_NBR
                        AND A.CLS_KEY = AAA.CLS_KEY
                      ";

        /**
         * Build and's - return for IUBLA
         */
        $this->and = '  and A.inst_cd in (' . $inst_cd . ')';
        $this->and2 = 'and AA.inst_cd in (' . $inst_cd . ')';
        $this->and3 = 'and BB.inst_cd in (' . $inst_cd . ')';
        $this->and4 = 'and CC.inst_cd in (' . $inst_cd . ')';
        $this->and5 = 'and CC.inst_cd in (' . $inst_cd . ')';
        $this->and5 .= ' and DD.inst_cd in (' . $inst_cd . ')';
        $this->and6 = 'and K.inst_cd in (' . $inst_cd . ')';

        /***
         * Build and for terms
         */
        $this->and .= ' and A.acad_term_cd in (' . $acad_terms_cd . ')';
        $this->and3 .= ' and BB.acad_term_cd in (' . $acad_terms_cd . ')';
        $this->and4 .= ' and DD.acad_term_cd in (' || $acad_terms_cd || ')';
        $this->and5 .= ' and DD.acad_term_cd in (' || $acad_terms_cd || ')';
        $this->and6 .= ' and J.acad_term_cd in (' || $acad_terms_cd || ')';

        return $where_clause . $this->and;


    }

    protected function formatInstructorName($name)
    {
        $comma_pos = strpos($name, ",");

        // If there is a comma
        if ($comma_pos !== false) {
            return substr($name, 0, $comma_pos) . " " . substr($name, $comma_pos + 1, 1);
        }

        return $name;

    }


    private function getTableOLAAA()
    {

        return "
                    SELECT DISTINCT OL_H.COI_CLS_NBR AS CLASS_NBR,  OL_G.ACAD_TERM_CD, OL_G.ENROLLMENT
            FROM


            (SELECT CLASS_NBR, ACAD_TERM_CD, SUM (CLS_DRVD_ENRL_CNT) AS ENROLLMENT
            FROM
            (SELECT DISTINCT OL_B.CLS_DRVD_ENRL_CNT,
            OL_C.COE_CLS_NBR as CLASS_NBR,
            OL_C.ACAD_TERM_CD
            FROM DSS_RDS.SR_CLS_GT OL_A
            INNER JOIN DSS_RDS.SR_CLS_ENRL_CNT_GT OL_B
            ON OL_A.CLS_KEY = OL_B.CLS_KEY
            INNER JOIN DSS_RDS.SR_IU_OA_CLAS_MAP_GT OL_C
            ON OL_A.CLS_NBR = OL_C.COE_CLS_NBR
            AND OL_A.ACAD_TERM_CD = OL_C.ACAD_TERM_CD
            UNION ALL
            SELECT OL_E.CLS_DRVD_ENRL_CNT, OL_F.COE_CLS_NBR as CLASS_NBR, OL_F.ACAD_TERM_CD
            FROM DSS_RDS.SR_CLS_GT OL_D
            INNER JOIN DSS_RDS.SR_CLS_ENRL_CNT_GT OL_E
            ON OL_D.CLS_KEY = OL_E.CLS_KEY
            INNER JOIN DSS_RDS.SR_IU_OA_CLAS_MAP_GT OL_F
            ON OL_D.CLS_NBR = OL_F.COE_CLS_NBR
            AND OL_D.ACAD_TERM_CD = OL_F.ACAD_TERM_CD)
            GROUP BY CLASS_NBR, ACAD_TERM_CD) OL_G
            INNER JOIN DSS_RDS.SR_IU_OA_CLAS_MAP_GT OL_H
            ON OL_H.COE_CLS_NBR = OL_G.CLASS_NBR
            OR OL_H.COE_CLS_NBR = OL_G.CLASS_NBR

            UNION

            SELECT DISTINCT CLASS_NBR, ACAD_TERM_CD AS ACAD_TERM_CD, SUM (CLS_DRVD_ENRL_CNT) AS ENROLLMENT
            FROM (SELECT DISTINCT OL_J.CLS_DRVD_ENRL_CNT, OL_K.COE_CLS_NBR CLASS_NBR, OL_K.ACAD_TERM_CD
            FROM DSS_RDS.SR_CLS_GT OL_I
            INNER JOIN DSS_RDS.SR_CLS_ENRL_CNT_GT OL_J
            ON OL_I.CLS_KEY = OL_J.CLS_KEY
            INNER JOIN DSS_RDS.SR_IU_OA_CLAS_MAP_GT OL_K
            ON OL_I.CLS_NBR = OL_K.COE_CLS_NBR
            AND OL_I.ACAD_TERM_CD = OL_K.ACAD_TERM_CD
            UNION ALL
            SELECT OL_M.CLS_DRVD_ENRL_CNT, OL_N.COE_CLS_NBR CLASS_NBR, OL_N.ACAD_TERM_CD
            FROM DSS_RDS.SR_CLS_GT OL_L
            INNER JOIN DSS_RDS.SR_CLS_ENRL_CNT_GT OL_M
            ON OL_L.CLS_KEY = OL_M.CLS_KEY
            INNER JOIN DSS_RDS.SR_IU_OA_CLAS_MAP_GT OL_N
            ON OL_L.CLS_NBR = OL_N.COI_CLS_NBR
            AND OL_L.ACAD_TERM_CD = OL_N.ACAD_TERM_CD)
            GROUP BY CLASS_NBR, ACAD_TERM_CD
                    ";
    }


}