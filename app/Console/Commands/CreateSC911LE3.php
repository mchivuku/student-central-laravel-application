<?php
/**
 * Created by
 * User: IU Communications
 * Date: 5/25/16
 */

namespace StudentCentralCourseBrowser\Console\Commands;

use Illuminate\Console\Command;
use StudentCentralCourseBrowser\Jobs as Job;

class CreateSC911LE3 extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'job:CreateSC911LE3';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Implements the functionality that is in the procedure CreateSC911LE3 from SQR';

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
        $job = new Job\CreateSC911LE3() ;
        $job->execute();
    }
}
