<?php
/**
 * Created by
 * User: IU Communications
 * Date: 7/10/16
 */

namespace StudentCentralApp\Models;


use Illuminate\Database\Eloquent\Model;
use League\Fractal\Resource\Collection as Collection;
use StudentCentralApp\Transformers\CourseTransformer;

class Course extends Model
{


    /**
     * Function builds courses
     * @param $term
     * @param $term_info
     * @param $dept
     * @param $allcourses
     * @param $term_folder_path
     */
    function buildCourses($term, $term_info = null, $dept = null,
                          $course_letter = "",
                          $catalog_nbr = "", $instruction_mode = "",
                          $days = "", $session = "")
    {

        /** @var $classes - classes for the department and term */
        if (!isset($dept->crs_subj_dept_cd)) {
            $classes = ClassTable::where('cls_sc_prnt_ind', '=', 'Y')
                ->where('acad_term_cd', '=', $term)
                ->where('crs_subj_dept_cd', '=', $dept);

        } else {
            $classes = ClassTable::where('cls_sc_prnt_ind', '=', 'Y')
                ->where('acad_term_cd', '=', $term)
                ->where('crs_subj_dept_cd', '=',$dept->crs_subj_dept_cd);

        }


        if (isset($course_letter) && $course_letter != "")
            $classes = $classes->where('crs_subj_ltr_cd', '=', $course_letter);

        if (isset($catalog_nbr) && $catalog_nbr != "")
            $classes = $classes->where('crs_catlg_nbr', '=', $catalog_nbr);

        if (isset($instruction_mode) && $instruction_mode != "")
            $classes = $classes->where('cls_instrc_mode_cd', 'like', $instruction_mode);

        if (isset($session) && $session != "")
            $classes = $classes->where('cls_sesn_cd', '=', $session);


        /** days check */
        if (isset($days) && $days!="") {
            $day_query = "";
            foreach ($days as $day) {

                if ($day_query != "")
                    $day_query .= " and ";
                $day_query .= "(cls_drvd_mtg_ptrn_cd like '$day%'
                     or cls_drvd_mtg_ptrn_cd like '%$day%'
                     or  cls_drvd_mtg_ptrn_cd like '$day%'
                     or  cls_drvd_mtg_ptrn_cd like '$day')";

            }

            $classes = $classes->whereRaw($day_query);
        }


        $classes = $classes->customOrderBy($term)->distinct()->get();


        if (count($classes) == 0) return;


        /** @var $courses - course can contain multiple classes */
        $courses = "";

        /** @var $class global variables */
        $acad_grp_line_keep = "";
        $cls_sesn_curr_sess_keep = "";
        $crs_subj_line_keep = "";
        $crs_attrib_clst_line_keep = "";
        $crs_desc_line_keep = "";
        $crs_cmpnt_line_keep = "";
        $cls_assct_nbr_keep = 0;
        $cls_key_keep = 0;
        $cls_mtg_ptrn_nbr_keep = "";
        $crs_subj_dept_cd_keep = "";
        $acad_grp_cd_keep = "";
        $crs_subj_cd_keep = "";
        $subj_catlg_keep = "";
        $special_sess_notes_flag = 'NO';
        $cls_cnst_type_req_cd = "";
        $classassoc_rec_ctr = 0;
        $classassoc_rec_ctr_keep = 0;
        $instructor_assigned_seq_nbr_keep = "";
        $ti_crse_list = '';
        $crs_catlg_nbr_keep = 0;


