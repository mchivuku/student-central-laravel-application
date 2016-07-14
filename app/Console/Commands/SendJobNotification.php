<?php
/**
 * Created by
 * User: IU Communications
 * Date: 6/28/16
 */

namespace StudentCentralApp\Console\Commands;


use Illuminate\Console\Command;

class SendJobNotification extends Command
{
    protected $name = 'email.notify';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the command.
     *
     * @return void
     */
    public function handle()
    {

        $jobs = \DB::connection('coursebrowser')->table('job_log')->select(array("*"))
            ->whereRaw('DATE(`timestamp`) = CURDATE() and event like "Job%"')->get();

        // groups jobs - by name - to capture stats
        $result = [];
        foreach ($jobs as $job) {
            $name = $job->name;
            $result[$name][] = $job;
        }

        $data = array_map(function ($job) {
            $details = "";
            $status = "";
            $name = "";

            foreach ($job as $v) {

                $name = $v->name;
                switch ($v->event) {
                    case "Job start":
                        $details['job_started'] =
                            date("F j, Y, g:i a", strtotime($v->timestamp));
                        break;

                    case "Job finish":
                        $details['job_finished'] =
                            date("F j, Y, g:i a", strtotime($v->timestamp));
                        $status = "Success";
                        break;

                    case "Job Failed":
                        $details['job_failed'] =
                            date("F j, Y, g:i a", strtotime($v->timestamp)) .
                            " <br/>" . $v->message;
                        $status = "Failed";
                        break;

                }

            }

            return ["name" => $name, "status" => $status, "details" => $details];
        }, $result);

        $emails = ['mbcalver@iu.edu', 'mercerjd@iu.edu'];

        \Mail::send('emails.notify', ["jobs" => $data],
            function ($message) use ($emails) {
                $message->subject('Student Central - Course Browser - '
                    . date("F j, Y"))
                    ->to($emails);
            });
    }
}