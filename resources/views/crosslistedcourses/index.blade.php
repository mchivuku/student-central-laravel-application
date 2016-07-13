@foreach($result as $course)

<p><strong>Term: </strong>{{$course['term']['desc']}}</p>
<p><strong>Department: </strong>{{$departments[$course['department']]}}</p>
<dl class="accordion" role="tablist">


    @foreach($course['courses'] as $courses)
        <dt role="tab" tabindex="">{{$courses['crs_subj_line']}}</dt>
        <dd role="tabpanel" class="content">

        <ul class="no-bullet">
            @foreach($courses['courses'] as $c)
                <li>{{$c}}</li>
            @endforeach

        </ul>

        </dd>

    @endforeach
</dl>
<hr/>
@endforeach