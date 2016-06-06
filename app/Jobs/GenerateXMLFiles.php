<?php
/**
 * Created by
 * User: IU Communications
 * Date: 6/2/16
 */
namespace StudentCentralCourseBrowser\Jobs;

use StudentCentralCourseBrowser\Models as Models;
use Symfony\Component\Process\Process;

class GenerateXMLFiles extends Job
{

    public function __construct()
    {
        parent::__construct('GenerateXMLFiles');
    }


    /**
     *
     */
    protected function run()
    {
        $acad_terms = $this->getAcadTerms();
        // $path = $this->makeCoursesFolder();
        $path = '';

        //1.Iterate through terms
        //2.Iterate through departments and build courses and classes;
        collect($acad_terms)->each(/**
         * @param $term
         */

            function ($term) use ($path) {

                $term_info = Models\TermDescription::acadTerm($term)->first();
                //$term_folder_path = $this->makeTermFolder($term,$path);

                //get all departments for the acad term
                $departments = collect(Models\TermDepartment::acadTerm($term)->orderBy('crs_subj_dept_cd')->get());

                // create xml writer object
                $xml = new \XMLWriter();
                // memory for string output
                $xml->openMemory();
                //create the document tag
                $xml->startDocument('1.0', 'UTF-8');

                $term_folder_path = '';

                $departments->each(/**
                 * @param $dept
                 */
                    function ($dept) use ($term_info, $xml, $term_folder_path) {

                        $term = $term_info->term;
                        /** @var Create the  xml file */
                        // $file_name =$term_folder_path."/".$dept->crs_subj_dept_cd.".xml";
                        // $fh = fopen($file_name, 'a');

                        // Term information
                        $xml->startElement("term");
                        $xml->writeElement('term_cd', $term);
                        $xml->writeElement('term_description', $term_info->description);

                        // Department Information
                        $xml->startElement("department");
                        $xml->writeElement('department_cd', $dept->crs_subj_dept_cd);
                        $xml->writeElement('description', $dept->crs_subj_desc);
                        $xml->writeElement('acad_group', $dept->acad_grp_cd);


                        // Courses
                        $classes = Models\ClassTable::acadTerm('4162')
                            ->dept('AAAD')
                            ->customOrderBy($term)->get();


                        $course_element = "";
                        /** Each Class */
                        $courses="";
                        foreach ($classes as $class) {
                            // global variables
                            $crs_id = $class->crs_id;
                            $crsofr_nbr = $class->crsofr_nbr;
                            $cls_sect_cd = $class->cls_sect_cd;

                            $acad_term = $term;
                            $acad_term_desc = $class->acad_term_desc;
                            $inst_cd = $class->inst_cd;
                            $inst_desc = $class->inst_desc;
                            $cmp_loc_cd = $class->cmp_loc_cd;

                            $acad_grp_cd = $class->acad_grp_cd;
                            $acad_grp_desc = rtrim($class->acad_grp_desc);
                            $acad_grp_line = $acad_grp_desc . " ( " . $acad_grp_cd . ")";

                            //global variable
                            $crs_subj_cd = $class->crs_subj_cd;

                            $crs_subj_desc = $class->crs_subj_desc;
                            $crs_subj_dept_cd = $class->crs_subj_dept_cd;
                            $crs_subj_ltr_cd = $class->crs_subj_ltr_cd;
                            $crs_catlg_nbr = $class->crs_catlg_nbr;
                            $subj_catlg = $crs_subj_cd . " " . $crs_catlg_nbr;

                            //Global Variable
                            $cls_sesn_cd = $class->cls_sesn_cd;
                            $cls_drvd_sesn_cd = $class->cls_drvd_sesn_cd;
                            $cls_sesn_desc = $class->cls_sesn_desc;

                            $cls_sesn_page_head = " ";
                            $cls_sesn_curr_sess = " ";

                            $crs_desc = strtoupper($class->crs_desc);
                            $cls_nbr = $class->cls_nbr;
                            $cls_typ_ind = $class->cls_typ_ind;
                            $crs_cmpnt_cd = $class->crs_cmpnt_cd;
                            $crs_cmpnt_shrt_desc = $class->crs_cmpnt_shrt_desc;
                            $crs_cmpnt_line = $crs_cmpnt_shrt_desc . " (" . $crs_cmpnt_cd . ")";
                            $cls_enrl_stat_cd = $class->cls_enrl_stat_cd;
                            $cls_stat_cd = $class->cls_stat_cd;

                            //Associated Class Number - Global Variable
                            $cls_assct_nbr = $class->cls_assct_nbr;

                            // Cluster requirement - check - not used
                            $cls_cnst_typ_req_cd = $class->cls_cnst_typ_req_cd;

                            //Check consent type - Department and Instructor
                            $cls_cnst_typ_req_cd = collect(['D', 'I'])
                            . contains($cls_cnst_typ_req_cd) ? "PERM" : " ";

                            $cls_enrl_cpcty_nbr = $class->cls_enrl_cpcty_nbr;
                            $cls_cmb_sect_shrt_desc = $class->cls_cmb_sect_shrt_desc;

                            $cls_cmb_sect_id = $class->cls_cmb_sect_id;

                            $cs_cls_drvd_enrl_cnt = 0;
                            $cs_cls_enrl_cpcty_nbr = 0;

                            // Get Combined section info
                            $combined_section_info = Models\ClassCombinedSection
                                ::where('acad_term_cd', '=', $acad_term)
                                ->where('cls_cmb_sect_id', '=', $cls_cmb_sect_id)
                                ->where('cls_sesn_cd', '=', $cls_sesn_cd)->get();

                            $cs_cls_drvd_enrl_cnt = $combined_section_info->sum('cls_drvd_enrl_cnt');


                            $first = $combined_section_info->first();

                            $cs_cls_enrl_cpcty_nbr = isset($first)
                                ? $combined_section_info->first()->cls_enrl_cpcty_nbr : 0;

                            $combined_section_enrl_diff = $cs_cls_enrl_cpcty_nbr - $cs_cls_drvd_enrl_cnt;

                            $class_enrl_diff = $cls_enrl_cpcty_nbr - $class->cls_drvd_enrl_cnt;


                            if (count($combined_section_info) > 0 && ($combined_section_enrl_diff < $class_enrl_diff)) {
                                $cls_tot_avl_nbr = $combined_section_enrl_diff;
                            } else {
                                $cls_tot_avl_nbr = $class_enrl_diff;
                            }


                            if ($cls_tot_avl_nbr < 0) $cls_tot_avl_nbr = 0;
                            if ($cls_tot_avl_nbr == 0 || $cls_stat_cd != 'A')
                                $cls_clsd_cd = 'CLSD';
                            else
                                $cls_clsd_cd = '';

                            $crs_tpc_id = $class->crs_tpc_id;
                            $crs_tpc_desc = $class->crs_tpc_desc;

                            $cls_sc_prnt_ind = $class->cls_sc_prnt_ind;
                            $cls_wlst_tot_nbr = $class->cls_wlst_tot_nbr;


                            // Meeting pattern - facility, meeting time
                            $cls_mtg_ptrn_nbr = $class->cls_mtg_ptrn_nbr;
                            $facil_id = $class->facil_id;
                            $facil_bldg_cd = $class->facil_bldg_cd;
                            $facil_bldg_rm_nbr = $class->facil_bldg_rm_nbr;

                            //TODO: check with Malinda - formatting
                            $format_time = function ($time) {
                                return str_replace("am", "a.m.",
                                    str_replace("pm", "p.m.", $time));
                            };
                            $cls_mtg_strt_tm = $format_time($class->cls_mtg_strt_tm);
                            $cls_mtg_end_tm = $format_time($class->cls_mtg_end_tm);
                            $cls_instr_nm = $class->formatted_instructor_name;

                            $cls_sc_inst_prnt_ind = $class->cls_sc_inst_prnt_ind;
                            $crs_attrib_cd = $class->crs_attrib_cd;
                            $crs_attrib_desc = $class->crs_attrib_desc;
                            $crs_attrib_val_cd = $class->crs_attrib_val_cd;
                            $crs_attrib_clst_cd = substr($crs_attrib_val_cd, 7, 3);
                            $crs_attrib_clst_line = $crs_attrib_desc . " ( " . $crs_attrib_clst_cd . " )";

                            //Credits
                            $cls_assct_min_unt_nbr = $class->cls_assct_min_unt_nbr;
                            $cls_assct_max_unt_nbr = $class->cls_assct_max_unt_nbr;

                            if ($cls_assct_max_unt_nbr > $cls_assct_min_unt_nbr)
                                $crs_desc_line = $crs_subj_cd . " " . $crs_catlg_nbr . " " . $crs_desc . " (" .
                                    $cls_assct_min_unt_nbr . "&amp;ndash;" . $cls_assct_max_unt_nbr . " CR)";
                            else
                                $crs_desc_line = $crs_subj_cd . " " . $crs_catlg_nbr . " " . $crs_desc . " (" .
                                    $cls_assct_min_unt_nbr . " CR)";

                            $cls_cmpnt_cd = $class->cls_cmpnt_cd;
                            $cls_rqmt_grp_cd = $class->cls_rqmt_grp_cd;
                            $cls_use_catlg_rqrst_ind = $class->cls_use_catlg_rqrst_ind;
                            $crs_rqmt_grp_cd = $class->crs_rqmt_grp_cd;

                            $cls_subj_dept_cd = $class->cls_subj_dept_cd;
                            $crs_subj_line = $crs_subj_desc .
                                ' (' . $crs_subj_dept_cd . ')';


                            $cls_key = $class->cls_key;


                            //Get Class Notes, check for different class keep but same dept
                            if ((!isset($cls_key_keep) || (isset($cls_key_keep) &&
                                        ($cls_key_keep != $cls_key))) &&
                                ((!isset($cls_subj_dept_cd_keep) || (isset($cls_subj_dept_cd_keep) &&
                                        ($cls_subj_dept_cd_keep == $cls_subj_dept_cd))))
                            ) {

                                //If it is summer term
                                if (substr($term, 5, 1) == 5) {
                                    if (!isset($cls_sesn_curr_sess_keep) || (isset($cls_sesn_curr_sess_keep) &&
                                            $cls_sesn_curr_sess_keep != $cls_sesn_curr_sess)
                                    )
                                        $special_sess_notes_flag = true;
                                }

                                $class_notes_A = Models\ClassNotes::classNotesA($cls_key)->distinct()->get();

                            }


                            if (!(isset($crs_desc_line_keep)) ||
                                (isset($crs_desc_line_keep) && $crs_desc_line_keep != $crs_desc_line)
                            ) {
                                if (isset($course_element))
                                    $xml->endElement();

                                $courses[$crs_desc_line]['crs_subj_line']=$crs_subj_line;
                                $courses[$crs_desc_line]['crs_subj_dept_cd']=$crs_subj_dept_cd;



                                $xml->startElement("course");
                                $xml->writeElement('crs_subj_line', $crs_subj_line);
                                $xml->writeElement('crs_subj_dept_cd', $crs_subj_dept_cd);

                                $course_element = true;

                            }


                            // cluster line
                            if ($crs_attrib_clst_cd != '') {

                                if ((!isset($crs_attrib_clst_line_keep) || (isset($crs_attrib_clst_line_keep) &&
                                            $crs_attrib_clst_line_keep != $crs_attrib_clst_line)) ||
                                    (!isset($cls_sesn_curr_sess_keep) || (isset($cls_sesn_curr_sess_keep) &&
                                            $cls_sesn_curr_sess_keep != $cls_sesn_curr_sess)) ||
                                    (!isset($crs_subj_line_keep) || (isset($crs_subj_line_keep) ||
                                            $crs_subj_line_keep != $crs_subj_line))
                                ) {
                                    $courses[$crs_desc_line]['crs_attrib_clst_line']=$crs_attrib_clst_line;

                                    $xml->writeElement('crs_attrib_clst_line', $crs_attrib_clst_line);

                                }
                            }

                            //count class associations
                            $class_association_sects = Models\ClassAssociation
                                ::lookupClassAssociations(
                                    $crs_id, $crsofr_nbr, $acad_term,
                                    $cls_sesn_cd, $cls_assct_nbr)->get();

                            $count_assoc_rec_ct = count($class_association_sects);

                            if ((!isset($cls_sesn_curr_sess_keep) || (isset($cls_sesn_curr_sess_keep) &&
                                        $cls_sesn_curr_sess_keep != $cls_sesn_curr_sess))
                                || (!isset($crs_desc_line_keep) || (isset($crs_desc_line_keep) &&
                                        $crs_desc_line_keep != $crs_desc_line))
                                || (!isset($crs_attrib_clst_line_keep) || (isset($crs_attrib_clst_line_keep) &&
                                        $crs_attrib_clst_line_keep != $crs_attrib_clst_line))
                            ) {

                               // $courses[$crs_desc_line]['crs_desc_line']=$crs_desc_line;

                                $xml->writeElement('crs_desc_line', $crs_desc_line);

                            } else {
                                if ($count_assoc_rec_ct > 1 || (isset($count_assoc_rec_ct_keep) &&
                                        $count_assoc_rec_ct_keep > 1)
                                ) {
                                    $xml->writeElement('crs_desc_line', $crs_desc_line);
                                }
                            }

                            /**
                             *    !----THIS HOLDS INFO TO SUPPRESS REPEATING INFORMATION----!
                             * !----PUT IT HERE AFTER CRS DESC LINE WRITTEN IN ORDER ----!
                             */
                            $cls_sesn_curr_sess_keep = $cls_sesn_curr_sess;
                            /**
                             *  !----TO ALLOW SESSION HEADER TO PRINT FOR SUMMERS WHEN----!
                             * !----A LARGE BEFORE NOTE WOULD PREVENT IT FROM PRINTING---!
                             */

                            /**! CHECK FOR SAME COMPONENT, NOT PRIMARY COMPONENT*/
                            if (!isset($crs_cmpnt_line_keep) || ((isset($crs_cmpnt_line_keep) &&
                                        $crs_cmpnt_line_keep != $crs_cmpnt_line)
                                    && ($crs_cmpnt_cd != $cls_cmpnt_cd))
                            ) {
                                $xml->writeElement('crs_cmpnt_line', $crs_cmpnt_line);
                                $courses[$crs_desc_line]['crs_cmpnt_line']=$crs_cmpnt_line;

                            }

                            /** Description dont match */
                            $err_badclsAssoc = '** ERROR - ' . $crs_subj_cd . ' ' . $crs_catlg_nbr . ' **';
                            if ((isset($crs_desc_line_keep) && ($crs_desc_line_keep != $crs_desc_line))) {
                                $xml->writeElement('err_bad_clsAssoc', $err_badclsAssoc);
                                $courses[$crs_desc_line]['err_bad_clsAssoc']= $err_badclsAssoc;
                            } else {

                                //! CHECK FOR NON-GRADED COMPONENT WITH DIFFERENT COURSE DESC LINE THAN PREVIOUS CLASS SECTION
                                if (($cls_cmpnt_cd != $crs_cmpnt_cd) && (

                                    (!isset($crs_desc_line_keep) || (isset($crs_desc_line_keep)
                                            && ($crs_desc_line_keep != $crs_desc_line))))
                                )

                                    $xml->writeElement('err_bad_clsAssoc', $err_badclsAssoc);
                                $courses[$crs_desc_line]['err_bad_clsAssoc']= $err_badclsAssoc;
                            }

                            if (!isset($cls_key_keep) || (isset($cls_key_keep) && $cls_key_keep != $cls_key)) {



                                //End class
                                if (isset($class_element)) {
                                    $xml->endElement();//END CLASS
                                }

                                $courses[$crs_desc_line]['class'][$cls_key]= [];

                                $xml->startElement('class');
                                $class_element = true;
                            }


                            //Check for Course topic
                            if (trim($crs_tpc_desc) != "") {

                                if (!isset($cls_key_keep) || (isset($cls_key_keep) && $cls_key_keep != $cls_key))
                                    $xml->writeElement('crs_tpc_desc', "VT:" . $crs_tpc_desc);
                                $courses[$crs_desc_line]['class'][$cls_key]['crs_tpc_desc']= "VT:" . $crs_tpc_desc;

                            }

                            if (!isset($cls_key_keep) || (isset($cls_key_keep) && $cls_key_keep != $cls_key)) {
                                $class_notes_b = Models\ClassNotes::where('cls_key', '=', $cls_key)
                                    ->where('cls_nts_prnt_at_cd', '=', 'B')->get();


                                $notes= $class_notes_b->map(function ($notes_b) use ($xml,&$notes) {
                                    if ($notes_b->cls_nts_seq_nbr != $this->ti_inst) {
                                        $xml->writeElement('class_notes_b', $notes_b->nts_nbr_long_desc);
                                        return $notes_b->nts_nbr_long_desc;
                                    }
                                });

                                $courses[$crs_desc_line]['class'][$cls_key]['notes_b']= $notes;



                                foreach ($class_notes_A as $notes_a) {

                                    if ($notes_a->cls_nts_seq_nbr != $this->ti_inst) {

                                        if (isset($notes_a->nts_nbr_long_desc))
                                            $xml->writeElement('class_notes_a', $notes_a->nts_nbr_long_desc);
                                        $courses[$crs_desc_line]['class'][$cls_key]['notes_a'][]=$notes_a->nts_nbr_long_desc;
                                    }
                                };


                                $ti_course = $cls_drvd_sesn_cd . $crs_subj_cd . $crs_catlg_nbr;
                                $crseRslt = 1;
                                $crseRslt = isset($ti_crse_list) ?
                                    strpos($ti_crse_list, $ti_course) : 1;//Transfer indiana Initaitive logic

                                if ($crseRslt == 0)//TODO-recheck
                                    //get transfer indiana initiative - notes
                                    $xml->writeElement('trans_in', '');

                                //Enrollment
                                if ($cls_typ_ind != 'E') {
                                    $holdstars = '*****';
                                    $xml->writeElement('cls_nbr', $holdstars);
                                    $courses[$crs_desc_line]['class'][$cls_key]['cls_nbr']=$holdstars;
                                } else {
                                    $xml->writeElement('cls_nbr', $cls_nbr);
                                    $courses[$crs_desc_line]['class'][$cls_key]['cls_nbr']=$cls_nbr;

                                }


                                // closed class
                                if ($cls_clsd_cd != ''){
                                    $xml->writeElement('cls_closed', $cls_clsd_cd);
                                    $courses[$crs_desc_line]['class'][$cls_key]['cls_closed']=$cls_clsd_cd;

                                }


                                $xml->writeElement('cls_cnst_typ_req_cd', $cls_cnst_typ_req_cd);
                                $courses[$crs_desc_line]['class'][$cls_key]['cls_cnst_typ_req_cd']=$cls_cnst_typ_req_cd;


                            }


                            //class meeting times - same class meeting times
                            $xml->startElement('class_details');
                            $courses[$crs_desc_line]['class'][$cls_key]['class_details'][$cls_mtg_ptrn_nbr]="";

                            if ((!isset($cls_key_keep) || (isset($cls_key_keep) && $cls_key_keep != $cls_key)) ||
                                ((!isset($cls_mtg_ptrn_nbr_keep) || (isset($cls_mtg_ptrn_nbr_keep) &&
                                        $cls_mtg_ptrn_nbr_keep != $cls_mtg_ptrn_nbr)))
                            ) {


                                if ($cls_mtg_strt_tm == "") {
                                    $courses[$crs_desc_line]['class'][$cls_key]['class_details'][$cls_mtg_ptrn_nbr]
                                    ['cls_mtg_strt_tm'] = "ARR";

                                    $xml->writeElement('cls_mtg_strt_tm', "ARR");
                                }
                                else{

                                    $courses[$crs_desc_line]['class'][$cls_key]['class_details'][$cls_mtg_ptrn_nbr]
                                    ['cls_mtg_strt_tm'] = $cls_mtg_strt_tm;
                                    $xml->writeElement('cls_mtg_strt_tm', $cls_mtg_strt_tm);

                                }
                                if ($cls_mtg_end_tm == ""){
                                    $courses[$crs_desc_line]['class'][$cls_key]['class_details'][$cls_mtg_ptrn_nbr]
                                    ['cls_mtg_end_tm'] = "ARR";
                                    $xml->writeElement('cls_mtg_end_tm', "ARR");
                                }

                                else{
                                    $courses[$crs_desc_line]['class'][$cls_key]['class_details'][$cls_mtg_ptrn_nbr]
                                    ['cls_mtg_end_tm'] = $cls_mtg_end_tm;
                                    $xml->writeElement('cls_mtg_end_tm', $cls_mtg_end_tm);

                                }

                                if ($class->cls_drvd_mtg_ptrn_cd == " "){
                                    $courses[$crs_desc_line]['class'][$cls_key]['class_details'][$cls_mtg_ptrn_nbr]
                                    ['cls_drvd_mtg_ptrn_cd'] = "ARR";
                                    $xml->writeElement('cls_drvd_mtg_ptrn_cd', "ARR");
                                }

                                else{

                                    $courses[$crs_desc_line]['class'][$cls_key]['class_details'][$cls_mtg_ptrn_nbr]
                                    ['cls_drvd_mtg_ptrn_cd'] = $class->cls_drvd_mtg_ptrn_cd;
                                    $xml->writeElement('cls_drvd_mtg_ptrn_cd', $class->cls_drvd_mtg_ptrn_cd);

                                }
                                if ($facil_bldg_cd == " ") {
                                    $xml->writeElement('facil_bldg_cd', "ARR");
                                    $xml->writeElement('facil_bldg_rm_nbr', "");
                                    $courses[$crs_desc_line]['class'][$cls_key]['class_details'][$cls_mtg_ptrn_nbr]
                                    ['facil_bldg_cd'] = "ARR";
                                    $courses[$crs_desc_line]['class'][$cls_key]['class_details'][$cls_mtg_ptrn_nbr]
                                    ['facil_bldg_rm_nbr'] = "";

                                } else {
                                    $xml->writeElement('facil_bldg_cd', $facil_bldg_cd);
                                    $xml->writeElement('facil_bldg_rm_nbr', $facil_bldg_rm_nbr);
                                    $courses[$crs_desc_line]['class'][$cls_key]['class_details'][$cls_mtg_ptrn_nbr]
                                    ['facil_bldg_cd'] = $facil_bldg_cd;
                                    $courses[$crs_desc_line]['class'][$cls_key]['class_details'][$cls_mtg_ptrn_nbr]
                                    ['facil_bldg_rm_nbr'] = $facil_bldg_rm_nbr;
                                }

                                //Instructor
                                $xml->writeElement('instructor', $cls_instr_nm);
                                $courses[$crs_desc_line]['class'][$cls_key]['class_details'][$cls_mtg_ptrn_nbr]
                                ['instructor'] = $cls_instr_nm;
                            }

                            if (!isset($cls_key_keep) || (isset($cls_key_keep) && $cls_key_keep != $cls_key)) {
                                $xml->writeElement('cls_enrl_cpcty_nbr', $cls_enrl_cpcty_nbr);
                                $xml->writeElement('cls_tot_avl_nbr', $cls_tot_avl_nbr);
                                $xml->writeElement('cls_wlst_tot_nbr', $cls_wlst_tot_nbr);

                                $courses[$crs_desc_line]['class'][$cls_key]['class_details'][$cls_mtg_ptrn_nbr]
                                ['cls_enrl_cpcty_nbr'] = $cls_enrl_cpcty_nbr;

                                $courses[$crs_desc_line]['class'][$cls_key]['class_details'][$cls_mtg_ptrn_nbr]
                                ['cls_tot_avl_nbr'] = $cls_tot_avl_nbr;

                                $courses[$crs_desc_line]['class'][$cls_key]['class_details'][$cls_mtg_ptrn_nbr]
                                ['cls_wlst_tot_nbr'] = $cls_wlst_tot_nbr;

                            }

                            if (trim($crs_tpc_desc) != "" &&
                                (!isset($cls_key_keep) || ((isset($cls_key_keep) && $cls_key_keep != $cls_key))) ||
                                (!isset($cls_key_keep) || (isset($cls_key_keep) && $cls_key_keep == $cls_key)
                                    && (!isset($cls_mtg_ptrn_nbr_keep) || (isset($cls_mtg_ptrn_nbr_keep) &&
                                            $cls_mtg_ptrn_nbr_keep != $cls_mtg_ptrn_nbr)))
                            ) {

                                if (trim($crs_tpc_desc) != ""){
                                    $courses[$crs_desc_line]['class'][$cls_key]['class_details'][$cls_mtg_ptrn_nbr]
                                    ['crs_mtg_tpc_desc'] ="VT:" . $crs_tpc_desc;
                                    $xml->writeElement('crs_mtg_tpc_desc', "VT:" . $crs_tpc_desc);
                                }


                            }

                            if ($cls_cnst_typ_req_cd != 'PERM') {
                                if ($cls_rqmt_grp_cd != '') {
                                    $rqmt_grp_cd = $cls_rqmt_grp_cd;
                                    $getERG = Models\RequirementGroup
                                        ::where('acad_rqgrp_cd', '=', $rqmt_grp_cd)
                                        ->where('acad_rqmt_ln_typ_cd', '=', 'COND')->get();

                                    if (count($getERG) > 0) {
                                        $cls_cnst_type_req_cd = 'RSTR';
                                        $courses[$crs_desc_line]['class'][$cls_key]['class_details'][$cls_mtg_ptrn_nbr]
                                        ['cls_cnst_type_req_cd'] ='RSTR';

                                        $xml->writeElement('cls_cnst_type_req_cd', 'RSTR');
                                    }

                                    if ($cls_cnst_type_req_cd != 'RSTR') {
                                        $getERG2 = Models\ReservationCapacity
                                            ::where('cls_key', '=', $cls_key)->get();

                                        if (count($getERG2) > 0) {
                                            $courses[$crs_desc_line]['class'][$cls_key]['class_details'][$cls_mtg_ptrn_nbr]
                                            ['cls_cnst_type_req_cd'] ='RSTR';
                                            $xml->writeElement('cls_cnst_type_req_cd', 'RSTR');
                                        }
                                    }
                                }

                            }

                            $xml->endElement(); //class details close

                            $acad_grp_line_keep = $acad_grp_line;
                            $cls_sesn_curr_sess_keep = $cls_sesn_curr_sess;
                            $crs_subj_line_keep = $crs_subj_line;
                            $crs_attrib_clst_line_keep = $crs_attrib_clst_line;
                            $crs_desc_line_keep = $crs_desc_line;
                            $crs_cmpnt_line_keep = $crs_cmpnt_line;
                            $cls_assct_nbr_keep = $cls_assct_nbr;
                            $cls_key_keep = $cls_key;
                            $cls_mtg_ptrn_nbr_keep = $cls_mtg_ptrn_nbr;
                            $crs_subj_dept_cd_keep = $crs_subj_dept_cd;
                            $acad_grp_cd_keep = $acad_grp_cd;
                            $crs_subj_cd_keep = $crs_subj_cd;
                            $subj_catlg_keep = $subj_catlg;


                        }


                        $xml->endElement(); //End department
                        $xml->endElement(); // End Term
                       // echo $xml->flush(true);
                       // exit;

                        echo "<pre>";
                        print_r($courses);
                        exit;
                    });


                // save the contents to the file.

            });


    }

    /** Make  */
    protected
    function makeCoursesFolder()
    {

        $path = storage_path() . "/courses_" . date('m-d-Y_Hi');
        $process = $this->runProcess('mkdir ' . $path);
        if ($process) return $path;

        return false;
    }

    /** Helper Functions */
    protected
    function runProcess($commandstring)
    {
        $process = new Process($commandstring);
        $process->run();

        // executes after the command finishes
        if (!$process->isSuccessful()) {
            throw new \Exception($commandstring . " failed");
        }

        return true;

    }

    /** Create main folder with timestamp, terms folder inside it */
    protected
    function makeTermFolder($term, $path)
    {
        $term_folder_path = $path . "/" . $term;
        $process = $this->runProcess('mkdir ' . $term_folder_path);

        if ($process) return $term_folder_path;
        return false;
    }
}