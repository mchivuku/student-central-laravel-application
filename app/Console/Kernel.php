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
        Commands\Inspire::class,
        Commands\GetInstDescr::class,
        Commands\GetTermDescr::class,
        Commands\CreateSC911LE3::class,
        Commands\GlobalNotes::class,
        Commands\GetClassNotes::class,
        Commands\GetCombinedSectionInfo::class,
        Commands\CrossListings::class,
        Commands\GetClassAttributes::class,
        Commands\GetERG::class,
        Commands\GetERG2::class,
        Commands\GetClassAssociations::class,
        Commands\GetDepartments::class,
        Commands\GenerateXMLFiles::class
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
