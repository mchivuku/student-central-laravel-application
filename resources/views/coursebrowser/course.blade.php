<!-- Sort classes in the associated set so that credit offering class appears first -->

<?php
/** sort classes by component - with credit component first */
$crs_component = isset($course["component_short_desc"]) ? $course["component_short_desc"] : "";

?>

        <!-- Loop through each associated class set -->

@foreach($course['associated_classes'] as $class)
    <?php

    $associated_classes = $class['classes'];


    ?>


    @include('coursebrowser.class',['class'=>$associated_classes,
'course_component'=>isset($course['component_short_desc'])
?$course['component_short_desc']:""])

@endforeach


