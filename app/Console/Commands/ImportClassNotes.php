<?php
/**
 * Created by
 * User: IU Communications
 * Date: 5/31/16
 */

namespace StudentCentralCourseBrowser\Console\Commands;

use Illuminate\Console\Command;
use StudentCentralCourseBrowser\Jobs as Job;

/**
 * Class GetClassNotes
 * @package StudentCentralCourseBrowser\Console\Commands
 */
class ImportClassNotes extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'job:ImportClassNotes';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Get class notes from - DSS_RDS.SR_CLS_NTS_GT';

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
        $job = new Job\ImportClassNotes() ;
        $job->execute();
    }
}
