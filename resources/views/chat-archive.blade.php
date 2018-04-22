

@extends("layout")

@section("contents")
<section>
    <div class="archive">
    @foreach ($messages->reverse() as $msg)
    <div class="message">
        <span>{{$msg->username}}</span>
        <span>:</span>
        <span>
            @foreach(explode("\n", $msg->contents) as $line)
                {{$line}}<br>
            @endforeach
        </span>
        <div class="footer">
            <span> on </span>
            {{$msg->created_at}}
        </div>
    </div>
    @endforeach
    @for ($i = 1; $i <= $messages->lastPage(); $i++)
        <a class="{{textIf($page==$i, "bracket")}}" href="?page={{$i}}">{{$i}}</a>
    @endfor
    </div>
</section>
@endsection
