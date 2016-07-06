@foreach($class as $c)
    <?php

    /** @var Each class inside associated class list $c */

    // display closed - if closed
    $class_title["clsd"] = $c["class_closed"];

    //class number
    $class_title["classNbr"] = $c["class_number"];

    // consent - if present
    $class_title["consent"] = $c["consent_type_requirement"];

    // Available - seats
    $class_title["seats"] = "Seats: &nbsp;" . $c["enrollment_capacity"] . ", " .
            "Avail:&nbsp;" . $c["total_available"] . ", " . "Wait:&nbsp;" . $c["waitlisted_total_number"];

    $first_line = $c['topic'];
    $second_line = implode("&nbsp;", array_map(function ($x) {
        return $x;
    }, $class_title));
    ?>


    @if(isset($c["class_notes_before"]) && $c["class_notes_before"]!="")
        <p><strong>Class notes(before):&nbsp;</strong></p>
        {!! Html::renderNotes($c['class_notes_before']) !!}
    @endif

    @if(isset($c['component_short_description']) &&
        $c['component_short_description']!=$course_component)
        <p>Component:&nbsp;{{$c['component_long_description']}}</p>
    @endif

    <p><strong>{{$first_line}}</strong></p>
    <p><strong>{{$second_line}}</strong></p>
    <p><strong>Class details:&nbsp;</strong></p>
    <ul>
        @foreach($c['details'] as $detail)
            <li>
                {{isset($detail["instructor"])&&$detail["instructor"]!=""?
                $detail["instructor"]:"&mdash;"}}, {{$detail["meeting_pattern"]}},
                {{$detail["start_time"]}}&mdash;{{$detail["end_time"]}}
                ,{{$detail["facility_bldg_code"]}}&mdash;{{$detail["facility_bldg_rm_number"]}}
            </li>
        @endforeach
    </ul>

    <p><strong>Class notes(after):&nbsp;</strong></p>
    {!! Html::renderNotes($c['class_notes_after']) !!}

    @if(isset($class['long_description']))
          <p><strong>Class description:</strong></p>
            {{$class['long_description']}}
    @endif

@endforeach

