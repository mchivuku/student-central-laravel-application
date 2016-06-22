<?php

namespace StudentCentralApp\Console\Commands;

use Illuminate\Console\Command;
use StudentCentralApp\Jobs as Job;

class ImportTermDescr extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'job:ImportTermDescr';

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
        $job = new Job\ImportTermDescr();
        $job->execute();
    }
}
