<style type="text/css">
    table {
        border-collapse: collapse;
    }

    table, td, th {
        border: 1px solid black;
    }
</style>


<h2>Job notification</h2>

<table>
    <thead>
    <th> Job Name </th>
    <th> Status </th>
    <th> Job started </th>
    <th> Job ended </th>
    <th> Job failed</th>
    </thead>
    <tbody>

    <?php

    foreach($jobs as $job){
        echo "<tr>";
        echo sprintf("<td>%s</td><td>%s</td>",
                $job["name"],$job["status"]
                );

        $job_started = isset($job['details']['job_started'])?$job['details']['job_started']:"";
        $job_finished = isset($job['details']['job_finished'])?$job['details']['job_finished']:"";
        $job_failed = isset($job['details']['job_failed'])?$job['details']['job_failed']:"";

        echo "<td>".$job_started."</td>";

        echo "<td>".$job_finished."</td>";
        echo "<td>".$job_failed."</td>";


        echo "</tr>";
    }

    ?>

    </tbody>
</table>