        foreach ($classes as $class) {

            $cls_key = $class->cls_key;

            // Global variables
            $crs_id = $class->crs_id;
            $crsofr_nbr = $class->crsofr_nbr;
            $cls_sect_cd = $class->cls_sect_cd;
            $cls_cnst_type_req_cd = $class->cls_cnst_type_req_cd;
            $acad_term = $term;
            $acad_term_desc = $class->acad_term_desc;
            $inst_cd = $class->inst_cd;
            $inst_desc = $class->inst_desc;
            $cmp_loc_cd = $class->cmp_loc_cd;

            /** @var new class details gets added - $instructor_assigned_seq_nbr is incremented */
            $instructor_assigned_seq_nbr = $class->cls_instr_asgn_seq_nbr;

            $acad_grp_cd = $class->acad_grp_cd;
            $acad_grp_desc = rtrim($class->acad_grp_desc);
            $acad_grp_line = $acad_grp_desc . " ( " . $acad_grp_cd . ")";

            //Global variable
            $crs_subj_cd = $class->crs_subj_cd;

            // subject, subject department - (eg. AAAD,African Am & Afri Diaspora Std)
            $crs_subj_desc = $class->crs_subj_desc;
            $crs_subj_dept_cd = $class->crs_subj_dept_cd;

            // Course letter, and catlog number
            $crs_subj_ltr_cd = $class->crs_subj_ltr_cd;// eg.B
            $crs_catlg_nbr = trim($class->crs_catlg_nbr); // eg. B500
            $crs_subj_line = $crs_subj_desc . "( " . $crs_subj_dept_cd . " )";

            $subj_catlg = $crs_subj_cd . " " . $crs_catlg_nbr;

            /* Global Variables */
            $cls_sesn_cd = $class->cls_sesn_cd; // 1, 2W, 8W1 etc.
            $cls_drvd_sesn_cd = $class->cls_drvd_sesn_cd; // not needed it is mostly used for summer sessions
            $cls_sesn_desc = $class->cls_sesn_desc; //Regular, summer etc.

            $cls_sesn_curr_sess = " ";

            $crs_desc = strtoupper($class->crs_desc); // course title

            /** @var Class information- $cls_nbr */
            $cls_nbr = $class->cls_nbr;

            /** @var Class type indicator */
            $cls_typ_ind = $class->cls_typ_ind;

            /** @var Course component */
            $crs_cmpnt_cd = $class->crs_cmpnt_cd; // course component - if class component is same as course component is a credit offering class
            $crs_cmpnt_shrt_desc = $class->crs_cmpnt_shrt_desc; //course component short desc
            $crs_cmpnt_line = $crs_cmpnt_shrt_desc . " (" . $crs_cmpnt_cd . ")";
            $cls_enrl_stat_cd = $class->cls_enrl_stat_cd; // OC - oncampus or offcampus


            /** @var enrollment status code for the class */
            $cls_stat_cd = $class->cls_stat_cd; // Active or inactive class.

            /** Associated Class Number - Global Variable - a group of class form associated class section under a course.*/
            $cls_assct_nbr = $class->cls_assct_nbr;

            /** @var Combined section is used to compute the accurate enrollment numbers */
            $cls_cmb_sect_shrt_desc = $class->cls_cmb_sect_shrt_desc;
            $cls_cmb_sect_id = $class->cls_cmb_sect_id;
            $cls_enrl_cpcty_nbr = $class->cls_enrl_cpcty_nbr;
            $cls_drvd_enrl_cnt = $class->cls_drvd_enrl_cnt;
            $cls_tot_avl_nbr = $this->compute_enrollment_numbers
            ($cls_enrl_cpcty_nbr, $cls_drvd_enrl_cnt, $acad_term,
                $cls_cmb_sect_id, $cls_sesn_cd);
            $cls_wlst_tot_nbr = $class->cls_wlst_tot_nbr;

            /** Determine if the class is closed */
            if ($cls_tot_avl_nbr < 0) $cls_tot_avl_nbr = 0;
            if ($cls_tot_avl_nbr == 0 || $cls_stat_cd != 'A')
                $cls_clsd_cd = 'CLSD';
            else
                $cls_clsd_cd = '';


            /** @var Meeting topic $crs_mtg_tpc_desc */
            $crs_mtg_tpc_desc = $class->crs_tpc_dec;
            $crs_tpc_desc = $class->crs_tpc_desc;


            // Meeting pattern - facility, meeting time
            $cls_mtg_ptrn_nbr = $class->cls_mtg_ptrn_nbr;
            $facil_id = $class->facil_id;
            $facil_bldg_cd = $class->facil_bldg_cd;
            $facil_bldg_rm_nbr = $class->facil_bldg_rm_nbr;


            /** @var Class times format in am, pm $format_time */
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

            //Class credits
            $cls_assct_min_unt_nbr = $class->cls_assct_min_unt_nbr;
            $cls_assct_max_unt_nbr = $class->cls_assct_max_unt_nbr;

            if ($cls_assct_max_unt_nbr > $cls_assct_min_unt_nbr)
                $crs_desc_line = $crs_subj_cd . " " . $crs_catlg_nbr . " " . $crs_desc;
            else
                $crs_desc_line = $crs_subj_cd . " " . $crs_catlg_nbr . " " . $crs_desc;

            // class type - discussion, lecture etc.
            $cls_cmpnt_cd = $class->cls_cmpnt_cd;
            $cls_rqmt_grp_cd = $class->cls_rqmt_grp_cd;
            $cls_use_catlg_rqrst_ind = $class->cls_use_catlg_rqrst_ind;
            $crs_rqmt_grp_cd = $class->crs_rqmt_grp_cd;

            $cls_subj_dept_cd = $class->cls_subj_dept_cd;
            $crs_subj_line = $crs_subj_desc .
                ' (' . $crs_subj_dept_cd . ')';

            /** Constructor course array with class */
            if ($cls_key_keep != $cls_key) {

                if (substr($term, 5, 1) == 5) {
                    if ($cls_sesn_curr_sess_keep != $cls_sesn_curr_sess)
                        $special_sess_notes_flag = 'YES';
                }

                /** @var Get class notes */
                $class_notes_a = ClassNotes::classNotesA($cls_key)
                    ->distinct()
                    ->get()
                    ->map(function ($notes) {
                        return [$notes->nts_nbr_long_desc,
                            $notes->nts_long_desc];
                    })->toArray();

                $courses[$crs_subj_cd . "-" . $crs_catlg_nbr]
                ['class_assoc'][$cls_assct_nbr]['classes']["cls_key_".$cls_key]
                ['class_notes_a'] = array_filter(array_flatten($class_notes_a), function ($x) {
                    return isset($x) && $x != '';
                });

                $courses[$crs_subj_cd . "-" . $crs_catlg_nbr]
                ['class_assoc'][$cls_assct_nbr]['classes']["cls_key_".$cls_key]['cls_sesn_cd'] = $cls_sesn_cd;
                $courses[$crs_subj_cd . "-" . $crs_catlg_nbr]
                ['class_assoc'][$cls_assct_nbr]['classes']["cls_key_".$cls_key]['cls_drvd_sesn_cd'] = $cls_sesn_cd;
                $courses[$crs_subj_cd . "-" . $crs_catlg_nbr]
                ['class_assoc'][$cls_assct_nbr]['classes']["cls_key_".$cls_key]['cls_sesn_desc'] = $cls_sesn_desc;

                $courses[$crs_subj_cd . "-" . $crs_catlg_nbr]
                ['class_assoc'][$cls_assct_nbr]['classes']["cls_key_".$cls_key]['cls_instrc_mode_cd'] =
                    $class->cls_instrc_mode_cd;
                $courses[$crs_subj_cd . "-" . $crs_catlg_nbr]
                ['class_assoc'][$cls_assct_nbr]['classes']["cls_key_".$cls_key]['cls_instrc_mode_shrt_desc'] = $class->cls_instrc_mode_shrt_desc;
                $courses[$crs_subj_cd . "-" . $crs_catlg_nbr]
                ['class_assoc'][$cls_assct_nbr]['classes']["cls_key_".$cls_key]['cls_instrc_mode_desc'] = $class->cls_instrc_mode_desc;

            }

            /** Course level properties */
            if ($crs_desc_line_keep != $crs_desc_line) {


                if (isset($term_info) && isset($dept->crs_subj_desc)) {
                    $courses[$crs_subj_cd . "-" . $crs_catlg_nbr]['term']
                        = ['term' => $term,
                        'desc' => $term_info->description];
                    $courses[$crs_subj_cd . "-" . $crs_catlg_nbr]['department']
                        = ['crs_sub_dept_cd' =>
                        $dept->crs_subj_dept_cd,
                        'crs_subj_desc' =>
                            $dept->crs_subj_desc];

                }


                $courses[$crs_subj_cd . "-" . $crs_catlg_nbr]['crs_subj_ltr_cd']
                    = $crs_subj_ltr_cd;
                $courses[$crs_subj_cd . "-" . $crs_catlg_nbr]['crs_desc_line'] = $crs_desc_line;
                $courses[$crs_subj_cd . "-" . $crs_catlg_nbr]['crs_subj_dept_cd'] = $crs_subj_dept_cd;
                $courses[$crs_subj_cd . "-" . $crs_catlg_nbr]['crs_subj_line'] = $crs_subj_line;
                $courses[$crs_subj_cd . "-" . $crs_catlg_nbr]['crs_cmpnt_line']
                    = $crs_cmpnt_line;//course type - discussion, lecture, lab etc.
                $courses[$crs_subj_cd . "-" . $crs_catlg_nbr]['crs_catlg_nbr'] = $crs_catlg_nbr;
                $courses[$crs_subj_cd . "-" . $crs_catlg_nbr]['crs_cmpnt_cd']
                    = $crs_cmpnt_cd;

                /** Course attributes - same as class attributes */
                $courses[$crs_subj_cd . "-" . $crs_catlg_nbr]['course_attributes'] =
                    ClassAttribute::where('cls_key', '=', $cls_key)
                        ->distinct()->select('crs_attrib_val_cd', 'crs_attrib_val_desc')
                        ->get()->toArray();

            }


            //! CHECK FOR SAME COMPONENT, NOT PRIMARY COMPONENT - non credit class
            if ($crs_catlg_nbr != $crs_catlg_nbr_keep && $cls_cmpnt_cd != $crs_cmpnt_cd) {

                $courses
                [$crs_subj_cd . "-" . $crs_catlg_nbr]['class_assoc'][$cls_assct_nbr]['classes']
                ["cls_key_".$cls_key]['cls_cmpnt_cd'] = $crs_cmpnt_cd;

                $courses
                [$crs_subj_cd . "-" . $crs_catlg_nbr]['class_assoc'][$cls_assct_nbr]['classes']["cls_key_".$cls_key]['cls_cmpnt_desc']
                    = $crs_cmpnt_line;

                if ($crs_desc_line_keep != $crs_desc_line) {
                    $err_badClsAssoc = '** ERROR - ' . $crs_subj_cd . " " . $crs_catlg_nbr . ' **';
                    $courses[$crs_subj_cd . "-" . $crs_catlg_nbr]['class_assoc'][$cls_assct_nbr]['classes']["cls_key_".$cls_key]
                    ['err_badClsAssoc'] = $err_badClsAssoc;
                }

            } else {

                $courses
                [$crs_subj_cd . "-" . $crs_catlg_nbr]['class_assoc'][$cls_assct_nbr]['classes']["cls_key_".$cls_key]['cls_cmpnt_cd']
                    = $crs_cmpnt_cd;
                $courses
                [$crs_subj_cd . "-" . $crs_catlg_nbr]['class_assoc'][$cls_assct_nbr]['classes']["cls_key_".$cls_key]['cls_cmpnt_desc']
                    = $crs_cmpnt_line;


                if ($cls_cmpnt_cd != $crs_cmpnt_cd && $crs_desc_line_keep != $crs_desc_line) {
                    $err_badClsAssoc = '** ERROR - ' . $crs_subj_cd . " " . $crs_catlg_nbr . ' **';
                    $courses [$crs_subj_cd . "-" . $crs_catlg_nbr]['class_assoc'][$cls_assct_nbr]['classes']["cls_key_".$cls_key]
                    ['err_badClsAssoc'] = $err_badClsAssoc;
                }
            }


            /** If there is course topic */
            if (trim($crs_tpc_desc) != "") {

                if ($cls_key_keep != $cls_key) {
                    $courses[$crs_subj_cd . "-" . $crs_catlg_nbr]['class_assoc']
                    [$cls_assct_nbr]['classes']["cls_key_".$cls_key]['crs_tpc_desc'] = 'VT:' . $crs_tpc_desc;

                }
            }

            if ($cls_key_keep != $cls_key) {

                //Notes_B
                $class_notes_b = ClassNotes::classNotesB($cls_key)
                    ->distinct()
                    ->get()
                    ->map(function ($notes) {
                        return [$notes->nts_nbr_long_desc,
                            $notes->nts_long_desc];
                    })->flatMap(function ($item) {
                        return $item;
                    })->filter(function ($note) {
                        return isset($note) && $note != '';
                    })->toArray();

                // Transfer indiana initiative - save it on the course
                $transferIndianaInitiative =
                    ClassNotes::where('CLS_KEY', '=', $cls_key)
                        ->distinct()
                        ->get()->map(function ($note) {
                            if ($note->cls_nts_nbr == $this->ti_inst)
                                return $note->nts_nbr_long_desc;
                        })->filter(function ($note) {
                            return isset($note) && $note != "";
                        })->toArray();

                /** Class Notes - B */
                $courses[$crs_subj_cd . "-" . $crs_catlg_nbr]
                ['class_assoc']
                [$cls_assct_nbr]['classes']["cls_key_".$cls_key]
                ['class_notes_b'] = $class_notes_b;

                /** Transfer Indiana Initiative logic - appears on the course */
                $courses[$crs_subj_cd . "-" . $crs_catlg_nbr]
                ['transfer_indiana_initiative']
                    = $transferIndianaInitiative;

            }

            if ($cls_key_keep != $cls_key) {

                if ($cls_typ_ind != 'E')
                    $holdstars = '*****';
                else
                    $holdstars = $cls_nbr;

                $courses[$crs_subj_cd . "-" . $crs_catlg_nbr]['class_assoc'][$cls_assct_nbr]['classes']["cls_key_".$cls_key]['cls_nbr'] = $holdstars;
                $courses[$crs_subj_cd . "-" . $crs_catlg_nbr]['class_assoc'][$cls_assct_nbr]['classes']["cls_key_".$cls_key]['clsd'] = $cls_clsd_cd;

                //Consent check
                $cls_cnst_typ_req_cd = "";
                if ($class->cls_cnst_typ_req_cd == 'D' || $class->cls_cnst_typ_req_cd == 'I')
                    $cls_cnst_typ_req_cd = 'PERM';


                // check consent type if not PERM
                if ($cls_cnst_typ_req_cd != "PERM") {
                    if ($cls_rqmt_grp_cd != '') {
                        $rqmt_grp_cd = $cls_rqmt_grp_cd;
                        $getERG = RequirementGroup
                            ::where('acad_rqgrp_cd', '=', $rqmt_grp_cd)
                            ->where('acad_rqmt_ln_typ_cd', '=', 'COND')->get();

                        if (count($getERG) > 0) {
                            $cls_cnst_type_req_cd = 'RSTR';
                            $courses
                            [$crs_subj_cd . "-" . $crs_catlg_nbr]['class_assoc'][$cls_assct_nbr]['classes']["cls_key_".$cls_key]['cls_cnst_type_req_cd']
                                = $cls_cnst_type_req_cd;
                        }

                        if ($cls_cnst_type_req_cd != 'RSTR') {
                            $getERG2 = ReservationCapacity
                                ::where('cls_key', '=', $cls_key)->get();

                            if (count($getERG2) > 0) {
                                $cls_cnst_type_req_cd = 'RSTR';
                                $courses
                                [$crs_subj_cd . "-" . $crs_catlg_nbr]['class_assoc'][$cls_assct_nbr]['classes']
                                ["cls_key_".$cls_key]['cls_cnst_type_req']
                                    = $cls_cnst_type_req_cd;
                            }
                        }
                    }

                } else {

                    $courses
                    [$crs_subj_cd . "-" . $crs_catlg_nbr]['class_assoc']
                    [$cls_assct_nbr]['classes']["cls_key_".$cls_key]['cls_cnst_type_req']
                        = $cls_cnst_typ_req_cd;
                }

            }


            /** @var class details $details */
            $details = "";
            $key = "";
            /** Start Time */
            if ($cls_mtg_strt_tm == "")
                $details['cls_mtg_strt_tm'] = "ARR";
            else
                $details['cls_mtg_strt_tm'] = $cls_mtg_strt_tm;
            $key .= $details['cls_mtg_strt_tm'];
            /** End Time */
            if ($cls_mtg_end_tm == "")
                $details['cls_mtg_end_tm'] = "ARR";
            else
                $details['cls_mtg_end_tm'] = $cls_mtg_end_tm;
            $key .= $details['cls_mtg_end_tm'];
            /** Meeting Pattern */
            if ($class->cls_drvd_mtg_ptrn_cd == " ")
                $details['cls_drvd_mtg_ptrn_cd'] = "ARR";
            else
                $details['cls_drvd_mtg_ptrn_cd'] = $class->cls_drvd_mtg_ptrn_cd;
            $key .= $details['cls_drvd_mtg_ptrn_cd'];
            /** Facility  */
            if (trim($facil_bldg_cd) == "") {
                $details['facil_bldg_cd'] = "ARR";
                $details['facil_bldg_rm_nbr'] = "";
            } else {
                $details['facil_bldg_cd'] = str_replace("BL", "", $facil_bldg_cd);
                $details['facil_bldg_rm_nbr'] = ($facil_bldg_rm_nbr);
            }
            $key .= $details['facil_bldg_cd'];

            /** Instructor  */
            $details['instructor'] = $cls_instr_nm;

            // Instructor assigned sequence number may be different for the same class details
            if (isset($courses[$crs_subj_cd . "-" . $crs_catlg_nbr]
                    ['class_assoc'][$cls_assct_nbr]['classes']["cls_key_".$cls_key]['details']) &&
                array_key_exists($key, $courses[$crs_subj_cd . "-" . $crs_catlg_nbr]
                ['class_assoc'][$cls_assct_nbr]['classes']["cls_key_".$cls_key]['details'])
            ) {

                $instructor = $courses[$crs_subj_cd . "-" . $crs_catlg_nbr]
                ['class_assoc'][$cls_assct_nbr]['classes']
                ["cls_key_".$cls_key]['details'][$key]['instructor'];

                $explode = explode(";", $instructor);

                if (!(isset($explode) && in_array($details['instructor'], $explode))) {
                    $courses[$crs_subj_cd . "-" . $crs_catlg_nbr]['class_assoc'][$cls_assct_nbr]['classes']["cls_key_".$cls_key]['details']
                    [$key]['instructor'] .= ";" . $details["instructor"];
                }

            } else {
                $courses[$crs_subj_cd . "-" . $crs_catlg_nbr]
                ['class_assoc'][$cls_assct_nbr]['classes']["cls_key_".$cls_key]
                ['details'][$key] = $details;

            }


            if ($cls_key_keep != $cls_key) {
                $courses
                [$crs_subj_cd . "-" . $crs_catlg_nbr]['class_assoc'][$cls_assct_nbr]['classes']["cls_key_".$cls_key]['cls_enrl_cpcty_nbr']
                    = $cls_enrl_cpcty_nbr;

                $courses
                [$crs_subj_cd . "-" . $crs_catlg_nbr]['class_assoc'][$cls_assct_nbr]['classes']["cls_key_".$cls_key]['cls_tot_avl_nbr']
                    = $cls_tot_avl_nbr;

                $courses
                [$crs_subj_cd . "-" . $crs_catlg_nbr]['class_assoc'][$cls_assct_nbr]['classes']["cls_key_".$cls_key]['cls_wlst_tot_nbr']
                    = $cls_wlst_tot_nbr;

                // Class Description
                $courses
                [$crs_subj_cd . "-" . $crs_catlg_nbr]['class_assoc'][$cls_assct_nbr]['classes']["cls_key_".$cls_key]['cls_long_desc']
                    = $this->getClassDescriptions($term, $cls_nbr);
            }


            if ($crs_mtg_tpc_desc != '' && ($cls_key_keep != $cls_key)
                || $cls_key_keep == $cls_key && $cls_mtg_ptrn_nbr_keep != $cls_mtg_ptrn_nbr
            ) {


                $courses
                [$crs_subj_cd . "-" . $crs_catlg_nbr]['class_assoc'][$cls_assct_nbr]['classes']["cls_key_".$cls_key]['crs_mtg_tpc_desc']
                    = $crs_mtg_tpc_desc;

            }


            /** Get Class Attributes */
            if ($cls_key_keep != $cls_key) {

                // class attributes
                $attributes = ClassAttribute::where('cls_key', '=', $cls_key)
                    ->distinct()->select('crs_attrib_val_cd', 'crs_attrib_val_desc')
                    ->get()->toArray();

                $courses[$crs_subj_cd . "-" . $crs_catlg_nbr]['class_assoc'][$cls_assct_nbr]['classes']["cls_key_".$cls_key]
                ['class_attributes']
                    = $attributes;

                /** add description line */
                if ($cls_assct_max_unt_nbr > $cls_assct_min_unt_nbr)
                    $courses[$crs_subj_cd . "-" . $crs_catlg_nbr]['class_assoc'][$cls_assct_nbr]
                    ['description_line'] = $crs_subj_cd . " " . $crs_catlg_nbr . " " . $crs_desc . " (" .
                        $cls_assct_min_unt_nbr . "&amp;ndash;" . $cls_assct_max_unt_nbr . " CR)";
                else
                    $courses[$crs_subj_cd . "-" . $crs_catlg_nbr]['class_assoc'][$cls_assct_nbr]
                    ['description_line'] = $crs_subj_cd . " " . $crs_catlg_nbr . " " . $crs_desc . " (" .
                        $cls_assct_min_unt_nbr . " CR)";

                $courses[$crs_subj_cd . "-" . $crs_catlg_nbr]['class_assoc'][$cls_assct_nbr]
                ['min_credit_hrs'] = $cls_assct_min_unt_nbr;

                $courses[$crs_subj_cd . "-" . $crs_catlg_nbr]['class_assoc'][$cls_assct_nbr]
                ['max_credit_hrs'] = $cls_assct_max_unt_nbr;

            }

            // Repeated information suppress
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
            $instructor_assigned_seq_nbr_keep = $instructor_assigned_seq_nbr;

            $acad_grp_cd_keep = $acad_grp_cd;
            $crs_subj_cd_keep = $crs_subj_cd;
            $subj_catlg_keep = $subj_catlg;
            $crs_catlg_nbr_keep = $crs_catlg_nbr;

        }


