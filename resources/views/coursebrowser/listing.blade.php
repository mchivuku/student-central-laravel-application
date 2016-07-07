<ul class="no-bullet">
    @foreach($courses as $course)
        <li>{!! \Html::link(($course['link']),html_entity_decode($course['description'])) !!} </li>
    @endforeach
</ul>
