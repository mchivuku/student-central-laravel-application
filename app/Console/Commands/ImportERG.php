<?php
/**
 * Created by
 * User: IU Communications
 * Date: 5/25/16
 */

namespace StudentCentralCourseBrowser\Console\Commands;

use Illuminate\Console\Command;
use StudentCentralCourseBrowser\Jobs as Job;

class ImportERG extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'job:ImportERG';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command imports data from requirement group table to the requirements group table';

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
        $job = new Job\ImportERG() ;
        $job->execute();
    }
}
