<!-- Sort classes in the associated set so that credit offering class appears first -->

<?php
/** sort classes by component - with credit component first */
$crs_component = isset($course["component_short_desc"]) ? $course["component_short_desc"] : "";

?>

        <!-- Loop through each associated class set -->
<h3>{{$term}}</h3>
@foreach($course['associated_classes'] as $k=>$v)
 @foreach($v as $key=>$classes)
     @if($classes['min_credit_hrs']==$classes['max_credit_hrs'])
     <p><strong>{{$course['description_line']." (".$classes['min_credit_hrs']."CR)"}}</strong></p>
     @else
         <p><strong>{{$course['description_line']." (".$classes['min_credit_hrs']."&mdash;".$classes['max_credit_hrs']."CR)"}}</strong></p>
     @endif
    @include('coursebrowser.class',['class'=>$classes['classes'],
'course_component'=>isset($course['component_short_desc'])
?$course['component_short_desc']:""])
    @endforeach
@endforeach


