<?php
/**
 * Created by
 * User: IU Communications
 * Date: 6/2/16
 */
namespace StudentCentralCourseBrowser\Jobs;

use League\Fractal\Manager;
use League\Fractal\Resource\Collection;
use StudentCentralCourseBrowser\Models as Models;
use StudentCentralCourseBrowser\Transformers\ClassTransformer;
use StudentCentralCourseBrowser\Transformers\CourseTransformer;
use Symfony\Component\Process\Process;

class GenerateJSONFiles extends Job
{

    protected $courseTransformer,$fractal;
    public function __construct()
    {
        parent::__construct('GenerateJSONFiles');
        $this->fractal = new Manager();

    }


    /**
     * Function builds json files for each department with all the courses and classes for that department.
     */
    protected function run()
    {

        $acad_terms = $this->getAcadTerms();

        /** @var Root Folder - that has date_timestamp $path */
        //  $path = $this->makeCoursesFolder();

        $path = '';

        //1.Iterate through terms
        //2.Iterate through departments and build courses and classes;
        collect($acad_terms)->each(/**
         * @param $term
         */

            function ($term) use ($path) {

                $term_info = Models\TermDescription::acadTerm($term)->first();
                //$term_folder_path = $this->makeTermFolder($term, $path);
                $term_folder_path = '';
                /** step 2: get all departments */
                $departments = collect(Models\TermDepartment::acadTerm($term)
                    ->orderBy('crs_subj_dept_cd')->get());

                $allcourses = "";
                /** Iterate through every department */
                $departments->each(/**
                 * @param $dept
                 */
                    function ($dept) use ($term_info, $term_folder_path,$allcourses) {

                        $term = $term_info->term;
                        $desc = $term_info->description;

                        /** @var $classes - classes for the department and term */
                        $classes = Models\ClassTable::where('cls_sc_prnt_ind', '=', 'Y')
                            ->where('acad_term_cd','=',$term)
                            ->where('crs_subj_dept_cd','=', $dept->crs_subj_dept_cd )
                            ->where('cls_stat_cd', '=','A')
                            ->customOrderBy($term)
                            ->distinct()->get();

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
                        $crs_catlg_nbr_keep=0;


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

                            /** @var Course component*/
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
                            $cls_tot_avl_nbr = $this->compute_enrollment_numbers($cls_enrl_cpcty_nbr, $cls_drvd_enrl_cnt, $acad_term,
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
                                $crs_desc_line = $crs_subj_cd . " " . $crs_catlg_nbr . " " . $crs_desc . " (" .
                                    $cls_assct_min_unt_nbr . "&amp;ndash;" . $cls_assct_max_unt_nbr . " CR)";
                            else
                                $crs_desc_line = $crs_subj_cd . " " . $crs_catlg_nbr . " " . $crs_desc . " (" .
                                    $cls_assct_min_unt_nbr . " CR)";

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
                                $class_notes_a = Models\ClassNotes::classNotesA($cls_key)
                                    ->distinct()
                                    ->get()
                                    ->map(function ($notes) {
                                        return [$notes->nts_nbr_long_desc,
                                              $notes->nts_long_desc];
                                    })->toArray();

                                $courses[$crs_catlg_nbr]
                                ['class_assoc'][$cls_assct_nbr][$cls_key]
                                ['class_notes_a'] = array_filter(array_flatten($class_notes_a),function($x){
                                    return isset($x)&&$x!='';
                                });

                            }

                            /** Course level properties */
                            if ($crs_catlg_nbr_keep != $crs_catlg_nbr) {

                                $courses[$crs_catlg_nbr]['term']=['term'=>$term,'desc'=>$term_info->description];
                                $courses[$crs_catlg_nbr]['department']=['crs_sub_dept_cd'=>$dept->crs_sub_dept_cd,
                                    'crs_subj_desc'=>$dept->crs_subj_desc];

                                $courses[$crs_catlg_nbr]['crs_desc_line'] = $crs_desc_line;
                                $courses[$crs_catlg_nbr]['crs_subj_dept_cd'] = $crs_subj_dept_cd;
                                $courses[$crs_catlg_nbr]['crs_subj_line'] = $crs_subj_line;
                                $courses[$crs_catlg_nbr]['crs_cmpnt_line']
                                    = $crs_cmpnt_line;//course type - discussion, lecture, lab etc.
                                $courses[$crs_catlg_nbr]['crs_catlg_nbr']=$crs_catlg_nbr;
                                $courses[$crs_catlg_nbr]['crs_cmpnt_cd']
                                    = $crs_cmpnt_cd;

                            }


                            //! CHECK FOR SAME COMPONENT, NOT PRIMARY COMPONENT - non credit class
                            if ($crs_catlg_nbr != $crs_catlg_nbr_keep && $cls_cmpnt_cd != $crs_cmpnt_cd) {

                                $courses
                                [$crs_catlg_nbr]['class_assoc'][$cls_assct_nbr]
                                [$cls_key]['cls_cmpnt_cd'] = $crs_cmpnt_cd;

                                $courses
                                [$crs_catlg_nbr]['class_assoc'][$cls_assct_nbr][$cls_key]['cls_cmpnt_desc']
                                    = $crs_cmpnt_line;

                                if ($crs_desc_line_keep != $crs_desc_line) {
                                    $err_badClsAssoc = '** ERROR - ' . $crs_subj_cd . " " . $crs_catlg_nbr . ' **';
                                    $courses[$crs_catlg_nbr]['class_assoc'][$cls_assct_nbr][$cls_key]
                                    ['err_badClsAssoc'] = $err_badClsAssoc;
                                }

                            } else {

                                $courses
                                [$crs_catlg_nbr]['class_assoc'][$cls_assct_nbr][$cls_key]['cls_cmpnt_cd']
                                    = $crs_cmpnt_cd;
                                $courses
                                [$crs_catlg_nbr]['class_assoc'][$cls_assct_nbr][$cls_key]['cls_cmpnt_desc']
                                    = $crs_cmpnt_line;


                                if ($cls_cmpnt_cd != $crs_cmpnt_cd && $crs_desc_line_keep != $crs_desc_line) {
                                    $err_badClsAssoc = '** ERROR - ' . $crs_subj_cd . " " . $crs_catlg_nbr . ' **';
                                    $courses [$crs_catlg_nbr]['class_assoc'][$cls_assct_nbr][$cls_key]
                                    ['err_badClsAssoc'] = $err_badClsAssoc;
                                }
                            }

                            if (trim($crs_tpc_desc) != "") {
                                if ($cls_key_keep != $cls_key) {
                                    $courses[$crs_catlg_nbr]['class_assoc'][$cls_assct_nbr][$cls_key]['crs_tpc_desc'] = 'VT:' . $crs_tpc_desc;;
                                }
                            }

                            if ($cls_key_keep != $cls_key) {
                                //Notes_B

                                $ti_crse_list = "";
                                $class_notes_b = Models\ClassNotes::classNotesB($cls_key)
                                    ->distinct()
                                    ->get()
                                    ->map(function ($notes) use (&$ti_crse_list) {

                                        return [$notes->nts_nbr_long_desc,
                                            $notes->nts_long_desc];
                                    })->flatMap(function ($item) {
                                        return $item;
                                    })->filter(function ($note) {
                                        return isset($note) && $note != '';
                                    })->toArray();

                                //Transfer Indiana Initiative;
                                $ti_course = $cls_drvd_sesn_cd . $crs_subj_cd . $crs_catlg_nbr;
                                $CrseRslt = 1;

                                $CrseRslt = strpos($ti_crse_list, $ti_course, 0);
                                if ($CrseRslt == 0) {
                                    //Transfer Indiana Initiative
                                    $transferIndianaInitiative = Models\ClassNotes::where('CLS_KEY', '=', $cls_key)
                                        ->distinct()
                                        ->get()->map(function ($note) {
                                            if ($note->cls_nts_nbr == $this->ti_inst)
                                                return $note->nts_nbr_long_desc;
                                        });


                                }
                                $courses[$crs_catlg_nbr]['class_assoc'][$cls_assct_nbr][$cls_key]['class_notes_b'] = $class_notes_b;

                                $ti_crse_list = $ti_crse_list . $cls_drvd_sesn_cd . $crs_subj_cd . $crs_catlg_nbr;

                            }

                            if ($cls_key_keep != $cls_key) {

                                if ($cls_typ_ind != 'E')
                                    $holdstars = '*****';
                                else
                                    $holdstars = $cls_nbr;

                                $courses[$crs_catlg_nbr]['class_assoc'][$cls_assct_nbr][$cls_key]['cls_nbr'] = $holdstars;
                                $courses[$crs_catlg_nbr]['class_assoc'][$cls_assct_nbr][$cls_key]['clsd'] = $cls_clsd_cd;

                                //Consent check
                                $cls_cnst_typ_req_cd = "";
                                if ($class->cls_cnst_typ_req_cd=='D'|| $class->cls_cnst_typ_req_cd=='I')
                                    $cls_cnst_typ_req_cd = 'PERM';


                                // check consent type if not PERM
                                if ($cls_cnst_typ_req_cd!="PERM") {
                                    if ($cls_rqmt_grp_cd != '') {
                                        $rqmt_grp_cd = $cls_rqmt_grp_cd;
                                        $getERG = Models\RequirementGroup
                                            ::where('acad_rqgrp_cd', '=', $rqmt_grp_cd)
                                            ->where('acad_rqmt_ln_typ_cd', '=', 'COND')->get();

                                        if (count($getERG) > 0) {
                                            $cls_cnst_type_req_cd = 'RSTR';
                                            $courses
                                            [$crs_catlg_nbr]['class_assoc'][$cls_assct_nbr][$cls_key]['cls_cnst_type_req_cd']
                                                = $cls_cnst_type_req_cd;
                                        }

                                        if ($cls_cnst_type_req_cd != 'RSTR') {
                                            $getERG2 = Models\ReservationCapacity
                                                ::where('cls_key', '=', $cls_key)->get();

                                            if (count($getERG2) > 0) {
                                                $cls_cnst_type_req_cd = 'RSTR';
                                                $courses
                                                [$crs_catlg_nbr]['class_assoc'][$cls_assct_nbr]
                                                [$cls_key]['cls_cnst_type_req']
                                                    = $cls_cnst_type_req_cd;
                                            }
                                        }
                                    }

                                }else{

                                    $courses
                                    [$crs_catlg_nbr]['class_assoc']
                                    [$cls_assct_nbr][$cls_key]['cls_cnst_type_req']
                                        = $cls_cnst_typ_req_cd;
                                }

                            }


                            /** @var class details $details */
                            $details = "";

                            /** Start Time */
                            if ($cls_mtg_strt_tm == "")
                                $details['cls_mtg_strt_tm'] = "ARR";
                            else
                                $details['cls_mtg_strt_tm'] = $cls_mtg_strt_tm;

                            /** End Time */
                            if ($cls_mtg_end_tm == "")
                                $details['cls_mtg_end_tm'] = "ARR";
                            else
                                $details['cls_mtg_end_tm'] = $cls_mtg_end_tm;

                            /** Meeting Pattern */
                            if ($class->cls_drvd_mtg_ptrn_cd == " ")
                                $details['cls_drvd_mtg_ptrn_cd'] = "ARR";
                            else
                                $details['cls_drvd_mtg_ptrn_cd'] = $class->cls_drvd_mtg_ptrn_cd;

                            /** Facility  */
                            if (trim($facil_bldg_cd) == "") {
                                $details['facil_bldg_cd'] = "ARR";
                                $details['facil_bldg_rm_nbr'] = "";
                            } else {
                                $details['facil_bldg_cd'] = str_replace("BL", "", $facil_bldg_cd);
                                $details['facil_bldg_rm_nbr'] = ($facil_bldg_rm_nbr);
                            }

                            /** Instructor  */
                            $details['instructor'] = $cls_instr_nm;
                            $courses[$crs_catlg_nbr]
                            ['class_assoc'][$cls_assct_nbr][$cls_key]['details'][] = $details;


                            if ($cls_key_keep != $cls_key) {
                                $courses
                                [$crs_catlg_nbr]['class_assoc'][$cls_assct_nbr][$cls_key]['cls_enrl_cpcty_nbr']
                                    = $cls_enrl_cpcty_nbr;

                                $courses
                                [$crs_catlg_nbr]['class_assoc'][$cls_assct_nbr][$cls_key]['cls_tot_avl_nbr']
                                    = $cls_tot_avl_nbr;

                                $courses
                                [$crs_catlg_nbr]['class_assoc'][$cls_assct_nbr][$cls_key]['cls_wlst_tot_nbr']
                                    = $cls_wlst_tot_nbr;

                                // Class Description
                                 $courses
                                 [$crs_catlg_nbr]['class_assoc'][$cls_assct_nbr][$cls_key]['cls_long_desc']
                                  = $this->GetClassDescriptions($term,$cls_nbr);
                            }


                            if ($crs_mtg_tpc_desc != '' && ($cls_key_keep != $cls_key)
                                || $cls_key_keep == $cls_key && $cls_mtg_ptrn_nbr_keep != $cls_mtg_ptrn_nbr
                            ) {


                                $courses
                                [$crs_catlg_nbr]['class_assoc'][$cls_assct_nbr][$cls_key]['crs_mtg_tpc_desc']
                                    = $crs_mtg_tpc_desc;

                            }


                            /** Get Class Attributes */
                            if ($cls_key_keep != $cls_key) {

                                // class attributes
                                $attributes = Models\ClassAttribute::where('cls_key', '=', $cls_key)
                                    ->distinct()->select('crs_attrib_val_cd','crs_attrib_val_desc')->get()->toArray();

                                $courses[$crs_catlg_nbr]['class_assoc'][$cls_assct_nbr][$cls_key]['class_attributes']
                                    = $attributes;

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
                            $crs_catlg_nbr_keep= $crs_catlg_nbr;

                        }

                            $allcourses= array_merge($allcourses,$courses);

                         $data = (new Collection($courses,new CourseTransformer));
                         echo ($this->fractal->createData( $data )->toJson());
                         exit;



                    });


            });
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
        $combined_section_info = Models\ClassCombinedSection
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
        if (count($combined_section_info) > 0 && ($combined_section_enrl_diff < $class_enrl_diff)) {
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
    protected function GetClassDescriptions($acad_term,$cls_nbr){
        // Class Descriptions
        $class_description = Models\ClassDescription
            ::where('acad_term_cd', '=', $acad_term)
            ->where('cls_nbr', '=', $cls_nbr)
            ->first();

        if(isset($class_description)){
            return $class_description->cls_long_desc;
        }

        return "";

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


    /**
     * Save JSON to file
     * @param $path
     * @param $filename
     * @param $contents
     */
    public function saveJsonToFile($path,$filename,$contents){

        if(!file_exists($filename))
            $fd = fopen($path."/".$filename,"w");
        else
            $fd = fopen($path."/".$filename,"a+");

        fwrite($fd,$contents);
        fclose($fd);
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