<?php
/**
 * Created by
 * User: IU Communications
 * Date: 5/25/16
 */

namespace StudentCentralApp\Console\Commands;

use Illuminate\Console\Command;
use StudentCentralApp\Jobs as Job;

class ImportClassAttributes extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'job:ImportClassAttributes';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Gets class attributes for the course';

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
        $job = new Job\ImportClassAttributes() ;
        $job->execute();
    }
}
