<?php
/**
 * Created by
 * User: IU Communications
 * Date: 6/22/16
 */

namespace StudentCentralApp\Console\Commands;

use Illuminate\Console\Command;
use StudentCentralApp\Jobs as Job;

class ImportNonStandardSessionDates extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'job:ImportNonStandardSessionDates';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Run job for importing non-standard session dates';

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
        $job = new Job\ImportNonStandardSessionDates();
        $job->execute();
    }
}
