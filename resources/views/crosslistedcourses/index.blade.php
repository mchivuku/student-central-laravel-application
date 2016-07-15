@foreach($result as $course)

    <div class="class-wrapper">
        <div class="class-header">
            <p><strong>{{$course['term']['desc']}}</strong></p>
            <p><strong>{{$departments[$course['department']]}}</strong></p>
        </div>
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
    </div>
@endforeach