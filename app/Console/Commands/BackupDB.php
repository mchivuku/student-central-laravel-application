<?php
/**
 * Created by
 * User: IU Communications
 * Date: 5/25/16
 */

namespace StudentCentralApp\Console\Commands;

use Illuminate\Console\Command;
use StudentCentralApp\Jobs as Job;

class BackupDB extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'job:BackupDB';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Job loads the current data into backup tables before loading the tables with fresh data';

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
        $job = new Job\BackupDB() ;
        $job->execute();
        echo 'Job executed successfully';
    }
}
