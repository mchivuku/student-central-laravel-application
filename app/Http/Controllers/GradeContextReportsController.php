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

        return $records;
    }

    /***
     * Get request
     * @param Request $request
     */
    public function getReport(Request $request){

        $term = $request->post('derterm');
        $auth_key = $request->post('derive');
        $username = $request->post("derpath");
        $newid = $request->post("deruid");

        $term_path = $term . "/";

        /** @var NEED TO UPDATE THIS accordingly $DESTDIR */
        $DESTDIR = "SMB DRIVE PATH OR SFTP PATH ";

        $enc_dir = $DESTDIR . $term_path;

        // determine the filename
        $filename = $this->obfuscate($term."key.key");
        $key = file_get_contents($enc_dir . $filename);
        $key = decrypt_data($key, config('app.master_key'),
            config('app.master_iv'));

        /** @var IV $filename */
        $filename = obfuscate($term . "iv.key");
        $iv = file_get_contents($enc_dir . $filename);
        $iv = decrypt_data($iv, config('app.master_key'),
            config('app.master_iv'));


        $filename = obfuscate($username);

        //For terms after 4102, determine filename based on UID
        $filename2 = obfuscate($newid);

        if (file_exists($enc_dir . $filename)){

            $data = file_get_contents($enc_dir . $filename);
            $data = decrypt_data($data, $key, $iv);

            $headers = array(
                "Pragma: public",
                "Cache-Control: public",
                "Content-Disposition: attachment; filename=".$filename2."",
                'Content-Length: '. filesize($enc_dir . $filename)
            );

            return response()->download($enc_dir . $filename, $filename2, $headers);

        }
    }

    private function obfuscate($string){

            $obfuscated = md5($string);
            $obfuscated = sha1($obfuscated);
            return($obfuscated);

    }


}