        if (isset($courses) && count($courses) > 0 && $courses != "") {
            $data = new Collection($courses, new CourseTransformer);
            return ['data' => $data, 'courses' => $courses];

        }

        return null;
    }

    /**
     * Function computes enrollment numbers
     * pass class counts, combined section Id,acad_term, class session
     *
     * return - total available count
     */
    protected function compute_enrollment_numbers($cls_enrl_cpcty_nbr, $cls_drvd_enrl_cnt,
                                                  $acad_term, $cls_cmb_sect_id, $cls_sesn_cd)
    {

        /** @var Initialize combined section numbers */
        $cs_cls_drvd_enrl_cnt = 0;
        $cs_cls_enrl_cpcty_nbr = 0;

        // Get Combined section info
        $combined_section_info = ClassCombinedSection
            ::where('acad_term_cd', '=', $acad_term)
            ->where('cls_cmb_sect_id', '=', $cls_cmb_sect_id)
            ->where('cls_sesn_cd', '=', $cls_sesn_cd)->get();

        // Sum combined section numbers
        $cs_cls_drvd_enrl_cnt = $combined_section_info->sum('cls_drvd_enrl_cnt');
        //get first for the $cs_cls_enrl_cpcty_nbr
        $combined_section_first = $combined_section_info->first();

        if (isset($combined_section_first) && count($combined_section_first) > 0) {
            $cs_cls_enrl_cpcty_nbr = $combined_section_first->cls_enrl_capcty_nbr;
        }

        // Combined section enrollment numbers difference
        $combined_section_enrl_diff = $cs_cls_enrl_cpcty_nbr - $cs_cls_drvd_enrl_cnt;
        //class difference
        $class_enrl_diff = $cls_enrl_cpcty_nbr - $cls_drvd_enrl_cnt;


        /** Compute total */
        if (count($combined_section_info) > 0 &&
            ($combined_section_enrl_diff < $class_enrl_diff)) {
            $cls_tot_avl_nbr = $combined_section_enrl_diff;
        } else {
            $cls_tot_avl_nbr = $class_enrl_diff;
        }

        return $cls_tot_avl_nbr;

    }

    /***
     * Class Descriptions
     * @param $acad_term
     * @param $cls_nbr
     */
    protected function getClassDescriptions($acad_term, $cls_nbr)
    {
        // Class Descriptions
        $class_description = ClassDescription
            ::where('acad_term_cd', '=', $acad_term)
            ->where('cls_nbr', '=', $cls_nbr)
            ->first();

        if (isset($class_description)) {
            return $class_description->cls_long_desc;
        }

        return "";

    }


}