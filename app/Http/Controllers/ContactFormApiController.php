<?php
/**
 * Created by
 * User: IU Communications
 * Date: 6/24/16
 */

namespace StudentCentralApp\Http\Controllers;

use Illuminate\Http\Request;
use League\Fractal\Manager;
use League\Fractal\Resource\Collection;
use Mockery\CountValidator\Exception;


/**
 * Class ContactFormApiController
 * @package StudentCentralApp\Http\Controllers
 *
 * Contact Form - contact form controller -
 * return person data, creates an entry in the database after form post.
 */
class ContactFormApiController extends BaseApiController
{
    protected $allowed_file_ext = ["xls", "xlsx", "csv", "doc", "docx", "pdf", "jpg", "jpeg"];

    protected static $topicOptions = [
        "Financial Aid" => ["fa1" => "Scholarships &amp; Grants",
            "fa2" => "Financial Aid Application (FAFSA)",
            "fa3" => "Student Loans",
            "fa4" => "Verification (FAFSA)",
            "fa5" => "Appeals",
            "fa6" => "Financial Awareness/Financial Literacy",
            "fa8" => "Satisfactory Academic Progress",
            "fa7" => "Other Financial Aid Documentation",
        ],
        "Student Records" => [
            "sr1" => "Drop/Add a Class",
            "sr2" => "Enrollment Certification",
            "sr3" => "Update Graduation Date",
            "sr4" => "Update Name or Profile",
            "sr5" => "Citizenship Verification Questions",
            "sr6" => "Update Residency",
            "sr7" => "Update Immunization",
            "sr8" => "Audit All Class(es)"
        ],
        "Student Accounts" => ["ac1" => "Account Balance Inquiry", "ac2" => "Refund",
            "ac3" => "Billing/Payment", "ac4" => "Third Party Users", "ac5" => "Holds",
            "ac6" => "Drop/Add - Impact on Tuition", "ac7" => "Personal Deferment Option"],
        "Transcripts &amp; Diplomas" => ["td1" => "Request Transcript", "td2" => "Transcript Question",
            "td3" => "Pick Up Pre-Ordered Transcript", "td4" => "Pick Up Diploma"]
    ];

    public function __construct(Manager $fractal)
    {
        parent::__construct($fractal);
    }

    /** Function to return json view of topics */
    public function getTopics(){
        $data = ['data' => collect(self::$topicOptions)];
        return json_encode($data);
    }

    /**
     * Function returns user information from the people database.
     */
    public function getUser($username)
    {

        $user_info = \DB::connection("contactformdb")->table("BREGDSS.SR_PRSN_EXTRT_T")
            ->select("PRSN_UNIV_ID", "PRSN_PRM_LAST_NM", "PRSN_PRM_1ST_NM", "PRSN_GDS_CMP_EMAIL_ADDR")
            ->where('PRSN_NTWRK_ID', '=', strtoupper($username))->get();

        /** @var Check if there is a contact id or if the user already submitted to the database $cid */
        $cid = "";

        return $this->fractal
            ->createData(new Collection($user_info, function ($user)use($username) {
                return
                    ["universityId" => $user->prsn_univ_id,
                        "last_name" => $user->prsn_prm_last_nm,
                        "first_name" => $user->prsn_prm_1st_nm,
                        "email_address" => $user->prsn_gds_cmp_email_addr,
                        'networkId'=>$username
                    ];

            }))->toJson();

    }

    /**
     * Function handles contact form submit
     */
    public function submit(Request $request)
    {

        // check request - return json response with errors;
        // 1. check the file is in the allowed extensions
        if ($request->hasFile('upfile')) {

            // check the file extensions
            $file = $request->file("upfile");
            $ext = $file->getClientOriginalExtension();

            // not a requested mime type
            if (!in_array($ext, $this->allowed_file_ext))
                return $this->respondWithError("Invalid file format - xls, xlsx, csv, doc, docx, .jpg, .jpeg and pdf only");

            /** Check if the file is uploaded appropriately */
            if (!$file->isValid())
                return $this->respondWithError("Uploaded file has errors");


            /** save the data into database */
           return $this->save($request);
        }

        /** save the data into database */
        return $this->save($request);

    }

