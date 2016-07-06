<?php
/**
 * Created by
 * User: IU Communications
 * Date: 7/5/16
 */

namespace StudentCentralApp\Console\Commands;

use Illuminate\Console\Command;
use StudentCentralApp\Jobs as Job;

class ImportIntoCourseBrowserDB extends Command
{

    protected $name = 'import:coursedb';


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
     * Execute the command.
     * Command should run all the batch jobs for course database
     * comment or uncomment to choose to run to a single table
     * @return void
     */
    public function handle()
    {

        //1. Import into class table
        $job = new Job\CreateSC911LE3();
        $job->execute();

        echo 'completed CreateSC911LE3.'.PHP_EOL;

        $job = new Job\ImportClassAssociations();
        $job->execute();

        echo 'completed ImportClassAssociations.'.PHP_EOL;

        // 3. Import into class attribute table
        $job = new Job\ImportClassAttributes();
        $job->execute();

        echo 'completed ImportClassAttributes.'.PHP_EOL;

        // 4. Import into class description table
        $job = new Job\ImportClassDescriptions();
        $job->execute();

        echo 'completed ImportClassDescriptions.'.PHP_EOL;

        // 4. Import into class description table
        $job = new Job\ImportCombinedSectionInfo();
        $job->execute();

        echo 'completed ImportCombinedSectionInfo.'.PHP_EOL;

        //5. Import into CrossListings
        $job = new Job\ImportCrossListings();
        $job->execute();

        //6. Import into ERG
        $job = new Job\ImportERG();
        $job->execute();
        echo 'completed ImportERG.'.PHP_EOL;

        //7. Import into ERG2
        $job = new Job\ImportERG2();
        $job->execute();
        echo 'completed ImportERG2.'.PHP_EOL;

        // Import Nonstandard session dates
        $job = new Job\ImportNonStandardSessionDates();
        $job->execute();
        echo 'completed ImportNonStandardSessionDates.'.PHP_EOL;

        //Import JSON Files
        $job = new Job\GenerateJSONFiles();
        $job->execute();
        echo 'completed GenerateJSONFiles.'.PHP_EOL;
        echo PHP_EOL;
    }

}