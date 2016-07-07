<?php
uasort($class, function($c1,$c2){
   if(strpos("*****",$c1['class_number'])!==false)
       return -1;

   if(strpos("*****",$c2['class_number'])!==false)
       return 1;

    if($c1['class_number']==$c2['class_number'])return 0;

    return $c1['class_number']<$c2['class_number']?-1:1;
});
?>
@foreach($class as $c)

    @if(isset($c["class_notes_before"]) && $c["class_notes_before"]!="")
        {!! Html::renderNotes($c['class_notes_before']) !!}
    @endif

    @if(isset($c['component_short_description']) &&
        $c['component_short_description']!=$course_component)
        <p>Component:&nbsp;{{$c['component_long_description']}}</p>
    @endif

    <dl class="inline">
        @if($c['topic']!="")
            <dt>Topic: </dt>
            <dd>{{$c['topic']}}</dd>
        @endif

            <dt>Class number: </dt>
            <dd>{{$c["class_number"]}}
                @if($c["class_closed"]!="")
                    {{$c["class_closed"]}}
                @endif
            </dd>
            @if($c["consent_type_requirement"]!="")
                <dt>Class consent: </dt>
                <dd>{{$c["consent_type_requirement"]}}</dd>
            @endif
            <dt>Seats: </dt>
            <dd>{{$c["enrollment_capacity"]}}</dd>
            <dt>Available: </dt>
            <dd>{{$c["total_available"]}}</dd>
            <dt>Wait: </dt>
            <dd>{{$c["waitlisted_total_number"]}}</dd>

    </dl>
    <dl>
            @foreach($c['details'] as $detail)
            <dt>Class information: </dt>
                <dd> {{isset($detail["instructor"])&&$detail["instructor"]!=""?
                     $detail["instructor"]:"&nbsp;"}}, {{$detail["meeting_pattern"]}},
                    {{$detail["start_time"]}}&mdash;{{$detail["end_time"]}}
                    ,{{$detail["facility_bldg_code"]}}&nbsp;{{$detail["facility_bldg_rm_number"]}}
                </dd>
            @endforeach

    </dl>
    {!! Html::renderNotes($c['class_notes_after']) !!}

    @if(isset($class['long_description']))
        <p><strong>Class description:</strong></p>
        {{$class['long_description']}}
    @endif
<hr/>
@endforeach

