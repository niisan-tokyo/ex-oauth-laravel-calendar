<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8" />
</head>
<body>
    <a href="/todo/create"><button>TODO作成</button></a>
    <ul>
        @foreach ($events as $event)
            <li>
                @php
                    $todoEvent = $todo->where('event_id', $event['id'])->first();   
                @endphp
                @if($todoEvent)☆彡@endif
                {{ $event['start'] }} ~ {{ $event['end'] }}: {{ $event['summary'] }}
                @if($todoEvent)
                    <form method="POST" action="/todo/{{ $todoEvent->id }}">
                        @method('DELETE')
                        @csrf
                        <button type="submit">消す</button>
                    </form>
                @endif
            </li>
        @endforeach
    </ul>
</body>
</html>