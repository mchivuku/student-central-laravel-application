<?php
/**
 * Created by
 * User: IU Communications
 * Date: 6/20/16
 */

namespace StudentCentralApp\Http\Controllers;

use StudentCentralApp\Transformers\GradeDistribution as Transformer;
use Illuminate\Http\Request;

class GradeContextReportsController extends Controller
{
    /** return form */
    public function index(Request $request){
        return view('gradecontext.index');
    }
    /***
     * Return listings.
     * @param Request $request
     */
    public function get(Request $request)
    {
        $universityId = $request->get("univid");
        $userNetworkId = $request->get("networkId");

        if (!isset($universityId)) return;

        $query = "SELECT SR_GRMAILER_T.INST,SR_GRMAILER_T.TERM,SR_GRMAILER_T.UNIVID,SR_GRMAILER_T.NETWID, " .
            "SR_GRMAILER_T.NAME,SR_GRMAILER_T.TERMDESC,SR_GRMAILER_T.MTYPE FROM SR_GRMAILER_T WHERE " .
            "(((SR_GRMAILER_T.UNIVID= '" . $universityId . "'))
			  OR ((SR_GRMAILER_T.NETWID= '" . $userNetworkId . "') and (SR_GRMAILER_T.TERM <= '4102'))) AND SR_GRMAILER_T.MTYPE = 'FIN' " .
            "ORDER BY SR_GRMAILER_T.TERM desc,SR_GRMAILER_T.MTYPE";

        $records = \DB::connection("gradecontextdb")->select($query);

        echo "<pre>";
        print_r($records);

    }

}