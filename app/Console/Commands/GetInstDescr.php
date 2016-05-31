<?php

namespace StudentCentralCourseBrowser\Console\Commands;

use Illuminate\Console\Command;
use StudentCentralCourseBrowser\Jobs as Job;

class GetInstDescr extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'job:GetInstDescr';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

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
        $job = new Job\GetInstDescr() ;
        $job->execute();
    }
}
