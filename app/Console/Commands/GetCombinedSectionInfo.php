<?php
/**
 * Created by
 * User: IU Communications
 * Date: 6/1/16
 */



namespace StudentCentralCourseBrowser\Console\Commands;

use Illuminate\Console\Command;
use StudentCentralCourseBrowser\Jobs as Job;

/**
 * Class GetClassNotes
 * @package StudentCentralCourseBrowser\Console\Commands
 */
class GetCombinedSectionInfo extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'job:GetCombinedSectionInfo';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Get combined section data to calculate
                              enrollment numbers - SR_CMB_SECT_GT, SR_CLS_ENRL_CNT_GT';

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
        $job = new Job\GetCombinedSectionInfo() ;
        $job->execute();
        echo 'completed running the job';
    }
}
