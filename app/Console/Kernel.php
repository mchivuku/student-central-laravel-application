<?php

namespace StudentCentralApp\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;
use Illuminate\Support\Facades\Artisan;

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
        Commands\GenerateJSONFiles::class,

        Commands\ImportNonStandardSessionDates::class,
        Commands\SendJobNotification::class,
        Commands\ImportIntoCourseBrowserDB::class,
        Commands\ImportTerms::class
    ];

    /**
     * Define the application's command schedule.
     *
     * @param  \Illuminate\Console\Scheduling\Schedule $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {
        /** @var Data ware house has some weird hour schedule during weekends $weekDay */
        $weekDay = date('w');
        if($weekDay == 0 || $weekDay == 6)
            return;

            $schedule->command('import:coursedb')->before(function () {
                 Artisan::call('job:BackupDB');
                 echo 'Completed backup';
            })->after(
                function() {
                    Artisan::call('email.notify');
            })->dailyAt("06:05");

     //$schedule->command('job:GenerateJSONFiles')->dailyAt('07:29');


    }
}
