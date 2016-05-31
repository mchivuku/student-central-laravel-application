<?php
/**
 * Created by
 * User: IU Communications
 * Date: 5/29/16
 */

namespace StudentCentralCourseBrowser\Console\Commands;


use Illuminate\Console\Command;
use StudentCentralCourseBrowser\Jobs as Job;

class GlobalNotesNoSubjectArea extends Command
{

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'job:GlobalNotes';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Global notes reads data from SR_GLBL_NTS_GT';

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
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $job = new Job\GlobalNotes();
        $job->execute();
        echo 'Completed the job';
    }
}