    /** Respond if there is an error */
    protected function respondWithError($message = "",
                                        $headers = [])
    {

        $msg = isset($message) && $message != "" ? $message :
            "There was an error with request";

        return response()->json(["data" => ["message" => $msg]],
            self::$error,
            $headers);
    }

    private function getTopicDescription($topic){

        foreach(self::$topicOptions as $topicOption){
            foreach($topicOption as $k=>$v)
            {
                if($k==$topic)return $v;
            }
        }
        return "";
    }
    /** Helper functions */
    protected function save(Request $request)
    {

        $fn = $request->get('fname');
        $ln = $request->get('lname');
        $email = $request->get('email');
        $uid = $request->get('ouid');
        $snm = $request->get('snm');
        $ouid = $request->get('ouid');
        $hash = $request->get("hash");
        $tpc = $request->get('topic');
        $descr =$this->getTopicDescription($tpc);

        $txt = $request->get('comment');
        $user = $request->get('networkId');


        // get the contact Id - select STDNTCEN.CONTACT_ID.NEXTVAL AS CONTACT_ID FROM dual
        $contact = collect(\DB::connection("contactformdb")
            ->select("select STDNTCEN.CONTACT_ID.NEXTVAL AS CONTACT_ID FROM dual"))->first();

        if (!isset($contact))
            return $this->respondWithError("There was an error saving the data");

        $contact_id = $contact->contact_id;
        $tid = strtoupper($tpc) . $contact_id;

        // insert into contact
        \DB::connection("contactformdb")->table('SR_CONTACT_T')
            ->insert(['contact_id'=> $contact_id,
                'fname' => $fn, 'lname' => $ln, 'email' => $email,
                'prsn_univ_id' => $uid, 'stdnt_nm' => $snm,
                'stdnt_uid' => $ouid, 'topic_code' => $tpc,
                'topic_descr' => $descr, 'msg_txt' => $txt,
                'network_id' => $user,
                'transaction_id' => $tid,
                'created_ts' => \DB::connection("contactformdb")->raw('sysdate')

        ]);


        if (!$request->has('hash'))
            $hash = md5(uniqid(mt_rand(), true));
        else
            $hash = $request->get('hash');

        // save the file as blob.
        // begin transaction - select STDNTCEN.UPLOAD_ID.NEXTVAL
        // AS UPLOAD_ID FROM dual - get upload id
        // INSERT INTO STDNTCEN.SR_CONTACT_UPLOADS_T (CONTACT_ID, UPLOAD_FILE,
        // UPLOAD_NM, UPLOAD_ID,
        // UPLOAD_DATE, SESSION_HASH)
        $upload_id="";

            if ($request->hasFile('upfile')) {

                $upload = collect(\DB::connection("contactformdb")
                    ->select("select STDNTCEN.UPLOAD_ID.NEXTVAL  AS UPLOAD_ID FROM dual"))
                    ->first();
                $upload_id = $upload->upload_id;


                // Yajra\Oci8\Query\OracleBuilder  - has blob - save file method
                $upload_nm = $request->file('upfile')->getClientOriginalName();

                //** Building connection and inserting */
                $connections = \StudentCentralApp\Utils\DBConfigHandler::connections();
                $conn_params = $connections['contactformdb'];

                $conn = oci_connect($conn_params['username'], $conn_params['password'],
                    sprintf("%s/%s", $conn_params['host'],
                        $conn_params['service_name'])
                );

                $lob = oci_new_descriptor($conn, OCI_D_LOB);

                $stmt = oci_parse($conn, 'INSERT INTO
                STDNTCEN.SR_CONTACT_UPLOADS_T (CONTACT_ID, UPLOAD_FILE, UPLOAD_NM, UPLOAD_ID,
            UPLOAD_DATE, SESSION_HASH) '
                    . 'VALUES(:CID, EMPTY_BLOB(), :UPLOAD_FILE,
                :UPLOAD_ID, sysdate, :SESSHASH) RETURNING
                 UPLOAD_FILE INTO :BLOBDATA');

                oci_bind_by_name($stmt, ':UPLOAD_FILE', $upload_nm);
                oci_bind_by_name($stmt, ':CID', $contact_id);
                oci_bind_by_name($stmt, ':UPLOAD_ID', $upload_id);
                oci_bind_by_name($stmt, ':SESSHASH', $hash);
                oci_bind_by_name($stmt, ':BLOBDATA', $lob, -1, OCI_B_BLOB);

                oci_execute($stmt, OCI_DEFAULT);
                $lob->savefile($_FILES['upfile']['tmp_name']);
                oci_commit($conn);


            }


         // send email to the user and return
        $nm = $fn." ". $ln;
        $data =['tid'=>$tid,'lbl'=>$descr,'nm'=> $fn.' '.$ln,
        'email'=>$email,'uid'=>$uid,'snm'=>$snm,'ouid'=>$ouid,
        'upload_id'=>$upload_id,'text'=>$txt
        ];
        \Mail::send('emails.contactform', $data,
            function($message)use($email,$nm,$tid,$descr)
           {
               $message->to($email, $nm)->subject("[ID:".$tid."] We have received your ".$descr." inquiry ");
         });

        if( count(\Mail::failures()) > 0 ) {
            //INSERT INTO STDNTCEN.SR_CONFIRM_ERRORS_T (CONTACT_ID, LOG_DT)
	          //           VALUES (:zid, sysdate)
            \DB::connection("contactformdb")->insert(['contact_id'=>$contact_id,
                'log_dt'=>\DB::connection("contactformdb")->raw('sysdate')]);

        }else{

            //update confirmation sent successfully
            \DB::connection("contactformdb")->update(
                \DB::connection("contactformdb")->raw('update
                SR_CONTACT_T set confirmation_sent=sysdate
                where contact_id='.$contact_id));

        }

        return response()->json(["data" => ["message" => 'successfully sent',
            'statusCode'=>self::$success]],
            self::$success
            );

    }

    /**
     * Function to test if uploaded file is correct.
     * @param $uploadId
     */
    public function testUploadedBLOB($uploadId){
        // $query = 'SELECT UPLOAD_FILE FROM
        //STDNTCEN.SR_CONTACT_UPLOADS_T WHERE UPLOAD_ID = :UPLOAD_ID' ;

        $upload = \DB::connection("contactformdb")
            ->table('SR_CONTACT_UPLOADS_T')
            ->select('upload_file','upload_nm')
            ->where('upload_id','=',$uploadId)->first();

        $report =($upload->upload_nm);
        if ((stristr($report,'.')== "xls") and (!stristr($report,'.')== "xlsx")) {
            header("Content-type: application/vnd.ms-excel;");
        } elseif (stristr($report,'.')== "xlsx") {
            header("Content-type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet;");
        } elseif ((stristr($report,'.')== "doc") and (!stristr($report,'.')== "docx")) {
            header("Content-type: application/msword;");
        } elseif (stristr($report,'.')== "docx") {
            header("Content-type: application/vnd.openxmlformats-officedocument.wordprocessingml.document;");
        } elseif (stristr($report,'.')== "pdf") {
            header("Content-type: application/pdf;");
        } elseif (stristr($report,'.')== "txt") {
            header("Content-type: text/plain");
        } elseif (stristr($report,'.')== "rtf") {
            header("Content-type: application/rtf;");
        } elseif (stristr($report,'.')== "csv") {
            header("Content-type: text/csv;");
        } elseif (stristr($report,'.')== "jpg" or stristr($report,'.')== "jpeg") {
            header("Content-type: image/jpeg;");
        } else {
            //header("Content-type: text/plain;");
            header("Content-type: application/octet-stream;");
        }
        header("Content-Disposition: attachment; filename=$report");

        echo $upload->upload_file;
    }

}