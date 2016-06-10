<?php

namespace StudentCentralCourseBrowser\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * The Artisan commands provided by your application.
     *
     * @var array
     */
    protected $commands = [
        Commands\BackupDB::class,

        Commands\Inspire::class,
        Commands\ImportInstDescr::class,
        Commands\ImportTermDescr::class,
        Commands\CreateSC911LE3::class,
        Commands\GlobalNotes::class,
        Commands\ImportClassNotes::class,
        Commands\ImportCombinedSectionInfo::class,
        Commands\ImportCrossListings::class,
        Commands\ImportClassAttributes::class,
        Commands\ImportERG::class,
        Commands\ImportERG2::class,
        Commands\ImportClassAssociations::class,
        Commands\ImportDepartments::class,
        Commands\ImportClassDescriptions::class,
        Commands\GenerateJSONFiles::class
    ];

    /**
     * Define the application's command schedule.
     *
     * @param  \Illuminate\Console\Scheduling\Schedule  $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {
        // $schedule->command('inspire')
        //          ->hourly();
    }
}
