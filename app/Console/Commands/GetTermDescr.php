<?php

namespace StudentCentralCourseBrowser\Console\Commands;

use Illuminate\Console\Command;
use StudentCentralCourseBrowser\Jobs as Job;

class GetTermDescr extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'job:GetTermDescr';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Run job for ready term description';

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
        /** @var GetTermDescr $job */
        $job = new Job\GetTermDescr();
        $job->execute();
    }
}
