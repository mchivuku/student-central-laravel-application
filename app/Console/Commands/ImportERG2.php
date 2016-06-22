<?php
/**
 * Created by
 * User: IU Communications
 * Date: 5/25/16
 */

namespace StudentCentralApp\Console\Commands;

use Illuminate\Console\Command;
use StudentCentralApp\Jobs as Job;

class ImportERG2 extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'job:ImportERG2';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command imports data from reservation capacity table into reservation capacity table';

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
        $job = new Job\ImportERG2() ;
        $job->execute();
    }
}
