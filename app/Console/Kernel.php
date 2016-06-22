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
        $schedule->command('job:BackupDB')
            ->before(function () {
                // Import Class;
                Artisan::call('job:CreateSC911LE3');

                //Import Class Associations
                Artisan::call('job:ImportClassAssociations');

                //Import Class Attributes
                Artisan::call('job:ImportClassAttributes');

                //Import Class Descriptions
                Artisan::call('job:ImportClassDescriptions');

                //Import Class Notes
                Artisan::call('job:ImportClassNotes');

                //Import Combined Section Information
                Artisan::call('job:ImportClassAssociations');

                //Import Cross Listings
                Artisan::call('job:ImportCrossListings');


                //Import Departments
                Artisan::call('job:ImportDepartments');


                //Import ERG
                Artisan::call('job:ImportERG');

                //Import ERG2
                Artisan::call('job:ImportERG2');

            })
            ->dailyAt('15:42');

    }
}
