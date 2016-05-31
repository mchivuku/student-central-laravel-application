<?php
/**
 * Created by
 * User: IU Communications
 * Date: 5/17/16
 */


namespace StudentCentralCourseBrowser\Jobs;

use Illuminate\Database\Eloquent\Collection;
use Mockery\CountValidator\Exception;
use StudentCentralCourseBrowser\Utils as Utils;

/**
 * Class DatabaseExtensions
 * helper class to manage database functions
 * @package StudentCentralCourseBrowser\Jobs
 */
class DatabaseExtensions
{

    protected $chunk_size = 100;
    protected $connection_name = 'student_central_db';

    /**
     * Insert data into destination table in chunks
     * @param $table
     * @param $collection
     * @param $func
     */
    public function insert($table, $collection, $func)
    {

        $CI = $this;

        $chunks = $collection->chunk($this->chunk_size);


        try{
            // Insert Each chunk
            $chunks->each(
                function ($subset) use ($table, &$func, &$CI) {

                    /** There is more than one item in the collection */
                    if (count($subset) > 1){
                        \DB::connection('student_central_db')->table($table)->insert($CI->pack($subset, $func));
                    }
                    else /** One element  in the chunk */
                        \DB::connection($CI->connection_name)
                            ->table($table)
                            ->insert($func($subset->first()));

                });

        }catch(\Exception $ex){
            var_dump($ex->getMessage());
        }


    }

    /**
     * @param Collection $items
     * @param $func
     * @return array
     */
    public function pack( $items, $func)
    {
        $results = "";

        foreach ($items as $item) {
            $results[] = $func($item);
        }

        return $results;
    }

    /**
     * Truncate table
     * @param $table
     */
    public function truncate($table)
    {
        \DB::connection($this->connection_name)->table($table)->truncate();
    }

}