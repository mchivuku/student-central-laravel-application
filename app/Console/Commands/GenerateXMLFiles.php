<?php
/**
 * Created by
 * User: IU Communications
 * Date: 5/25/16
 */

namespace StudentCentralCourseBrowser\Console\Commands;

use Illuminate\Console\Command;
use StudentCentralCourseBrowser\Jobs as Job;

class GenerateXMLFiles extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'job:GenerateXMLFiles';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate xml files to cache';

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
        $job = new Job\GenerateXMLFiles();
        $job->execute();
    }
}
