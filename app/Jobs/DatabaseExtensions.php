<?php
/**
 * Created by
 * User: IU Communications
 * Date: 5/17/16
 */


namespace StudentCentralCourseBrowser\Jobs;

use Illuminate\Database\Eloquent\Collection;
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
    public function insert($table, $collection, $func, $chunksize = null)
    {

        $CI = $this;

        if (isset($chunksize)) {
            $chunks = $collection->chunk($chunksize);
        } else {
            $chunks = $collection->chunk($this->chunk_size);
        }


        try {


            $chunks->each(
                function ($subset) use ($table, &$func, &$CI) {

                    /** There is more than one item in the collection */
                    if (count($subset) > 1) {
                        \DB::connection('student_central_db')->table($table)
                            ->insert($CI->pack($subset, $func));

                    } else /** One element  in the chunk */
                        \DB::connection($CI->connection_name)
                            ->table($table)
                            ->insert($func($subset->first()));

                });

        } catch (\Exception $ex) {
            var_dump($ex->getMessage());
        }


    }

    /**
     * @param Collection $items
     * @param $func
     * @return array
     */
    public function pack($items, $func)
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


    /**
     * Function to read data from dss_prod in chunks
     * @param $query
     */
    public function readDataInChunksDSSPRODAndImport($query, $func, $chunksize = null)
    {

        /**
         * iterate through each acad term and retrieve 200 records each loop
         * Choose lowest chunk size so that the data can be read - job crashes on low memory
         */
        if (!isset($chunksize)) {
            $chunksize = $this->chunk_size;
        }

        $start_at = 0;
        $number_read = 0;
        $total = 0;
        $end_at = $chunksize;

        do {


            $sql = $query .
                " " . $this->limitClause($end_at) . " " .
                $this->orderByClause();


            $new_query = "select * from ($sql) where rn> " . $start_at;

            $pdo = \DB::connection('oracle')->getPdo();
            $statement = $pdo->prepare($new_query);

            if ($statement->execute()) {

                $data = $statement->fetchAll(\PDO::FETCH_ASSOC);
                $func($data);

            }

            /** Compute data pointers */
            $number_read = count($data);
            $total += $number_read;
            $start_at += $number_read;
            $end_at += $chunksize;
            echo 'Number Read - ' . $number_read . " Total - " . $total;
            echo PHP_EOL;


        } while ($number_read >= $chunksize);

    }


    /***
     * Limit 100 results each time
     * @param $end
     * @return string
     */
    protected function limitClause($end)
    {
        return " AND rownum <= $end";
    }

    protected function orderByClause()
    {
        return " order by rownum";
    }

}