<p>{{$course["description_line"]}} </p>
<!-- Sort classes in the associated set so that credit offering class appears first -->

<?php
/** sort classes by component - with credit component first */
$crs_component = isset($course["component_short_desc"])?$course["component_short_desc"]:"";
$associated_classes = $course["associated_classes"];
usort($associated_classes,function($a,$b)use($crs_component){


    $cls_cmpt_a =isset($a['component_short_description'])?
            strcmp($a['component_short_description'],$crs_component):0;
    $cls_cmpt_b =  isset($a['component_short_description'])?
            strcmp($a['component_short_description'], $crs_component):0;
    if($cls_cmpt_a==$cls_cmpt_b)return 0;

    return $cls_cmpt_a<$cls_cmpt_b?-1:1;
});


?>

<!-- Loop Through each associated class set -->

<!-- loop through each set -->
@foreach($associated_classes as $class)
    @include('coursebrowser.class',['class'=>$class,'course_component'=>
    isset($course['component_short_description'])?$course['component_short_description']:""])
@endforeach

