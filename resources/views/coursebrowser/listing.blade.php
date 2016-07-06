<ul>
    @foreach($courses as $course)
        <li>{{ \Html::link($course['link'],$course['description']) }}</li>
    @endforeach
</ul>
