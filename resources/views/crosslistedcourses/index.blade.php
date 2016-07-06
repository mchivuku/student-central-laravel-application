@foreach($result as $course)

<p><strong>Term: </strong>{{$course['term']['desc']}}</p>
<p><strong>Department: </strong>{{$departments[$course['department']]}} ({{$course['department']}})</p>
<ul>
    @foreach($course['courses'] as $courses)
        <li>{{$courses['crs_subj_line']}}</li>
        <ul>
            @foreach($courses['courses'] as $c)
                <li>{{$c}}</li>
            @endforeach
        </ul>
    @endforeach
</ul>
<hr/>
@endforeach