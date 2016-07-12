<?php


?>
@foreach($class as $c)

    @if(isset($c["class_notes_before"]) && $c["class_notes_before"]!="")
        {!! Html::renderNotes($c['class_notes_before']) !!}
    @endif

    <dl class="inline">
        @if(isset($c['component_short_description']) &&
        $c['component_short_description']!=$course_component)
            <dt>Class type:</dt>
            <dd>{{$c['component_long_description']}}</dd>
        @endif
        @if(isset($c['topic']) && $c['topic']!="")
            <dt>Topic:</dt>
            <dd>{{$c['topic']}}</dd>
        @endif

        <dt>Class number:</dt>
        <dd>{{$c["class_number"]}}
            @if($c["class_closed"]!="")
                {{$c["class_closed"]}}
            @endif
        </dd>
        @if($c["consent_type_requirement"]!="")
            <dt>Class consent:</dt>
            <dd>{{$c["consent_type_requirement"]}}</dd>
        @endif
        <dt>Instruction mode:</dt>
        <dd>{{$c["instruction_mode"]["long_description"]}}</dd>
        <dt>Session:</dt>
        <dd>{{$c["class_session"]["session_description"]}}</dd>

        <dt>Total seats:</dt>
        <dd>{{$c["enrollment_capacity"]}}</dd>
        <dt>Available seats:</dt>
        <dd>{{$c["total_available"]}}</dd>
        <dt>Waitlisted seats:</dt>
        <dd>{{$c["waitlisted_total_number"]}}</dd>

    </dl>

    <table>
        <thead>
        <tr>
            <th scope="col">Instructor</th>
            <th scope="col">Day</th>
            <th scope="col">Time</th>
            <th scope="col">Facility</th>
        </tr>
        </thead>
        <tbody>
        @foreach($c['details'] as $detail)
            <tr>

                <td scope="row"> {{isset($detail["instructor"])&&$detail["instructor"]!=""?
                     $detail["instructor"]:"&mdash;"}}</td>
                <td scope="row">{{$detail["meeting_pattern"]}}</td>
                <td scope="row">{{$detail["start_time"]}}&ndash;{{$detail["end_time"]}}</td>
                <td scope="row">{{$detail["facility_bldg_code"]}}&nbsp;{{$detail["facility_bldg_rm_number"]}}</td>
            </tr>
        @endforeach


        </tbody>
    </table>


    {!! Html::renderNotes($c['class_notes_after']) !!}

    @if(isset($c['long_description']) && $c['long_description']!="")
        <p><strong>Class description: </strong></p>
        {{$c['long_description']}}
    @endif
    <hr/>
@endforeach

