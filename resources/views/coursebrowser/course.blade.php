<!-- Sort classes in the associated set so that credit offering class appears first -->

<?php
/** sort classes by component - with credit component first */
$crs_component = isset($course["component_short_desc"]) ? $course["component_short_desc"] : "";

?>

        <!-- Loop through each associated class set -->

@foreach($course['associated_classes'] as $k=>$v)
 @foreach($v as $key=>$classes)
    @include('coursebrowser.class',['class'=>$classes['classes'],
'course_component'=>isset($course['component_short_desc'])
?$course['component_short_desc']:""])
    @endforeach
@endforeach


